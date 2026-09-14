<?php

require_once 'config.php';

$pdo = db();

$pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        email       VARCHAR(190) PRIMARY KEY,
        name        VARCHAR(120) NULL,
        phone       VARCHAR(40)  NULL,
        city        VARCHAR(120) NULL,
        created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$pdo->prepare(
    "INSERT IGNORE INTO users (email, name, phone, city) VALUES (?, ?, ?, ?)"
)->execute(['admin@xyz.com', 'Admin', '+1000', 'HQ']);

echo "Database ready.";