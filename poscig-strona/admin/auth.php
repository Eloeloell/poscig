<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/csrf.php';

// Auto-wylogowanie po 30 min
if (isset($_SESSION['last_activity']) && time() - (int) $_SESSION['last_activity'] > 1800) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

$_SESSION['last_activity'] = time();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}