<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/ForumController.php';

class ChatbotController {

    // ─────────────────────────────────────────────
    //  GEMINI API (primary)
    // ─────────────────────────────────────────────

    /**
     * Appelle Groq (primaire, rapide, gratuit) puis Gemini en fallback.
     */
    private function callAI(string $prompt, float $temperature = 0.7): string {
        // 1. Essayer Groq d'abord (plus rapide, quota généreux)
        $groqKey = Config::GROQ_API_KEY;
        if (!empty($groqKey)) {
            $result = $this->callGroq($prompt, $temperature, $groqKey);
            if ($result !== null) return $result;
        }

        // 2. Fallback Gemini
        $geminiKey = Config::GEMINI_API_KEY;
        if (!empty($geminiKey)) {
            $result = $this->callGemini($prompt, $temperature, $geminiKey);
            if ($result !== null) return $result;
        }

        return "L'IA est temporairement indisponible. Réessayez dans quelques instants.";
    }

    private function callGroq(string $prompt, float $temperature, string $apiKey): ?string {
        $url  = 'https://api.groq.com/openai/v1/chat/completions';
        $data = [
            'model'       => 'llama-3.1-8b-instant',
            'messages'    => [['role' => 'user', 'content' => $prompt]],
            'temperature' => $temperature,
            'max_tokens'  => 512,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        $response = curl_exec($ch);
        if (curl_errno($ch)) { curl_close($ch); return null; }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode($response, true);
        if (isset($json['choices'][0]['message']['content'])) {
            return $json['choices'][0]['message']['content'];
        }
        file_put_contents(__DIR__ . '/api_error.log', "[Groq] HTTP $httpCode : $response\n\n", FILE_APPEND);
        return null;
    }

    private function callGemini(string $prompt, float $temperature, string $apiKey): ?string {
        $models = ['gemini-2.0-flash', 'gemini-1.5-flash', 'gemini-1.5-flash-8b'];
        $data   = [
            'contents'         => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => ['temperature' => $temperature, 'maxOutputTokens' => 512],
        ];

        foreach ($models as $model) {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $apiKey;
            $ch  = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);

            $response = curl_exec($ch);
            if (curl_errno($ch)) { curl_close($ch); continue; }
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $json = json_decode($response, true);
            if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
                return $json['candidates'][0]['content']['parts'][0]['text'];
            }
            if ($httpCode === 429 || $httpCode === 404) continue;
            file_put_contents(__DIR__ . '/api_error.log', "[$model] HTTP $httpCode : $response\n\n", FILE_APPEND);
        }
        return null;
    }

    // Alias pour compatibilité interne
    private function callGeminiAPI(string $prompt, float $temperature = 0.7): string {
        return $this->callAI($prompt, $temperature);
    }

    // ─────────────────────────────────────────────
    //  PUBLIC METHODS
    // ─────────────────────────────────────────────

    /**
     * Répond à une question utilisateur (chatbot forum).
     */
    public function handleRequest(string $query): array {
        $response = [
            "type"    => "text",
            "message" => "",
            "data"    => []
        ];

        $systemPrompt = "Tu es 'e-lite Assistant', l'IA officielle de la plateforme d'e-learning e-lite.\n"
                      . "RÈGLE ABSOLUE : Tu ne dois répondre qu'aux questions concernant l'e-learning, l'éducation, la programmation, les cours ou l'utilisation de la plateforme e-lite.\n"
                      . "Si l'utilisateur pose une question hors de ce domaine (par exemple : la politique, la cuisine, le sport, etc.), tu dois REFUSER de répondre poliment en expliquant que ton rôle est strictement limité à l'assistance pédagogique.\n"
                      . "Réponds de façon très concise et professionnelle.\n\n"
                      . "Question de l'utilisateur : " . $query;

        $aiResponse = $this->callGeminiAPI($systemPrompt, 0.7);

        // Convertir markdown bold → HTML
        $aiResponse = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $aiResponse);
        $aiResponse = nl2br($aiResponse);

        $response["message"] = $aiResponse;

        // Suggérer des fils similaires
        $similarThreads = $this->findSimilarThreads($query);
        if (!empty($similarThreads)) {
            $response["message"] .= "<br><br><i>Au fait, j'ai trouvé ces discussions qui pourraient vous intéresser :</i>";
            $response["type"]    = "threads";
            $response["data"]    = $similarThreads;
        }

        return $response;
    }

    /**
     * Résume les posts d'un forum via l'IA.
     */
    public function summarizeThread(int $idForum): string {
        $db   = Config::getConnexion();
        $stmt = $db->prepare("SELECT contenu FROM post WHERE idForum = :idForum LIMIT 10");
        $stmt->execute(['idForum' => $idForum]);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($posts)) {
            return "Ce fil de discussion est vide, il n'y a rien à résumer.";
        }

        $fullText = "";
        foreach ($posts as $i => $p) {
            $fullText .= "Message " . ($i + 1) . " : " . $p['contenu'] . "\n";
        }

        $prompt = "Tu es un assistant IA. Voici les derniers messages d'une discussion sur un forum e-learning. "
                . "Fais un résumé clair, très concis (2 ou 3 phrases maximum) et professionnel de cette discussion :\n\n"
                . $fullText;

        $summary = $this->callGeminiAPI($prompt, 0.3);
        $summary = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $summary);

        return "<strong>Résumé IA :</strong> " . $summary;
    }

    /**
     * Évalue le risque d'un texte (modération de contenu).
     * Retourne ['risk' => 'Low'|'Medium'|'High', 'reason' => '...']
     */
    public function evaluateContentRisk(string $text): array {
        // 1. Filtre local rapide (première ligne de défense)
        $badWords  = ['merde', 'con', 'salope', 'connard', 'putain', 'bordel', 'fuck', 'shit', 'spam', 'casino', 'viagra'];
        $textLower = mb_strtolower($text);
        foreach ($badWords as $bw) {
            if (strpos($textLower, $bw) !== false) {
                return ["risk" => "High", "reason" => "Détection automatique de vocabulaire inapproprié."];
            }
        }

        // 2. Analyse IA
        $prompt = "Tu es un modérateur IA. Analyse ce texte pour un forum d'e-learning.\n"
                . "Évalue le risque (insultes, toxicité, spam).\n"
                . "Tu DOIS répondre UNIQUEMENT par un objet JSON brut, sans texte avant ou après, sous ce format :\n"
                . '{"risk": "Low", "reason": "explication courte"}' . "\n"
                . "Les valeurs possibles pour risk sont : Low, Medium, High.\n\n"
                . "Texte : " . $text;

        $response = $this->callGeminiAPI($prompt, 0.1);

        // Nettoyer les backticks Markdown éventuels
        $response = preg_replace('/```json\s*/i', '', $response);
        $response = str_replace('```', '', $response);
        $data     = json_decode(trim($response), true);

        if (json_last_error() === JSON_ERROR_NONE && isset($data['risk'])) {
            return $data;
        }

        // Fallback permissif si l'IA est indisponible
        return ["risk" => "Low", "reason" => "Analyse IA indisponible. Validation par défaut."];
    }

    // ─────────────────────────────────────────────
    //  PRIVATE HELPERS
    // ─────────────────────────────────────────────

    /**
     * Cherche des fils de discussion similaires à la requête (recherche par mots-clés).
     */
    private function findSimilarThreads(string $query): array {
        $db        = Config::getConnexion();
        $stopWords = ['le', 'la', 'les', 'un', 'une', 'des', 'est', 'sont', 'comment', 'pourquoi',
                      'quel', 'quelle', 'quels', 'quelles', 'je', 'tu', 'il', 'nous', 'vous', 'ils'];
        $words     = explode(' ', strtolower($query));

        $conditions = [];
        foreach ($words as $w) {
            $w = preg_replace('/[^a-zA-Z0-9_]/', '', $w);
            if (strlen($w) > 3 && !in_array($w, $stopWords)) {
                $conditions[] = "titre LIKE '%" . addslashes($w) . "%' OR description LIKE '%" . addslashes($w) . "%'";
            }
        }

        if (empty($conditions)) {
            return [];
        }

        $sql = "SELECT DISTINCT idForum, titre FROM forum WHERE " . implode(' OR ', $conditions) . " LIMIT 3";
        try {
            return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
