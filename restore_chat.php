<?php

session_start();

if (!isset($_SESSION['email'])) {
    header('Location: index.php');
    exit;
}

require_once 'db_helpers.php';

restore_messages($_SESSION['email']);

header('Location: chat.php');
exit;