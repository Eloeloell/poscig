<?php
declare(strict_types=1);

require __DIR__ . '/auth.php';
require_once __DIR__ . '/permissions.php';
require __DIR__ . '/_nav.php';
require __DIR__ . '/../config/db.php';

$username = (string) ($_SESSION['username'] ?? '');
$role = (string) ($_SESSION['role'] ?? '');
$userId = (int) ($_SESSION['user_id'] ?? 0);
$isAdmin = can_access_admin_tools($role);

$profile = [
    'username' => $username,
    'role' => $role,
    'first_name' => (string) ($_SESSION['first_name'] ?? ''),
    'last_name' => (string) ($_SESSION['last_name'] ?? ''),
    'harcerski_stopien' => (string) ($_SESSION['harcerski_stopien'] ?? ''),
    'instruktorski_stopien' => (string) ($_SESSION['instruktorski_stopien'] ?? ''),
];

if ($userId > 0) {
    $stmt = $pdo->prepare('SELECT username, first_name, last_name, harcerski_stopien, instruktorski_stopien, role FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();

    if ($row) {
        $profile = [
            'username' => (string) ($row['username'] ?? $username),
            'role' => (string) ($row['role'] ?? $role),
            'first_name' => (string) ($row['first_name'] ?? ''),
            'last_name' => (string) ($row['last_name'] ?? ''),
            'harcerski_stopien' => (string) ($row['harcerski_stopien'] ?? ''),
            'instruktorski_stopien' => (string) ($row['instruktorski_stopien'] ?? ''),
        ];

        $_SESSION['username'] = $profile['username'];
        $_SESSION['role'] = $profile['role'];
        $_SESSION['first_name'] = $profile['first_name'];
        $_SESSION['last_name'] = $profile['last_name'];
        $_SESSION['harcerski_stopien'] = $profile['harcerski_stopien'];
        $_SESSION['instruktorski_stopien'] = $profile['instruktorski_stopien'];
        $_SESSION['stopien'] = rank_summary($profile['harcerski_stopien'], $profile['instruktorski_stopien']);

        $username = $profile['username'];
        $role = $profile['role'];
        $isAdmin = can_access_admin_tools($role);
    }
}

$displayName = trim($profile['first_name'] . ' ' . $profile['last_name']);
if ($displayName === '') {
    $displayName = $profile['username'] !== '' ? $profile['username'] : 'Użytkownik';
}

$harcerskiLabel = rank_label($profile['harcerski_stopien']);
$instruktorskiLabel = rank_label($profile['instruktorski_stopien']);
$rankSummary = rank_summary($profile['harcerski_stopien'], $profile['instruktorski_stopien']);
$usersTotal = null;
$pointsTotal = null;
$historyTotal = null;

if ($isAdmin) {
    try {
        $usersTotal = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $pointsTotal = (int) $pdo->query('SELECT COUNT(*) FROM points')->fetchColumn();
        $historyTotal = (int) $pdo->query('SELECT COUNT(*) FROM points_history')->fetchColumn();
    } catch (Throwable $e) {
        $usersTotal = $usersTotal ?? null;
        $pointsTotal = null;
        $historyTotal = null;
    }
}
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Panel Pościgu</title>
    <link rel="stylesheet" href="/poscig-strona/src/strony/style.css">
    <link rel="stylesheet" href="admin.css?v=2">
    <script src="theme.js?v=2" defer></script>
</head>

<body class="admin-page" data-theme="dark">
    <div class="admin-wrap">
        <?php admin_nav('dashboard'); ?>

        <section class="admin-card admin-hero">
            <div>
                <span class="admin-badge">Panel operacyjny</span>
                <h1 class="admin-title">Panel Pościgu</h1>
                <p class="admin-subtitle">
                    <strong><?= htmlspecialchars($displayName) ?></strong>
                    (<?= htmlspecialchars($role) ?>)
                    <span class="admin-meta">/ <?= htmlspecialchars($rankSummary) ?></span>
                </p>
                <div class="admin-badges">
                    <span class="admin-chip">Sesja aktywna</span>
                    <span class="admin-chip">CSRF włączone</span>
                    <?php if ($isAdmin): ?>
                        <span class="admin-chip">Administrator</span>
                    <?php else: ?>
                        <span class="admin-chip">Dostęp ograniczony</span>
                    <?php endif; ?>
                </div>
            </div>
            <a class="admin-btn admin-btn--ghost" href="profile.php">Mój profil</a>
        </section>

        <section class="admin-card">
            <div class="admin-section-header">
                <div>
                    <h2 class="admin-title">Mój profil</h2>
                    <p class="admin-subtitle">Edytuj profil obok.</p>
                </div>
                <a class="admin-btn admin-btn--ghost" href="profile.php">Edytuj profil</a>
            </div>

            <div class="admin-kpis">
                <div class="admin-kpi">
                    <div class="admin-kpi__label">Imię i nazwisko</div>
                    <div class="admin-kpi__value"><?= htmlspecialchars($displayName) ?></div>
                    <div class="admin-kpi__hint">Dane profilu.</div>
                </div>
                <div class="admin-kpi">
                    <div class="admin-kpi__label">Login</div>
                    <div class="admin-kpi__value"><?= htmlspecialchars($username) ?></div>
                    <div class="admin-kpi__hint">Nazwa konta.</div>
                </div>
                <div class="admin-kpi">
                    <div class="admin-kpi__label">Harcerski</div>
                    <div class="admin-kpi__value"><?= htmlspecialchars($harcerskiLabel) ?></div>
                    <div class="admin-kpi__hint">Aktualny stopień harcerski, widoczny na podstronach.</div>
                </div>
                <div class="admin-kpi">
                    <div class="admin-kpi__label">Instruktorski</div>
                    <div class="admin-kpi__value"><?= htmlspecialchars($instruktorskiLabel) ?></div>
                    <div class="admin-kpi__hint">Stopień instruktorski.</div>
                </div>
            </div>
        </section>

        <section class="admin-card">
            <div class="admin-section-header">
                <div>
                    <h2 class="admin-title">Stan panelu</h2>
                    <p class="admin-subtitle">Aktywny.</p>
                </div>
            </div>

            <div class="admin-kpis">
                <div class="admin-kpi">
                    <div class="admin-kpi__label">Rola</div>
                    <div class="admin-kpi__value"><?= htmlspecialchars($role) ?></div>
                    <div class="admin-kpi__hint">Poziom dostępu.</div>
                </div>
                <div class="admin-kpi">
                    <div class="admin-kpi__label">Użytkownicy</div>
                    <div class="admin-kpi__value"><?= $usersTotal === null ? '&mdash;' : (int) $usersTotal ?></div>
                    <div class="admin-kpi__hint">Widoczne tylko dla administratora.</div>
                </div>
                <div class="admin-kpi">
                    <div class="admin-kpi__label">Zastępy</div>
                    <div class="admin-kpi__value"><?= $pointsTotal === null ? '&mdash;' : (int) $pointsTotal ?></div>
                    <div class="admin-kpi__hint">Liczba zastepow punktowych.</div>
                </div>
                <div class="admin-kpi">
                    <div class="admin-kpi__label">Historia</div>
                    <div class="admin-kpi__value"><?= $historyTotal === null ? '&mdash;' : (int) $historyTotal ?></div>
                    <div class="admin-kpi__hint">Ostatnie zdarzenia.</div>
                </div>
            </div>
        </section>

        <section class="admin-card">
            <div class="admin-section-header">
                <div>
                    <h2 class="admin-title">Skróty operacyjne</h2>
                    <p class="admin-subtitle">Najczęściej używane akcje.</p>
                </div>
            </div>

            <div class="admin-tiles">
                <?php if ($isAdmin): ?>
                    <a class="admin-tile" href="users.php">
                        <div class="admin-tile__icon">U</div>
                        <div>
                            <h3 class="admin-tile__title">Użytkownicy</h3>
                            <p class="admin-tile__desc">Profile, role i usuwanie kont.</p>
                        </div>
                        <div class="admin-tile__chev">&#8250;</div>
                    </a>

                    <a class="admin-tile" href="user_add.php">
                        <div class="admin-tile__icon">+</div>
                        <div>
                            <h3 class="admin-tile__title">Dodaj użytkownika</h3>
                            <p class="admin-tile__desc">Nowe konto z profilem i stopniem.</p>
                        </div>
                        <div class="admin-tile__chev">&#8250;</div>
                    </a>

                    <a class="admin-tile" href="edit_points.php">
                        <div class="admin-tile__icon">P</div>
                        <div>
                            <h3 class="admin-tile__title">Punkty</h3>
                            <p class="admin-tile__desc">Edycja punktów i historia.</p>
                        </div>
                        <div class="admin-tile__chev">&#8250;</div>
                    </a>
                <?php endif; ?>

                <a class="admin-tile" href="profile.php">
                    <div class="admin-tile__icon">M</div>
                    <div>
                        <h3 class="admin-tile__title">Mój profil</h3>
                        <p class="admin-tile__desc">Imię, nazwisko i stopnie.</p>
                    </div>
                    <div class="admin-tile__chev">&#8250;</div>
                </a>

                <a class="admin-tile" href="change_password.php">
                    <div class="admin-tile__icon">&#128274;</div>
                    <div>
                        <h3 class="admin-tile__title">Zmień hasło</h3>
                        <p class="admin-tile__desc">Hasło dla tego konta.</p>
                    </div>
                    <div class="admin-tile__chev">&#8250;</div>
                </a>
            </div>
        </section>
    </div>
</body>

</html>