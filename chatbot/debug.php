<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h3>PHP: " . PHP_VERSION . "</h3>";

echo "<p>DB_HOST: " . (getenv('DB_HOST') ?: 'NOT SET') . "</p>";
echo "<p>DB_NAME: " . (getenv('DB_NAME') ?: 'NOT SET') . "</p>";
echo "<p>DB_USER: " . (getenv('DB_USER') ?: 'NOT SET') . "</p>";
echo "<p>DB_PASS set: " . (getenv('DB_PASS') ? 'YES' : 'NO') . "</p>";

echo "<hr>";

if (!file_exists(__DIR__ . '/config.php')) {
    die("<p style='color:red'>config.php NOT FOUND in " . __DIR__ . "</p>");
}

echo "<p>config.php found</p>";

require_once __DIR__ . '/config.php';

echo "<p>config.php loaded</p>";

try {
    $pdo = db();
    echo "<p style='color:green'>Database connected</p>";

    $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "<p>users table: $count rows</p>";

    $count = $pdo->query("SELECT COUNT(*) FROM chat_messages")->fetchColumn();
    echo "<p>chat_messages table: $count rows</p>";

    echo "<p><strong>All good</strong></p>";
} catch (Throwable $e) {
    echo "<p style='color:red'>ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
}
