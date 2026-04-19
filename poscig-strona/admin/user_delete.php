<?php
declare(strict_types=1);

require __DIR__ . '/auth.php';
require_once __DIR__ . '/permissions.php';
require __DIR__ . '/../config/db.php';

if (!can_access_admin_tools((string) ($_SESSION['role'] ?? ''))) {
    http_response_code(403);
    exit('Brak dostępu');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Nieprawidłowe żądanie');
}

if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf_token'] ?? '', (string) $_POST['csrf'])) {
    exit('Błąd zabezpieczeń (CSRF)');
}

$userId = (int) ($_POST['id'] ?? 0);
if ($userId <= 0) {
    exit('Nieprawidłowy użytkownik');
}

if ($userId === (int) ($_SESSION['user_id'] ?? 0)) {
    exit('Nie możesz usunąć siebie');
}

$stmt = $pdo->prepare('SELECT role FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$userId]);
$targetRole = $stmt->fetchColumn();

if ($targetRole === false) {
    exit('Użytkownik nie istnieje');
}

if ($targetRole === 'admin') {
    $adminCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
    if ($adminCount <= 1) {
        exit('Nie można usunąć ostatniego administratora');
    }
}

$stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
$stmt->execute([$userId]);

header('Location: users.php');
exit;
