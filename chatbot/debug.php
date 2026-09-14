<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h3>Debug Info</h3>";

echo "<p><strong>Environment Variables:</strong></p>";
echo "<p>DB_HOST: " . (getenv('DB_HOST') ?: '<span style="color:red">NOT SET</span>') . "</p>";
echo "<p>DB_NAME: " . (getenv('DB_NAME') ?: '<span style="color:red">NOT SET</span>') . "</p>";
echo "<p>DB_USER: " . (getenv('DB_USER') ?: '<span style="color:red">NOT SET</span>') . "</p>";
echo "<p>DB_PASS: " . (getenv('DB_PASS') ? 'SET' : '<span style="color:red">NOT SET</span>') . "</p>";

echo "<hr>";

echo "<p><strong>Config File:</strong></p>";

if (!file_exists(__DIR__ . '/config.php')) {
    die("<p style='color:red'>config.php NOT FOUND in " . __DIR__ . "</p>");
}

echo "<p>config.php found</p>";

require_once __DIR__ . '/config.php';

echo "<p>config.php loaded</p>";

echo "<hr>";

echo "<p><strong>Database:</strong></p>";

try {
    $pdo = db();
    echo "<p style='color:green'>Database connected</p>";

    $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "<p>users table: $count rows</p>";

    $count = $pdo->query("SELECT COUNT(*) FROM chat_messages")->fetchColumn();
    echo "<p>chat_messages table: $count rows</p>";

    echo "<p><strong style='color:green'>All good</strong></p>";
} catch (Throwable $e) {
    echo "<p style='color:red'>ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
}
