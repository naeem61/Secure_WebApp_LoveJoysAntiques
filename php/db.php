<?php
$host = 'localhost';
$db = 'lovejoy_antiques';
$user = 'root';
$pass = '';

date_default_timezone_set('Europe/London');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

?>