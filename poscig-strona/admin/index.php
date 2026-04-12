<?php
declare(strict_types=1);

session_start();
require __DIR__ . '/csrf.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$loginError = (string) ($_SESSION['login_error'] ?? '');
unset($_SESSION['login_error']);
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
        <?php if ($loginError !== ''): ?>
            <div class="admin-alert"><?= htmlspecialchars($loginError) ?></div>
        <?php endif; ?>

        <div class="admin-auth">
            <section class="admin-card admin-auth__panel admin-auth__copy">
                <span class="admin-badge">Panel Poscigu / Dost&#281;p / Bezpiecze&#324;stwo</span>
                <h1 class="admin-auth__headline">Panel Poscigu</h1>
                <p class="admin-auth__text">
                    Zaloguj si&#281; do panelu operacyjnego.
                    role, has&#322;a i histori&#281; zmian punktowych.
                </p>
            </section>

            <section class="admin-card admin-auth__panel">
                <div class="admin-section-header">
                    <div>
                        <h2 class="admin-title">Panel Poscigu</h2>
                        <p class="admin-subtitle">Wprowad&#378; dane dost&#281;powe swojego konta.</p>
                    </div>
                    <button class="admin-btn admin-btn--ghost admin-theme-toggle" type="button" data-theme-toggle>
                        <span data-theme-label>Tryb jasny</span>
                    </button>
                </div>

                <form method="post" action="login.php" class="admin-form-grid">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                    <div class="admin-field">
                        <label>Login</label>
                        <input type="text" name="username" placeholder="Login" required>
                    </div>

                    <div class="admin-field">
                        <label>Has&#322;o</label>
                        <input type="password" name="password" placeholder="Has&#322;o" required>
                    </div>

                    <div class="admin-form-actions">
                        <button class="admin-btn" type="submit">Zaloguj</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</body>

</html>