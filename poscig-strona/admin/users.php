<?php
declare(strict_types=1);

require __DIR__ . '/auth.php';
require __DIR__ . '/_nav.php';
require __DIR__ . '/../config/db.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Brak dostepu');
}

$users = $pdo->query('SELECT id, username, role FROM users ORDER BY username')->fetchAll();
$csrf = (string) ($_SESSION['csrf_token'] ?? '');
$currentId = (int) ($_SESSION['user_id'] ?? 0);
?>
<!doctype html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Zarz&#261;dzanie u&#380;ytkownikami</title>
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
                    <h2 class="admin-title">U&#380;ytkownicy</h2>
                    <p class="admin-subtitle">Zarz&#261;dzaj rolami i kontami w jednym miejscu.</p>
                </div>
                <a class="admin-btn admin-btn--ghost" href="user_add.php">+ Dodaj</a>
            </div>

            <div class="admin-table-shell">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Login</th>
                            <th>Rola</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars((string) $u['username']) ?>
                                    <?= ((int) $u['id'] === $currentId) ? '<span class="admin-meta"> (ty)</span>' : '' ?>
                                </td>
                                <td><?= htmlspecialchars((string) $u['role']) ?></td>
                                <td>
                                    <?php if ((int) $u['id'] !== $currentId): ?>
                                        <form method="post" action="user_role.php" class="admin-inline-form">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                            <select name="role">
                                                <option value="kadra" <?= $u['role'] === 'kadra' ? 'selected' : '' ?>>Kadra</option>
                                                <option value="druzynowy" <?= $u['role'] === 'druzynowy' ? 'selected' : '' ?>>Dru&#380;ynowy</option>
                                                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                            <button class="admin-btn" type="submit">Zmie&#324;</button>
                                        </form>

                                        <form method="post" action="user_delete.php" class="admin-inline-form">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                            <button class="admin-btn admin-btn--danger" type="submit"
                                                onclick="return confirm('Na pewno usunac uzytkownika?')">Usu&#324;</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="admin-meta">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
