<?php
    // Database connection settings
    $host = "localhost";
    $dbname = "shop";       // database name
    $username = "root";     // default XAMPP username
    $password = "";         // default XAMPP password is empty

    try {
        // Create PDO connection
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        // Set error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
?>
