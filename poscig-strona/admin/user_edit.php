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

$targetId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
if ($targetId <= 0) {
    http_response_code(400);
    exit('Nieprawidłowy użytkownik');
}

$stmt = $pdo->prepare('SELECT id, username, first_name, last_name, harcerski_stopien, instruktorski_stopien, role FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$targetId]);
$target = $stmt->fetch();
if (!$target) {
    http_response_code(404);
    exit('Użytkownik nie istnieje');
}

$targetRole = (string) ($target['role'] ?? '');
if (!can_edit_other_profile($currentRole, $targetRole)) {
    http_response_code(403);
    exit('Brak dostępu');
}

$canAssignSeniorRanks = can_assign_senior_ranks($currentRole);
$harcerskiOptions = harcerski_rank_options();
$instruktorskiOptions = instruktorski_rank_options();

$errors = [];
$success = false;
$firstName = (string) ($target['first_name'] ?? '');
$lastName = (string) ($target['last_name'] ?? '');
$harcerskiStopien = (string) ($target['harcerski_stopien'] ?? '');
$instruktorskiStopien = (string) ($target['instruktorski_stopien'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf_token'] ?? '', (string) $_POST['csrf'])) {
        $errors[] = 'Błąd zabezpieczeń (CSRF)';
    }

    $firstName = trim((string) ($_POST['first_name'] ?? ''));
    $lastName = trim((string) ($_POST['last_name'] ?? ''));
    $postedHarcerski = (string) ($_POST['harcerski_stopien'] ?? '');
    $harcerskiToSave = $postedHarcerski !== '' ? $postedHarcerski : null;
    $postedInstruktorski = (string) ($_POST['instruktorski_stopien'] ?? '');

    if ($firstName === '' || !preg_match('/^[\p{L}][\p{L} \-\.]{1,49}$/u', $firstName)) {
        $errors[] = 'Imię: podaj poprawne imię';
    }

    if ($lastName === '' || !preg_match('/^[\p{L}][\p{L} \-\.]{1,49}$/u', $lastName)) {
        $errors[] = 'Nazwisko: podaj poprawne nazwisko';
    }

    if ($postedHarcerski !== '' && !array_key_exists($postedHarcerski, $harcerskiOptions)) {
        $errors[] = 'Nieprawidłowy stopień harcerski';
    }

    if ($canAssignSeniorRanks) {
        if ($postedInstruktorski !== '' && !array_key_exists($postedInstruktorski, $instruktorskiOptions)) {
            $errors[] = 'Nieprawidłowy stopień instruktorski';
        }
    } else {
        $postedInstruktorski = $instruktorskiStopien;
    }

    if (!$errors) {
        $instruktorskiToSave = $postedInstruktorski !== '' ? $postedInstruktorski : null;
        $stmt = $pdo->prepare('UPDATE users SET first_name = ?, last_name = ?, harcerski_stopien = ?, instruktorski_stopien = ? WHERE id = ?');
        $stmt->execute([$firstName, $lastName, $harcerskiToSave, $instruktorskiToSave, $targetId]);

        $target['first_name'] = $firstName;
        $target['last_name'] = $lastName;
        $target['harcerski_stopien'] = $harcerskiToSave ?? '';
        $target['instruktorski_stopien'] = $instruktorskiToSave ?? '';
        $harcerskiStopien = $harcerskiToSave ?? '';
        $instruktorskiStopien = $instruktorskiToSave ?? '';
        $success = true;
    }
}

$displayName = trim((string) ($target['first_name'] ?? '') . ' ' . (string) ($target['last_name'] ?? ''));
if ($displayName === '') {
    $displayName = (string) ($target['username'] ?? 'Użytkownik');
}

$harcerskiLabel = rank_label((string) ($target['harcerski_stopien'] ?? ''));
$instruktorskiLabel = rank_label((string) ($target['instruktorski_stopien'] ?? ''));
$rankSummary = rank_summary((string) ($target['harcerski_stopien'] ?? ''), (string) ($target['instruktorski_stopien'] ?? ''));
?>
<!doctype html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Edytuj profil</title>
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
                    <h2 class="admin-title">Profil użytkownika</h2>
                    <p class="admin-subtitle"><?= htmlspecialchars((string) $target['username']) ?></p>
                </div>
                <a class="admin-btn admin-btn--ghost" href="users.php">Wróć do listy</a>
            </div>

            <div class="admin-kpis">
                <div class="admin-kpi">
                    <div class="admin-kpi__label">Login</div>
                    <div class="admin-kpi__value"><?= htmlspecialchars((string) $target['username']) ?></div>
                    <div class="admin-kpi__hint">Konto edytowane przez panel.</div>
                </div>
                <div class="admin-kpi">
                    <div class="admin-kpi__label">Rola</div>
                    <div class="admin-kpi__value"><?= htmlspecialchars((string) $target['role']) ?></div>
                    <div class="admin-kpi__hint">Poziom dostępu.</div>
                </div>
                <div class="admin-kpi">
                    <div class="admin-kpi__label">Imię i nazwisko</div>
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
                    <h2 class="admin-title">Edytuj dane</h2>
                    <p class="admin-subtitle">Imię, nazwisko i stopnie.</p>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="admin-success">Profil został zapisany.</div>
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
                <input type="hidden" name="id" value="<?= (int) $targetId ?>">

                <div class="admin-field">
                    <label>Imię</label>
                    <input type="text" name="first_name" value="<?= htmlspecialchars($firstName) ?>" required>
                </div>

                <div class="admin-field">
                    <label>Nazwisko</label>
                    <input type="text" name="last_name" value="<?= htmlspecialchars($lastName) ?>" required>
                </div>

                <div class="admin-field">
                    <label>Stopień harcerski</label>
                    <select name="harcerski_stopien">
                        <option value="" <?= $harcerskiStopien === '' ? 'selected' : '' ?>>Brak</option>
                        <?php foreach ($harcerskiOptions as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= $harcerskiStopien === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-field">
                    <label>Stopień instruktorski</label>
                    <?php if ($canAssignSeniorRanks): ?>
                        <select name="instruktorski_stopien">
                            <option value="" <?= $instruktorskiStopien === '' ? 'selected' : '' ?>>Brak</option>
                            <?php foreach ($instruktorskiOptions as $value => $label): ?>
                                <option value="<?= htmlspecialchars($value) ?>" <?= $instruktorskiStopien === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="text" value="<?= htmlspecialchars($instruktorskiLabel) ?>" disabled>
                        <input type="hidden" name="instruktorski_stopien" value="<?= htmlspecialchars($instruktorskiStopien) ?>">
                    <?php endif; ?>
                </div>

                <div class="admin-form-actions">
                    <button class="admin-btn" type="submit">Zapisz zmiany</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>