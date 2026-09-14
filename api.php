<?php

session_start();

require_once 'db_helpers.php';
require_once 'nlp_parser.php';

header('Content-Type: application/json');

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(['reply' => 'Not authenticated.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['reply' => 'Method not allowed.']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$text = trim($body['message'] ?? '');

if ($text === '') {
    echo json_encode(['reply' => 'Please type a command.']);
    exit;
}

$sessionEmail = $_SESSION['email'];

save_message($sessionEmail, 'user', $text);

$pendingReply = check_pending_action($text);

if ($pendingReply !== null) {
    $reply = $pendingReply;
} else {
    $intent = parse_intent($text);

    try {
        $reply = handle_intent($intent);
    } catch (Throwable $e) {
        $reply = 'Something went wrong while processing your request.';
    }
}

save_message($sessionEmail, 'bot', $reply);

echo json_encode(['reply' => $reply]);

function handle_intent(array $intent): string
{
    $action = $intent['action'] ?? INTENT_UNKNOWN;

    return match ($action) {
        INTENT_ADD    => handle_add($intent),
        INTENT_UPDATE => handle_update($intent),
        INTENT_DELETE => handle_delete($intent),
        INTENT_LIST   => handle_list(),
        default       => handle_unknown(),
    };
}

function handle_add(array $intent): string
{
    $email = $intent['email'] ?? null;

    if (!$email) {
        return 'Please include an email address to add a new user.';
    }

    [, $message] = add_user(
        $email,
        $intent['name']  ?? null,
        $intent['phone'] ?? null,
        $intent['city']  ?? null
    );

    $_SESSION['last_target'] = $email;

    return $message;
}

function handle_update(array $intent): string
{
    $target = $intent['target'] ?? '';
    $field  = strtolower($intent['field'] ?? '');
    $value  = $intent['value'] ?? '';

    if ($field === '' || $value === '') {
        return 'Please specify the field and the new value.';
    }

    if ($target === '') {
        $target = $_SESSION['last_target'] ?? '';
    }

    if ($target === '') {
        return 'Which user should I update? Please include an email or a name.';
    }

    if (str_contains($target, '@')) {
        $user = get_user($target);
        if ($user === null) {
            return "No user found with email \"{$target}\".";
        }
        $email = $user['email'];
    } else {
        $matches = find_users_by_name($target);

        if (count($matches) === 0) {
            return "No user named \"{$target}\" exists.";
        }

        if (count($matches) > 1) {
            $_SESSION['pending_action'] = [
                'type'  => 'update',
                'field' => $field,
                'value' => $value,
            ];

            $list = implode("\n", array_map(
                fn($u) => "• {$u['email']} — " . ($u['name'] ?? '(no name)'),
                $matches
            ));
            return "Multiple users match \"{$target}\". Reply with one of these emails:\n{$list}";
        }

        $email = $matches[0]['email'];
    }

    $_SESSION['last_target'] = $email;

    [, $message] = update_user($email, $field, $value);

    return $message;
}

function handle_delete(array $intent): string
{
    $target = $intent['target'] ?? '';

    if ($target === '') {
        return 'Please specify which user to remove.';
    }

    if (str_contains($target, '@')) {
        $user = get_user($target);
        if ($user === null) {
            return "No user found with email \"{$target}\".";
        }
        $email = $user['email'];
    } else {
        $matches = find_users_by_name($target);

        if (count($matches) === 0) {
            return "No user named \"{$target}\" exists.";
        }

        if (count($matches) > 1) {
            $_SESSION['pending_action'] = [
                'type' => 'delete',
            ];

            $list = implode("\n", array_map(
                fn($u) => "• {$u['email']} — " . ($u['name'] ?? '(no name)'),
                $matches
            ));
            return "Multiple users match \"{$target}\". Reply with one of these emails:\n{$list}";
        }

        $email = $matches[0]['email'];
    }

    [, $message] = delete_user($email);

    return $message;
}

function handle_list(): string
{
    $users = list_users();

    if (!$users) {
        return 'No users in the system.';
    }

    $lines = array_map(
        fn(array $u) => sprintf(
            '%s | %s | %s | %s',
            $u['email'],
            $u['name']  ?? '-',
            $u['phone'] ?? '-',
            $u['city']  ?? '-'
        ),
        $users
    );

    return "Users:\n" . implode("\n", $lines);
}

function handle_unknown(): string
{
    return "I could not understand that command.\n"
        . "Try one of these:\n"
        . "add the user john@xyz.com with phone +92332\n"
        . "add eysha's name Eysha\n"
        . "add address Lahore\n"
        . "update john@xyz.com city to Lahore\n"
        . "remove john@xyz.com\n"
        . "list";
}

function check_pending_action(string $text): ?string
{
    if (!isset($_SESSION['pending_action'])) {
        return null;
    }

    $text  = trim($text);
    $lower = strtolower($text);

    if (in_array($lower, ['cancel', 'nevermind', 'never mind', 'stop', 'forget it'], true)) {
        unset($_SESSION['pending_action']);
        return 'Cancelled.';
    }

    if (!preg_match('/[\w\.\-]+@[\w\.\-]+\.\w+/', $text, $m)) {
        return null;
    }

    $email = $m[0];
    $user  = get_user($email);

    if ($user === null) {
        return "No user found with email \"{$email}\". Try again or type cancel.";
    }

    $pending = $_SESSION['pending_action'];
    unset($_SESSION['pending_action']);

    if ($pending['type'] === 'update') {
        [, $message] = update_user($email, $pending['field'], $pending['value']);
        $_SESSION['last_target'] = $email;
        return $message;
    }

    if ($pending['type'] === 'delete') {
        [, $message] = delete_user($email);
        return $message;
    }

    return null;
}