<?php
declare(strict_types=1);

require __DIR__ . '/auth.php';
require __DIR__ . '/_nav.php';
require __DIR__ . '/../config/db.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Brak dostepu');
}

$errors = [];
$username = '';
$role = 'kadra';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf_token'] ?? '', (string) $_POST['csrf'])) {
        $errors[] = 'CSRF';
    }

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $role = (string) ($_POST['role'] ?? 'kadra');

    if ($username === '' || !preg_match('/^[a-zA-Z0-9_.-]{3,32}$/', $username)) {
        $errors[] = 'Login: 3-32 znaki (litery/cyfry/._-)';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Haslo: minimum 8 znakow';
    }

    $allowedRoles = ['admin', 'kadra', 'druzynowy'];
    if (!in_array($role, $allowedRoles, true)) {
        $errors[] = 'Nieprawidlowa rola';
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
            $stmt->execute([$username, $hash, $role]);

            header('Location: users.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = 'Taki login juz istnieje';
            } else {
                $errors[] = 'Blad bazy danych';
            }
        }
    }
}
?>
<!doctype html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Dodaj u&#380;ytkownika</title>
    <link rel="stylesheet" href="/poscig-strona/src/strony/style.css">
    <link rel="stylesheet" href="admin.css?v=2">
    <script src="theme.js?v=2" defer></script>
</head>

<body class="admin-page" data-theme="dark">
    <div class="admin-wrap">
        <?php admin_nav('users'); ?>

        <div class="admin-card">
            <div class="admin-section-header">
                <div>
                    <h2 class="admin-title">Dodaj u&#380;ytkownika</h2>
                    <p class="admin-subtitle">Tworzenie konta dla kadry, dru&#380;ynowego lub administratora.</p>
                </div>
            </div>

            <?php if ($errors): ?>
                <div class="admin-alert">
                    <ul>
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" class="admin-form-grid">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                <div class="admin-field">
                    <label>Login</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required>
                </div>

                <div class="admin-field">
                    <label>Has&#322;o</label>
                    <input type="password" name="password" required>
                </div>

                <div class="admin-field">
                    <label>Rola</label>
                    <select name="role">
                        <option value="kadra" <?= $role === 'kadra' ? 'selected' : '' ?>>Kadra</option>
                        <option value="druzynowy" <?= $role === 'druzynowy' ? 'selected' : '' ?>>Dru&#380;ynowy</option>
                        <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>

                <div class="admin-form-actions">
                    <button class="admin-btn" type="submit">Dodaj</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
