<?php
declare(strict_types=1);

session_start();
require __DIR__ . '/csrf.php';
require __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf_token'] ?? '', (string) $_POST['csrf'])) {
    $_SESSION['login_error'] = 'Blad zabezpieczen (CSRF).';
    header('Location: index.php');
    exit;
}

$username = trim((string) ($_POST['username'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    $_SESSION['login_error'] = 'Nieprawidlowy login lub haslo.';
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, (string) $user['password'])) {
    session_regenerate_id(true);

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['username'] = (string) $user['username'];
    $_SESSION['role'] = (string) $user['role'];
    $_SESSION['last_activity'] = time();

    header('Location: dashboard.php');
    exit;
}

$_SESSION['login_error'] = 'Nieprawidlowy login lub haslo.';
header('Location: index.php');
exit;
