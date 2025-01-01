<?php
// Database connection details
$host = 'localhost'; // Change if your database is hosted elsewhere
$dbname = 'bebalridge'; // Replace with your database name
$username = 'root'; // Replace with your database username
$password = ''; // Replace with your database password

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Optional: Set the character set to UTF-8
    $pdo->exec("set names utf8");
    
    // Uncomment the line below to confirm the connection
    
} catch (PDOException $e) {
    // Handle connection error
    echo "Connection failed: " . $e->getMessage();
}
?>

