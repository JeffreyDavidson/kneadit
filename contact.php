<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
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

$data = json_decode(file_get_contents('php://input'), true);

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$message = trim($data['message'] ?? '');

if (!$name || !$email || !$message) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email']);
    exit;
}

// Store to file (same pattern as waitlist)
$entry = [
    'name' => $name,
    'email' => $email,
    'message' => $message,
    'date' => date('Y-m-d H:i:s'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
];

$file = __DIR__ . '/data/contacts.json';
$dir = dirname($file);
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$contacts = [];
if (file_exists($file)) {
    $contacts = json_decode(file_get_contents($file), true) ?: [];
}
$contacts[] = $entry;
file_put_contents($file, json_encode($contacts, JSON_PRETTY_PRINT));

// Send email notification
$to = 'hello@getkneadit.app';
$subject = "KneadIt Contact: {$name}";
$body = "Name: {$name}\nEmail: {$email}\n\nMessage:\n{$message}";
$headers = "From: noreply@getkneadit.app\r\nReply-To: {$email}";
@mail($to, $subject, $body, $headers);

echo json_encode(['success' => true, 'message' => 'Message received']);
