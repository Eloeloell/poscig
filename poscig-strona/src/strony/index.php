<?php session_start();
require __DIR__ . '/csrf.php';
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
} ?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Panel Kadry</title>
</head>

<body>
    <h2>Logowanie kadry drużyny</h2>
    <form method="post" action="login.php"> <input type="hidden" name="csrf"
            value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"> <input type="text" name="username"
            placeholder="Login" required> <input type="password" name="password" placeholder="Hasło" required> <button
            type="submit">Zaloguj</button> </form>
</body>

</html>