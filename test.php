<?php
require 'config.php';

$apiKey = Config::GEMINI_API_KEY;

// List models
$url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . $apiKey;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

echo "<h3>Modèles disponibles pour votre clé :</h3>";
echo "<pre>";
$data = json_decode($response, true);
if (isset($data['models'])) {
    foreach ($data['models'] as $model) {
        if (strpos($model['name'], 'gemini') !== false) {
            echo $model['name'] . " - " . implode(", ", $model['supportedGenerationMethods']) . "\n";
        }
    }
} else {
    print_r($response);
}
echo "</pre>";

// Test POST
echo "<h3>Test de génération :</h3>";
$urlPost = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey;
$ch2 = curl_init($urlPost);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch2, CURLOPT_POST, true);
curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode([
    'contents' => [['parts' => [['text' => 'Dis bonjour']]]]
]));
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
$response2 = curl_exec($ch2);
curl_close($ch2);

echo "<pre>";
print_r($response2);
echo "</pre>";
?>
