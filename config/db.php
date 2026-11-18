<?php
$host = '127.0.0.1';
$db   = 'univ_clubs';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("🔴 Erreur Base de Données : " . $e->getMessage() . "<br><small>Vérifiez que la base 'univ_clubs' existe dans phpMyAdmin.</small>");
}
?>