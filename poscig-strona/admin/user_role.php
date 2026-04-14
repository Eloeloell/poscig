<?php
declare(strict_types=1);

require __DIR__ . '/auth.php';
require __DIR__ . '/../config/db.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Brak dostępu');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Nieprawidłowe żądanie');
}

if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf_token'] ?? '', (string) $_POST['csrf'])) {
    exit('Błąd zabezpieczeń (CSRF)');
}

$userId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$newRole = (string) ($_POST['role'] ?? '');

$allowedRoles = ['admin', 'druh', 'druzynowy', 'zastepowy'];
if (!in_array($newRole, $allowedRoles, true)) {
    exit('Nieprawidłowa rola');
}

if ($userId <= 0) {
    exit('Nieprawidłowy użytkownik');
}

if ($userId === (int) ($_SESSION['user_id'] ?? 0)) {
    exit('Nie możesz zmienić własnej roli');
}

$stmt = $pdo->prepare('SELECT role FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$userId]);
$currentRole = $stmt->fetchColumn();

if ($currentRole === false) {
    exit('Użytkownik nie istnieje');
}

if ($currentRole === 'admin' && $newRole !== 'admin') {
    $adminCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
    if ($adminCount <= 1) {
        exit('Nie można odebrać roli ostatniemu administratorowi');
    }
}

$stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
$stmt->execute([$newRole, $userId]);

header('Location: users.php');
exit;