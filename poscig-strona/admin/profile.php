<?php
declare(strict_types=1);

require __DIR__ . '/auth.php';
require __DIR__ . '/_nav.php';
require_once __DIR__ . '/permissions.php';
require __DIR__ . '/../config/db.php';

$errors = [];
$success = false;
$userId = (int) ($_SESSION['user_id'] ?? 0);

$profile = [
    'username' => (string) ($_SESSION['username'] ?? ''),
    'role' => (string) ($_SESSION['role'] ?? ''),
    'first_name' => (string) ($_SESSION['first_name'] ?? ''),
    'last_name' => (string) ($_SESSION['last_name'] ?? ''),
    'harcerski_stopien' => (string) ($_SESSION['harcerski_stopien'] ?? ''),
    'instruktorski_stopien' => (string) ($_SESSION['instruktorski_stopien'] ?? ''),
];

if ($userId > 0) {
    $stmt = $pdo->prepare('SELECT username, role, first_name, last_name, harcerski_stopien, instruktorski_stopien FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();

    if ($row) {
        $profile['username'] = (string) ($row['username'] ?? $profile['username']);
        $profile['role'] = (string) ($row['role'] ?? $profile['role']);
        $profile['first_name'] = (string) ($row['first_name'] ?? $profile['first_name']);
        $profile['last_name'] = (string) ($row['last_name'] ?? $profile['last_name']);
        $profile['harcerski_stopien'] = (string) ($row['harcerski_stopien'] ?? $profile['harcerski_stopien']);
        $profile['instruktorski_stopien'] = (string) ($row['instruktorski_stopien'] ?? $profile['instruktorski_stopien']);

        $_SESSION['username'] = $profile['username'];
        $_SESSION['role'] = $profile['role'];
        $_SESSION['first_name'] = $profile['first_name'];
        $_SESSION['last_name'] = $profile['last_name'];
        $_SESSION['harcerski_stopien'] = $profile['harcerski_stopien'];
        $_SESSION['instruktorski_stopien'] = $profile['instruktorski_stopien'];
        $_SESSION['stopien'] = rank_summary($profile['harcerski_stopien'], $profile['instruktorski_stopien']);
    }
}

$harcerskiOptions = harcerski_rank_options();
$instruktorskiOptions = instruktorski_rank_options();
$canAssignSeniorRanks = can_assign_senior_ranks($profile['role']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf_token'] ?? '', (string) $_POST['csrf'])) {
        $errors[] = 'CSRF';
    }

    $firstName = trim((string) ($_POST['first_name'] ?? ''));
    $lastName = trim((string) ($_POST['last_name'] ?? ''));
    $postedHarcerski = (string) ($_POST['harcerski_stopien'] ?? '');
    $postedInstruktorski = (string) ($_POST['instruktorski_stopien'] ?? '');

    if ($firstName === '' || !preg_match('/^[\p{L}][\p{L} \-\.]{1,49}$/u', $firstName)) {
        $errors[] = 'Imie: podaj poprawne imie';
    }

    if ($lastName === '' || !preg_match('/^[\p{L}][\p{L} \-\.]{1,49}$/u', $lastName)) {
        $errors[] = 'Nazwisko: podaj poprawne nazwisko';
    }

    if (!array_key_exists($postedHarcerski, $harcerskiOptions)) {
        $errors[] = 'Nieprawidlowy stopien harcerski';
    }

    if ($canAssignSeniorRanks) {
        if ($postedInstruktorski !== '' && !array_key_exists($postedInstruktorski, $instruktorskiOptions)) {
            $errors[] = 'Nieprawidlowy stopien instruktorski';
        }
    } else {
        $postedInstruktorski = $profile['instruktorski_stopien'];
    }

    if (!$errors) {
        $instruktorskiToSave = $postedInstruktorski !== '' ? $postedInstruktorski : null;
        $stmt = $pdo->prepare('UPDATE users SET first_name = ?, last_name = ?, harcerski_stopien = ?, instruktorski_stopien = ? WHERE id = ?');
        $stmt->execute([$firstName, $lastName, $postedHarcerski, $instruktorskiToSave, $userId]);

        $_SESSION['first_name'] = $firstName;
        $_SESSION['last_name'] = $lastName;
        $_SESSION['harcerski_stopien'] = $postedHarcerski;
        $_SESSION['instruktorski_stopien'] = $instruktorskiToSave ?? '';
        $_SESSION['stopien'] = rank_summary($postedHarcerski, $instruktorskiToSave ?? '');

        $profile['first_name'] = $firstName;
        $profile['last_name'] = $lastName;
        $profile['harcerski_stopien'] = $postedHarcerski;
        $profile['instruktorski_stopien'] = $instruktorskiToSave ?? '';
        $success = true;
    }
}

