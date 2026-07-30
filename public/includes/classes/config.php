<?php

function getConnexion(): \PDO
{
    $host     = getenv('DB_HOST')     ?: '127.0.0.1';
    $dbname   = getenv('DB_NAME')     ?: 'skincarebeauty';
    $user     = getenv('DB_USER')     ?: 'root';
    $password = getenv('DB_PASSWORD') ?: 'jean';

    try {
        $pdo = new \PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $user,
            $password,
            [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]
        );
        return $pdo;

    } catch (\PDOException $e) {
        die('Erreur de connexion : ' . $e->getMessage());
    }
}