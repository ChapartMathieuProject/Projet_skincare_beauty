
<?php
$host = 'localhost';
$db = 'Skincarebeauty';
$user = 'root';
$pass = 'jean';

$dsn = "mysql:host=$host;dbname=$db;charset=ut8";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Erreur de connexion à la base : ' . $e->getMessage());
}

?>