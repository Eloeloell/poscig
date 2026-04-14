<?php
declare(strict_types=1);

require __DIR__ . '/auth.php';
require __DIR__ . '/_nav.php';
require_once __DIR__ . '/permissions.php';
require __DIR__ . '/../config/db.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Brak dostepu');
}

$errors = [];
$username = '';
$firstName = '';
$lastName = '';
$role = 'druh';
$harcerskiStopien = '';
$instruktorskiStopien = '';

$harcerskiOptions = harcerski_rank_options();
$instruktorskiOptions = instruktorski_rank_options();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf_token'] ?? '', (string) $_POST['csrf'])) {
        $errors[] = 'CSRF';
    }

    $username = trim((string) ($_POST['username'] ?? ''));
    $firstName = trim((string) ($_POST['first_name'] ?? ''));
    $lastName = trim((string) ($_POST['last_name'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $role = (string) ($_POST['role'] ?? 'druh');
    $harcerskiStopien = (string) ($_POST['harcerski_stopien'] ?? '');
    $instruktorskiStopien = (string) ($_POST['instruktorski_stopien'] ?? '');

    if ($username === '' || !preg_match('/^[a-zA-Z0-9_.-]{3,32}$/', $username)) {
        $errors[] = 'Login: 3-32 znaki (litery/cyfry/._-)';
    }

    if ($firstName === '' || !preg_match('/^[\p{L}][\p{L} \-\.]{1,49}$/u', $firstName)) {
        $errors[] = 'Imie: podaj poprawne imie';
    }

    if ($lastName === '' || !preg_match('/^[\p{L}][\p{L} \-\.]{1,49}$/u', $lastName)) {
        $errors[] = 'Nazwisko: podaj poprawne nazwisko';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Haslo: minimum 8 znakow';
    }

    if (!array_key_exists($harcerskiStopien, $harcerskiOptions)) {
        $errors[] = 'Nieprawidlowy stopien harcerski';
    }

    if ($instruktorskiStopien !== '' && !array_key_exists($instruktorskiStopien, $instruktorskiOptions)) {
        $errors[] = 'Nieprawidlowy stopien instruktorski';
    }

    $allowedRoles = ['admin', 'druh', 'druzynowy', 'zastepowy'];
    if (!in_array($role, $allowedRoles, true)) {
        $errors[] = 'Nieprawidlowa rola';
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare('INSERT INTO users (username, password, first_name, last_name, harcerski_stopien, instruktorski_stopien, role) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$username, $hash, $firstName, $lastName, $harcerskiStopien, $instruktorskiStopien !== '' ? $instruktorskiStopien : null, $role]);

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
    <title>Dodaj uzytkownika</title>
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
                    <h2 class="admin-title">Dodaj uzytkownika</h2>
                    <p class="admin-subtitle">Nowe konto z profilem i stopniami.</p>
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
                    <label>Imie</label>
                    <input type="text" name="first_name" value="<?= htmlspecialchars($firstName) ?>" required>
                </div>

                <div class="admin-field">
                    <label>Nazwisko</label>
                    <input type="text" name="last_name" value="<?= htmlspecialchars($lastName) ?>" required>
                </div>

                <div class="admin-field">
                    <label>Stopien harcerski</label>
                    <select name="harcerski_stopien" required>
                        <option value="" disabled <?= $harcerskiStopien === '' ? 'selected' : '' ?>>Wybierz stopien</option>
                        <?php foreach ($harcerskiOptions as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= $harcerskiStopien === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-field">
                    <label>Stopien instruktorski</label>
                    <select name="instruktorski_stopien">
                        <option value="" <?= $instruktorskiStopien === '' ? 'selected' : '' ?>>Brak</option>
                        <?php foreach ($instruktorskiOptions as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= $instruktorskiStopien === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-field">
                    <label>Haslo</label>
                    <input type="password" name="password" required>
                </div>

                <div class="admin-field">
                    <label>Rola</label>
                    <select name="role">
                        <option value="druh" <?= $role === 'druh' ? 'selected' : '' ?>>Druh</option>
                        <option value="zastepowy" <?= $role === 'zastepowy' ? 'selected' : '' ?>>Zastepowy</option>
                        <option value="druzynowy" <?= $role === 'druzynowy' ? 'selected' : '' ?>>Druzynowy</option>
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