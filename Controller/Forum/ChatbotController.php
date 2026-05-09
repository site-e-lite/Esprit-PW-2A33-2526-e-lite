<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/ForumController.php';

class ChatbotController {

    private function callGroqAPI($prompt, $temperature = 0.7) {
        $apiKey = Config::GROQ_API_KEY;
        if (empty($apiKey)) {
            return "Service IA indisponible (clé API manquante).";
        }
        $url = 'https://api.groq.com/openai/v1/chat/completions';

        $data = [
            'model' => 'llama-3.1-8b-instant',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => $temperature
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        $response = curl_exec($ch);
        
        if(curl_errno($ch)){
            $error = curl_error($ch);
            curl_close($ch);
            return "Erreur de connexion à l'IA : " . $error;
        }
        
        curl_close($ch);
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $json = json_decode($response, true);
        if (isset($json['choices'][0]['message']['content'])) {
            return $json['choices'][0]['message']['content'];
        }
        
        // Log the error response for debugging
        file_put_contents(__DIR__ . '/api_error.log', "Groq API Error: " . $response . "\n", FILE_APPEND);
        
        $errorMsg = "Je suis désolé, je n'ai pas pu générer une réponse (Erreur API).";
        if ($httpCode >= 400 && isset($json['error']['message'])) {
            $errorMsg = "Erreur Groq : " . $json['error']['message'];
        }
        
        return $errorMsg;
    }

    public function handleRequest($query) {
        $response = [
            "type" => "text",
            "message" => "",
            "data" => []
        ];

        // System Context for Chatbot (Strict Domain Guardrails)
        $systemPrompt = "Tu es 'e-lite Assistant', l'IA officielle de la plateforme d'e-learning e-lite.\n"
                      . "RÈGLE ABSOLUE : Tu ne dois répondre qu'aux questions concernant l'e-learning, l'éducation, la programmation, les cours ou l'utilisation de la plateforme e-lite.\n"
                      . "Si l'utilisateur pose une question hors de ce domaine (par exemple : la politique, la cuisine, le sport, etc.), tu dois REFUSER de répondre poliment en expliquant que ton rôle est strictement limité à l'assistance pédagogique.\n"
                      . "Réponds de façon très concise et professionnelle.\n\n"
                      . "Question de l'utilisateur : " . $query;

        // Call Groq with temperature 0.7 (balanced)
        $aiResponse = $this->callGroqAPI($systemPrompt, 0.7);

        // Convert markdown bold to simple HTML
        $aiResponse = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $aiResponse);
        $aiResponse = nl2br($aiResponse);

        $response["message"] = $aiResponse;

        // Hybrid approach: We still check if there are related threads to suggest
        $similarThreads = $this->findSimilarThreads($query);
        if (!empty($similarThreads)) {
             $response["message"] .= "<br><br><i>Au fait, j'ai trouvé ces discussions qui pourraient vous intéresser :</i>";
             $response["type"] = "threads";
             $response["data"] = $similarThreads;
        }

        return $response;
    }

    public function summarizeThread($idForum) {
        $db = Config::getConnexion();
        $stmt = $db->prepare("SELECT contenu FROM post WHERE idForum = :idForum LIMIT 10");
        $stmt->execute(['idForum' => $idForum]);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($posts)) return "Ce fil de discussion est vide, il n'y a rien à résumer.";

        $fullText = "";
        foreach ($posts as $i => $p) {
            $fullText .= "Message " . ($i+1) . " : " . $p['contenu'] . "\n";
        }

        $prompt = "Tu es un assistant IA. Voici les derniers messages d'une discussion sur un forum e-learning. "
                . "Fais un résumé clair, très concis (2 ou 3 phrases maximum) et professionnel de cette discussion :\n\n"
                . $fullText;

        // Low temperature (0.3) for factual summarization
        $summary = $this->callGroqAPI($prompt, 0.3);
        
        $summary = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $summary);
        
        return "<strong>Résumé IA :</strong> " . $summary;
    }

    public function evaluateContentRisk($text) {
        // 1. Liste locale de mots interdits (Première ligne de défense)
        $badWords = ['merde', 'con', 'salope', 'connard', 'putain', 'bordel', 'fuck', 'shit', 'spam', 'casino', 'viagra'];
        $textLower = mb_strtolower($text);
        foreach ($badWords as $bw) {
            if (strpos($textLower, $bw) !== false) {
                return ["risk" => "High", "reason" => "Détection automatique de vocabulaire inapproprié."];
            }
        }

        $prompt = "Tu es un modérateur IA. Analyse ce texte pour un forum d'e-learning.\n"
                . "Évalue le risque (insultes, toxicité, spam).\n"
                . "Tu DOIS répondre UNIQUEMENT par un objet JSON brut, sans texte avant ou après, sous ce format :\n"
                . '{"risk": "Low" | "Medium" | "High", "reason": "explication courte"}' . "\n\n"
                . "Texte : " . $text;

        // Very low temperature (0.1) to enforce strict JSON output
        $response = $this->callGroqAPI($prompt, 0.1);
        
        // Nettoyer la réponse pour s'assurer que c'est du JSON valide (au cas où l'IA rajoute des backticks Markdown)
        $response = str_replace(['```json', '```'], '', $response);
        $data = json_decode(trim($response), true);

        if (json_last_error() === JSON_ERROR_NONE && isset($data['risk'])) {
            return $data; // Returns ['risk' => '...', 'reason' => '...']
        }
        
        // Fallback permissif si l'API est indisponible (quota dépassé, etc.)
        return ["risk" => "Low", "reason" => "Analyse IA indisponible. Validation par défaut."];
    }

    private function findSimilarThreads($query) {
        $db = Config::getConnexion();
        $stopWords = ['le', 'la', 'les', 'un', 'une', 'des', 'est', 'sont', 'comment', 'pourquoi', 'quel', 'quelle', 'quels', 'quelles', 'je', 'tu', 'il', 'nous', 'vous', 'ils'];
        $words = explode(' ', strtolower($query));
        
        $condition = "";
        foreach ($words as $w) {
            if (strlen($w) > 3 && !in_array($w, $stopWords)) {
                $w = preg_replace('/[^a-zA-Z0-9_]/', '', $w);
                if (!empty($w)) {
                    $condition .= "titre LIKE '%$w%' OR description LIKE '%$w%' OR ";
                }
            }
        }
        $condition = rtrim($condition, " OR ");
        
        if (empty($condition)) return [];

        $sql = "SELECT DISTINCT idForum, titre FROM forum WHERE $condition LIMIT 3";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
