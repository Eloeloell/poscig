<?php
declare(strict_types=1);

/* ====== DANE Z PLESK ====== */
$db_host = 'localhost';
$db_name = 'kadrowka';
$db_user = 'kadra';
$db_pass = 'kadrowkaZHR@as';

/* ====== OPCJE PDO ====== */
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        $options
    );
} catch (PDOException $e) {
    die('DB ERROR: ' . $e->getMessage());
}
