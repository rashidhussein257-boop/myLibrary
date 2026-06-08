<?php
$host = "localhost";
$db_name = "library_db";
$username = "root";
$password = ""; // Kawaida XAMPP haina password

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    // Weka error mode kuwa Exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>