<?php

require_once 'config.php';

/* ============================================================
   Users
   ============================================================ */

function get_user(string $email): ?array
{
    $stmt = db()->prepare(
        "SELECT email, name, phone, city FROM users WHERE LOWER(email) = LOWER(?)"
    );
    $stmt->execute([$email]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function find_users_by_name(string $name): array
{
    $name = mb_strtolower(trim($name));
    if ($name === '') {
        return [];
    }

    $stmt = db()->query("SELECT email, name, phone, city FROM users");
    $matches = [];

    foreach ($stmt->fetchAll() as $row) {
        $full  = mb_strtolower(trim($row['name'] ?? ''));
        $first = $full !== '' ? explode(' ', $full)[0] : '';

        if ($full === '' && $first === '') {
            continue;
        }

        if (
            $full === $name ||
            $first === $name ||
            ($full !== '' && str_contains($full, $name)) ||
            ($first !== '' && str_contains($first, $name)) ||
            ($full !== '' && str_contains($name, $full)) ||
            ($first !== '' && str_contains($name, $first))
        ) {
            $matches[] = $row;
        }
    }

    return $matches;
}

function add_user(string $email, ?string $name, ?string $phone, ?string $city): array
{
    try {
        if ($name !== null) {
            $name = ucwords(strtolower(trim($name)));
        }
        if ($city !== null) {
            $city = ucwords(strtolower(trim($city)));
        }

        $stmt = db()->prepare(
            "INSERT INTO users (email, name, phone, city) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$email, $name, $phone, $city]);

        return [true, "Added user {$email}."];
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            return [false, "User {$email} already exists."];
        }
        return [false, "Database error."];
    }
}

function update_user(string $email, string $field, string $value): array
{
    if (!in_array($field, ['name', 'phone', 'city'], true)) {
        return [false, "Only name, phone or city can be updated."];
    }

    if (in_array($field, ['name', 'city'], true)) {
        $value = ucwords(strtolower(trim($value)));
    }

    $stmt = db()->prepare("UPDATE users SET {$field} = ? WHERE email = ?");
    $stmt->execute([$value, $email]);

    if ($stmt->rowCount() > 0) {
        return [true, "Updated {$field} of {$email} to \"{$value}\"."];
    }

    return [false, "User {$email} not found or value unchanged."];
}

function delete_user(string $email): array
{
    $stmt = db()->prepare("DELETE FROM users WHERE LOWER(email) = LOWER(?)");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        return [true, "Deleted user {$email}."];
    }

    return [false, "User {$email} not found."];
}

function list_users(): array
{
    return db()->query(
        "SELECT email, name, phone, city FROM users ORDER BY created_at DESC"
    )->fetchAll();
}

function count_users(): int
{
    return (int) db()->query("SELECT COUNT(*) FROM users")->fetchColumn();
}

/* ============================================================
   Chat history — active
   ============================================================ */

function save_message(string $userEmail, string $sender, string $message): void
{
    $stmt = db()->prepare(
        "INSERT INTO chat_messages (user_email, sender, message) VALUES (?, ?, ?)"
    );
    $stmt->execute([$userEmail, $sender, $message]);
}

function get_messages(string $userEmail, int $limit = 200): array
{
    $stmt = db()->prepare(
        "SELECT id, sender, message, created_at
         FROM chat_messages
         WHERE user_email = ? AND deleted_at IS NULL
         ORDER BY id ASC
         LIMIT " . (int) $limit
    );
    $stmt->execute([$userEmail]);

    return $stmt->fetchAll();
}

/* ============================================================
   Chat history — trash
   ============================================================ */

function get_trashed_messages(string $userEmail): array
{
    $stmt = db()->prepare(
        "SELECT id, sender, message, created_at, deleted_at
         FROM chat_messages
         WHERE user_email = ? AND deleted_at IS NOT NULL
         ORDER BY deleted_at DESC"
    );
    $stmt->execute([$userEmail]);

    return $stmt->fetchAll();
}

function trash_messages(string $userEmail): void
{
    $stmt = db()->prepare(
        "UPDATE chat_messages
         SET deleted_at = NOW()
         WHERE user_email = ? AND deleted_at IS NULL"
    );
    $stmt->execute([$userEmail]);
}

function restore_messages(string $userEmail): void
{
    $stmt = db()->prepare(
        "UPDATE chat_messages
         SET deleted_at = NULL
         WHERE user_email = ? AND deleted_at IS NOT NULL"
    );
    $stmt->execute([$userEmail]);
}

function delete_message_permanently(int $id, string $userEmail): void
{
    $stmt = db()->prepare(
        "DELETE FROM chat_messages WHERE id = ? AND user_email = ?"
    );
    $stmt->execute([$id, $userEmail]);
}

function empty_trash(string $userEmail): void
{
    $stmt = db()->prepare(
        "DELETE FROM chat_messages WHERE user_email = ? AND deleted_at IS NOT NULL"
    );
    $stmt->execute([$userEmail]);
}

function count_trashed_messages(string $userEmail): int
{
    $stmt = db()->prepare(
        "SELECT COUNT(*) FROM chat_messages WHERE user_email = ? AND deleted_at IS NOT NULL"
    );
    $stmt->execute([$userEmail]);

    return (int) $stmt->fetchColumn();
}