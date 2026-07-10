<?php
// Config/database.php - Connects to MySQL using PDO

$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3308';
$db   = getenv('DB_NAME') ?: 'learts_db';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // For production/development safety, show a generic message or details
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

