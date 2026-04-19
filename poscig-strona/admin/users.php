<?php
declare(strict_types=1);

require __DIR__ . '/auth.php';
require __DIR__ . '/_nav.php';
require_once __DIR__ . '/permissions.php';
require __DIR__ . '/../config/db.php';

$currentRole = (string) ($_SESSION['role'] ?? '');
if (!can_manage_all_profiles($currentRole)) {
    http_response_code(403);
    exit('Brak dostępu');
}

$isAdmin = can_access_admin_tools($currentRole);
$users = $pdo->query('SELECT id, username, first_name, last_name, harcerski_stopien, instruktorski_stopien, role FROM users ORDER BY username')->fetchAll();
$csrf = (string) ($_SESSION['csrf_token'] ?? '');
$currentId = (int) ($_SESSION['user_id'] ?? 0);
?>
<!doctype html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Zarządzanie użytkownikami</title>
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
                    <h2 class="admin-title">Użytkownicy</h2>
                    <p class="admin-subtitle">Profile, role i stopnie.</p>
                </div>
                <?php if ($isAdmin): ?>
                    <a class="admin-btn admin-btn--ghost" href="user_add.php">+ Dodaj</a>
                <?php endif; ?>
            </div>

            <div class="admin-table-shell">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Login</th>
                            <th>Imię i nazwisko</th>
                            <th>Harcerski</th>
                            <th>Instruktorski</th>
                            <th>Rola</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <?php
                            $fullName = trim((string) ($u['first_name'] ?? '') . ' ' . (string) ($u['last_name'] ?? ''));
                            $harcerskiLabel = rank_label((string) ($u['harcerski_stopien'] ?? ''));
                            $instruktorskiLabel = rank_label((string) ($u['instruktorski_stopien'] ?? ''));
                            $isSelf = (int) $u['id'] === $currentId;
                            $canEditProfile = $isSelf || can_edit_other_profile($currentRole, (string) $u['role']);
                            ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars((string) $u['username']) ?>
                                    <?= $isSelf ? '<span class="admin-meta"> (ty)</span>' : '' ?>
                                </td>
                                <td>
                                    <?php if ($fullName !== ''): ?>
                                        <?= htmlspecialchars($fullName) ?>
                                    <?php else: ?>
                                        <span class="admin-meta">Brak danych</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($harcerskiLabel) ?></td>
                                <td><?= htmlspecialchars($instruktorskiLabel) ?></td>
                                <td><?= htmlspecialchars((string) $u['role']) ?></td>
                                <td>
                                    <?php if ($canEditProfile): ?>
                                        <div class="admin-inline-form">
                                            <a class="admin-btn admin-btn--ghost" href="<?= $isSelf ? 'profile.php' : 'user_edit.php?id=' . (int) $u['id'] ?>">
                                                <?= $isSelf ? 'Mój profil' : 'Edytuj profil' ?>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span class="admin-meta">&mdash;</span>
                                    <?php endif; ?>

                                    <?php if ($isAdmin && !$isSelf): ?>
                                        <form method="post" action="user_role.php" class="admin-inline-form">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                            <select name="role">
                                                <option value="druh" <?= $u['role'] === 'druh' ? 'selected' : '' ?>>Druh</option>
                                                <option value="zastepowy" <?= $u['role'] === 'zastepowy' ? 'selected' : '' ?>>Zastępowy</option>
                                                <option value="druzynowy" <?= $u['role'] === 'druzynowy' ? 'selected' : '' ?>>Drużynowy</option>
                                                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                            <button class="admin-btn" type="submit">Zmień</button>
                                        </form>

                                        <form method="post" action="user_delete.php" class="admin-inline-form">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                            <button class="admin-btn admin-btn--danger" type="submit"
                                                onclick="return confirm('Na pewno usunąć użytkownika?')">Usuń</button>
                                        </form>
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
