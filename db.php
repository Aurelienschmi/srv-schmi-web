<?php
$dotenv = parse_ini_file(__DIR__ . '/.env');
$pdo = new PDO(
    "mysql:host={$dotenv['DB_HOST']};dbname={$dotenv['DB_NAME']};charset=utf8",
    $dotenv['DB_USER'],
    $dotenv['DB_PASS']
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>