$displayName = trim($profile['first_name'] . ' ' . $profile['last_name']);
if ($displayName === '') {
    $displayName = $profile['username'] !== '' ? $profile['username'] : 'Uzytkownik';
}

$harcerskiLabel = rank_label($profile['harcerski_stopien']);
$instruktorskiLabel = rank_label($profile['instruktorski_stopien']);
$rankSummary = rank_summary($profile['harcerski_stopien'], $profile['instruktorski_stopien']);
?>
<!doctype html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Profil</title>
    <link rel="stylesheet" href="/poscig-strona/src/strony/style.css">
    <link rel="stylesheet" href="admin.css?v=2">
    <script src="theme.js?v=2" defer></script>
</head>

<body class="admin-page" data-theme="dark">
    <div class="admin-wrap">
        <?php admin_nav('profile'); ?>

        <div class="admin-card">
            <div class="admin-section-header">
                <div>
                    <h2 class="admin-title">Moj profil</h2>
                    <p class="admin-subtitle">Dane konta i stopnie.</p>
                </div>
            </div>

            <div class="admin-kpis">
                <div class="admin-kpi">
                    <div class="admin-kpi__label">Login</div>
                    <div class="admin-kpi__value"><?= htmlspecialchars($profile['username']) ?></div>
                    <div class="admin-kpi__hint">Nazwa logowania.</div>
                </div>
                <div class="admin-kpi">
                    <div class="admin-kpi__label">Rola</div>
                    <div class="admin-kpi__value"><?= htmlspecialchars($profile['role']) ?></div>
                    <div class="admin-kpi__hint">Poziom dostepu.</div>
                </div>
                <div class="admin-kpi">
                    <div class="admin-kpi__label">Imie i nazwisko</div>
                    <div class="admin-kpi__value"><?= htmlspecialchars($displayName) ?></div>
                    <div class="admin-kpi__hint">Dane profilu.</div>
                </div>
                <div class="admin-kpi">
                    <div class="admin-kpi__label">Stopnie</div>
                    <div class="admin-kpi__value"><?= htmlspecialchars($rankSummary) ?></div>
                    <div class="admin-kpi__hint">Harcerski i instruktorski.</div>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-section-header">
                <div>
                    <h2 class="admin-title">Edytuj profil</h2>
                    <p class="admin-subtitle">Imie, nazwisko i stopnie.</p>
                </div>
                <a class="admin-btn admin-btn--ghost" href="dashboard.php">Wroc do panelu</a>
            </div>

            <?php if ($success): ?>
                <div class="admin-success">Profil zostal zapisany.</div>
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
                    <label>Imie</label>
                    <input type="text" name="first_name" value="<?= htmlspecialchars($profile['first_name']) ?>" required>
                </div>

                <div class="admin-field">
                    <label>Nazwisko</label>
                    <input type="text" name="last_name" value="<?= htmlspecialchars($profile['last_name']) ?>" required>
                </div>

                <div class="admin-field">
                    <label>Stopien harcerski</label>
                    <select name="harcerski_stopien" required>
                        <option value="" disabled <?= $profile['harcerski_stopien'] === '' ? 'selected' : '' ?>>Wybierz stopien</option>
                        <?php foreach ($harcerskiOptions as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= $profile['harcerski_stopien'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-field">
                    <label>Stopien instruktorski</label>
                    <?php if ($canAssignSeniorRanks): ?>
                        <select name="instruktorski_stopien">
                            <option value="" <?= $profile['instruktorski_stopien'] === '' ? 'selected' : '' ?>>Brak</option>
                            <?php foreach ($instruktorskiOptions as $value => $label): ?>
                                <option value="<?= htmlspecialchars($value) ?>" <?= $profile['instruktorski_stopien'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="text" value="<?= htmlspecialchars($instruktorskiLabel) ?>" disabled>
                        <input type="hidden" name="instruktorski_stopien" value="<?= htmlspecialchars($profile['instruktorski_stopien']) ?>">
                    <?php endif; ?>
                </div>

                <div class="admin-form-actions">
                    <button class="admin-btn" type="submit">Zapisz profil</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>