<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h3>Environment Variables</h3>";
echo "<p>DB_HOST: " . (getenv('DB_HOST') ?: '<span style=color:red>NOT SET</span>') . "</p>";
echo "<p>DB_PORT: " . (getenv('DB_PORT') ?: '<span style=color:red>NOT SET</span>') . "</p>";
echo "<p>DB_NAME: " . (getenv('DB_NAME') ?: '<span style=color:red>NOT SET</span>') . "</p>";
echo "<p>DB_USER: " . (getenv('DB_USER') ?: '<span style=color:red>NOT SET</span>') . "</p>";
echo "<p>DB_PASS: " . (getenv('DB_PASS') ? 'SET' : '<span style=color:red>NOT SET</span>') . "</p>";

echo "<hr><h3>Config File</h3>";

if (!file_exists(__DIR__ . '/config.php')) {
    die("<p style='color:red'>config.php NOT FOUND</p>");
}
echo "<p>config.php exists</p>";

require_once __DIR__ . '/config.php';
echo "<p>config.php loaded</p>";
echo "<p>DB_HOST value: <strong>" . DB_HOST . "</strong></p>";
echo "<p>DB_PORT value: <strong>" . DB_PORT . "</strong></p>";
echo "<p>DB_NAME value: <strong>" . DB_NAME . "</strong></p>";
echo "<p>DB_USER value: <strong>" . DB_USER . "</strong></p>";

echo "<hr><h3>Database Connection</h3>";

try {
    $pdo = db();
    echo "<p style='color:green'>Connected</p>";

    $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "<p>users: $count rows</p>";

    $count = $pdo->query("SELECT COUNT(*) FROM chat_messages")->fetchColumn();
    echo "<p>chat_messages: $count rows</p>";

    echo "<p><strong style='color:green'>ALL GOOD</strong></p>";
} catch (Throwable $e) {
    echo "<p style='color:red'><strong>ERROR:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
