<?php
declare(strict_types=1);

require __DIR__ . '/auth.php';
require __DIR__ . '/_nav.php';
require __DIR__ . '/../config/db.php';

$username = (string) ($_SESSION['username'] ?? '');
$role = (string) ($_SESSION['role'] ?? '');
$isAdmin = $role === 'admin';
$usersTotal = $isAdmin ? (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() : null;
$pointsTotal = $isAdmin ? (int) $pdo->query('SELECT COUNT(*) FROM points')->fetchColumn() : null;
$historyTotal = $isAdmin ? (int) $pdo->query('SELECT COUNT(*) FROM points_history')->fetchColumn() : null;
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Panel Poscigu</title>
    <link rel="stylesheet" href="/poscig-strona/src/strony/style.css">
    <link rel="stylesheet" href="admin.css?v=2">
    <script src="theme.js?v=2" defer></script>
</head>

<body class="admin-page" data-theme="dark">
    <div class="admin-wrap">
        <?php admin_nav('dashboard'); ?>

        <section class="admin-card admin-hero">
            <div>
                <span class="admin-badge">Panel operacyjny / moderacja</span>
                <h1 class="admin-title">Panel Poscigu</h1>
                <p class="admin-subtitle">Zalogowany jako <strong><?= htmlspecialchars($username) ?></strong>
                    (<?= htmlspecialchars($role) ?>).</p>
                <div class="admin-badges">
                    <span class="admin-chip">Sesja aktywna</span>
                    <span class="admin-chip">CSRF w&#322;&#261;czone</span>
                    <?php if ($isAdmin): ?>
                        <span class="admin-chip">Pe&#322;ny dost&#281;p</span>
                    <?php else: ?>
                        <span class="admin-chip">Ograniczony dost&#281;p</span>
                    <?php endif; ?>
                </div>
            </div>
            <a class="admin-btn admin-btn--ghost" href="change_password.php">Zmie&#324; has&#322;o</a>
        </section>

        <section class="admin-card">
            <div class="admin-section-header">
                <div>
                    <h2 class="admin-title">Stan panelu</h2>
                    <p class="admin-subtitle">Aktywny</p>
                </div>
            </div>

            <div class="admin-kpis">
                <div class="admin-kpi">
                    <div class="admin-kpi__label">Rola</div>
                    <div class="admin-kpi__value"><?= htmlspecialchars($role) ?></div>
                    <div class="admin-kpi__hint">Uprawnienia aktywne dla bieżącej sesji.</div>
                </div>
                <div class="admin-kpi">
                    <div class="admin-kpi__label">Użytkownicy</div>
                    <div class="admin-kpi__value"><?= $usersTotal === null ? '&mdash;' : (int) $usersTotal ?></div>
                    <div class="admin-kpi__hint">Widoczne tylko dla roli admin.</div>
                </div>
                <div class="admin-kpi">
                    <div class="admin-kpi__label">Zastępy</div>
                    <div class="admin-kpi__value"><?= $pointsTotal === null ? '&mdash;' : (int) $pointsTotal ?></div>
                    <div class="admin-kpi__hint">Liczba rekordów punktowych.</div>
                </div>
                <div class="admin-kpi">
                    <div class="admin-kpi__label">Historia</div>
                    <div class="admin-kpi__value"><?= $historyTotal === null ? '&mdash;' : (int) $historyTotal ?></div>
                    <div class="admin-kpi__hint">Ostatnie zdarzenia w dzienniku.</div>
                </div>
            </div>
        </section>

        <section class="admin-card">
            <div class="admin-section-header">
                <div>
                    <h2 class="admin-title">Skróty operacyjne</h2>
                    <p class="admin-subtitle">Najczęściej używane akcje administracyjne.</p>
                </div>
            </div>

            <div class="admin-tiles">
                <?php if ($role === 'admin'): ?>
                    <a class="admin-tile" href="users.php">
                        <div class="admin-tile__icon">U</div>
                        <div>
                            <h3 class="admin-tile__title">Użytkownicy</h3>
                            <p class="admin-tile__desc">Dodawanie, zmiana ról i usuwanie kont.</p>
                        </div>
                        <div class="admin-tile__chev">&#8250;</div>
                    </a>

                    <a class="admin-tile" href="user_add.php">
                        <div class="admin-tile__icon">+</div>
                        <div>
                            <h3 class="admin-tile__title">Dodaj użytkownika</h3>
                            <p class="admin-tile__desc">Szybko utwórz konto kadry/moderatora.</p>
                        </div>
                        <div class="admin-tile__chev">&#8250;</div>
                    </a>

                    <a class="admin-tile" href="edit_points.php">
                        <div class="admin-tile__icon">P</div>
                        <div>
                            <h3 class="admin-tile__title">Punkty zastępów</h3>
                            <p class="admin-tile__desc">Edycja punktów + historia zmian.</p>
                        </div>
                        <div class="admin-tile__chev">&#8250;</div>
                    </a>
                <?php endif; ?>

                <a class="admin-tile" href="change_password.php">
                    <div class="admin-tile__icon">&#128274;</div>
                    <div>
                        <h3 class="admin-tile__title">Zmień hasło</h3>
                        <p class="admin-tile__desc">Zmiana hasła dla Twojego konta.</p>
                    </div>
                    <div class="admin-tile__chev">&#8250;</div>
                </a>
            </div>
        </section>
    </div>
</body>

</html>