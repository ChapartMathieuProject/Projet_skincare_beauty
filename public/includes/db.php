<?php
require_once __DIR__ . '/classes/Entity/Product.php';
require_once __DIR__ . '/classes/DAO/DAO.php';
require_once __DIR__ . '/classes/DAO/ProductDAO.php';

$host = 'localhost';
$db   = 'Skincarebeauty';
$user = 'root';
$pass = 'jean';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Erreur de connexion à la base : ' . $e->getMessage());
}
?>