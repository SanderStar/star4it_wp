<?php
require 'vendor/autoload.php';

use Smalot\PdfParser\Parser;

// Load PDF text
$parser = new Parser();
$pdf = $parser->parseFile('example.pdf');

$text = $pdf->getText();
// TODO echo $text;

require __DIR__ . '/vendor/autoload.php'; 
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__); 
$dotenv->load(); 

$apiKey = $_ENV['GEMINI_API_KEY'];
if (!$apiKey) {
    throw new Exception("API key not found in environment");
}

$search = "ASR";

$prompt = "Search the following text for '$search' and return all matches:\n\n$text";

$payload = [
    "contents" => [
        ["parts" => [["text" => $prompt]]]
    ]
];

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=$apiKey";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// TODO: Remove the following line in production
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
curl_close($ch);

echo $response;

