<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);

if (!$email) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email']);
    exit;
}

$apiKey = 're_QE74YDW7_LDWWYLNCnisMVscoxLfwA9ZK';

// Get audiences
$ch = curl_init('https://api.resend.com/audiences');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
    ],
]);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

$audienceId = null;
if (!empty($response['data'])) {
    // Use first audience (shared Waitlist audience)
    $audienceId = $response['data'][0]['id'] ?? null;
}

if (!$audienceId) {
    $ch = curl_init('https://api.resend.com/audiences');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode(['name' => 'Waitlist']),
    ]);
    $result = json_decode(curl_exec($ch), true);
    curl_close($ch);
    $audienceId = $result['id'] ?? null;
}

if (!$audienceId) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not find or create audience']);
    exit;
}

// Add contact with product property
$ch = curl_init("https://api.resend.com/audiences/{$audienceId}/contacts");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'email' => $email,
        'unsubscribed' => false,
        'properties' => [
            ['key' => 'product', 'value' => 'kneadit'],
        ],
    ]),
]);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode >= 200 && $httpCode < 300) {
    echo json_encode(['success' => true, 'message' => 'Welcome to the waitlist!']);
} else {
    $decoded = json_decode($result, true);
    // Resend returns 409 for duplicate contacts
    if ($httpCode === 409) {
        echo json_encode(['success' => true, 'message' => 'Already on the list!']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to add contact', 'details' => $decoded]);
    }
}
