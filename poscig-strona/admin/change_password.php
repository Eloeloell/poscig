<?php
declare(strict_types=1);

require __DIR__ . '/auth.php';
require __DIR__ . '/_nav.php';
require __DIR__ . '/../config/db.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf_token'] ?? '', (string) $_POST['csrf'])) {
        $errors[] = 'CSRF';
    }

    $current = (string) ($_POST['current_password'] ?? '');
    $new1 = (string) ($_POST['new_password'] ?? '');
    $new2 = (string) ($_POST['new_password_2'] ?? '');

    if (strlen($new1) < 8) {
        $errors[] = 'Nowe haslo: minimum 8 znakow';
    }

    if ($new1 !== $new2) {
        $errors[] = 'Nowe hasla nie sa takie same';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([(int) ($_SESSION['user_id'] ?? 0)]);
        $hash = (string) ($stmt->fetchColumn() ?: '');

        if ($hash === '' || !password_verify($current, $hash)) {
            $errors[] = 'Aktualne haslo jest nieprawidlowe';
        } else {
            $newHash = password_hash($new1, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([$newHash, (int) $_SESSION['user_id']]);
            $success = true;
        }
    }
}
?>
<!doctype html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Zmiana hasla</title>
    <link rel="stylesheet" href="/poscig-strona/src/strony/style.css">
    <link rel="stylesheet" href="admin.css?v=2">
    <script src="theme.js?v=2" defer></script>
</head>

<body class="admin-page" data-theme="dark">
    <div class="admin-wrap">
        <?php admin_nav('dashboard'); ?>

        <div class="admin-card">
            <div class="admin-section-header">
                <div>
                    <h2 class="admin-title">Zmie&#324; has&#322;o</h2>
                    <p class="admin-subtitle">Aktualizacja has&#322;a dla bie&#380;&#261;cego konta.</p>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="admin-success">Haslo zostalo zmienione.</div>
            <?php endif; ?>

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
                    <label>Aktualne haslo</label>
                    <input type="password" name="current_password" required>
                </div>

                <div class="admin-field">
                    <label>Nowe haslo</label>
                    <input type="password" name="new_password" required>
                </div>

                <div class="admin-field">
                    <label>Powtorz nowe haslo</label>
                    <input type="password" name="new_password_2" required>
                </div>

                <div class="admin-form-actions">
                    <button class="admin-btn" type="submit">Zmie&#324;</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
