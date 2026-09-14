<?php

session_start();

require_once 'db_helpers.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed.']);
    exit;
}

$body  = json_decode(file_get_contents('php://input'), true) ?? [];
$email = trim($body['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Enter a valid email address.']);
    exit;
}

$user = get_user($email);

if ($user === null) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'This email is not registered.']);
    exit;
}

$_SESSION['email']    = $email;
$_SESSION['login_at'] = time();

echo json_encode(['ok' => true]);