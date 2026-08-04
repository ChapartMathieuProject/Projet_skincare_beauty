<?php
// Entités
require_once __DIR__ . '/classes/Entity/Product.php';
require_once __DIR__ . '/classes/Entity/Brand.php';
require_once __DIR__ . '/classes/Entity/ProductType.php';
require_once __DIR__ . '/classes/Entity/LoyaltyPoint.php';
require_once __DIR__ . '/classes/Entity/LoyaltyTier.php';
require_once __DIR__ . '/classes/Entity/LoyaltyVoucher.php';

// Services 
require_once __DIR__ . '/classes/Service/LoyaltyService.php';

// DAO
require_once __DIR__ . '/classes/DAO/DAO.php';
require_once __DIR__ . '/classes/DAO/ProductDAO.php';
require_once __DIR__ . '/classes/DAO/BrandDAO.php';
require_once __DIR__ . '/classes/DAO/ProductTypeDAO.php';
require_once __DIR__ . '/classes/DAO/LoyaltyPointDAO.php';
require_once __DIR__ . '/classes/DAO/LoyaltyTierDAO.php';
require_once __DIR__ . '/classes/DAO/LoyaltyVoucherDAO.php';

$host    = getenv('DB_HOST')     ?: 'localhost';
$db      = getenv('DB_NAME')     ?: 'Skincarebeauty';
$user    = getenv('DB_USER')     ?: 'root';
$pass    = getenv('DB_PASSWORD') ?: '';
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