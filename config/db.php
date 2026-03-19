<?php
$env = parse_ini_file(__DIR__ . '/../.env');

if ($env === false) {
    die(".env file could not be loaded.");
}

$host = $env["DB_HOST"];
$dbname = $env["DB_NAME"];
$user = $env["DB_USER"];
$password = $env["DB_PASS"];

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>