<?php

$host = 'localhost';
$dbname = 'mealmate_v2'; 
$username = 'root'; 
$password = ''; 
try {
    // Create the PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set PDO to throw exceptions on errors (great for debugging)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // If it fails, stop the page and print the error
    die("Database connection failed: " . $e->getMessage());
}
?>