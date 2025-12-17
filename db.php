<?php
require_once '/var/www/html/db_config.php';
$host = DB_SERVER;    
$db   = DB_DATABASE;  
$user = DB_USERNAME;  
$pass = DB_PASSWORD;  
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $conn = $pdo;
    // Optional: Echo a message for testing (Remove this before final submission)
    // echo "Connected successfully to RDS!"; 
} catch (\PDOException $e) {
    // In production, log this error instead of showing it to the user
    die("Connection failed: " . $e->getMessage()); 
}
?>