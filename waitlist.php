<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
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
    http_response_code(422);
    echo json_encode(['error' => 'Invalid email']);
    exit;
}

$file = __DIR__ . '/storage/waitlist.json';
$dir = dirname($file);

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$entries = [];
if (file_exists($file)) {
    $entries = json_decode(file_get_contents($file), true) ?: [];
}

// Check for duplicate
foreach ($entries as $entry) {
    if ($entry['email'] === $email) {
        echo json_encode(['success' => true, 'message' => 'Already on the list!']);
        exit;
    }
}

$entries[] = [
    'email' => $email,
    'date' => date('Y-m-d H:i:s'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
    'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '',
];

file_put_contents($file, json_encode($entries, JSON_PRETTY_PRINT));

echo json_encode(['success' => true, 'message' => 'Welcome to the waitlist!', 'count' => count($entries)]);
