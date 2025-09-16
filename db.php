<?php
// db.php

$host = '127.0.0.1';
$username = 'root';
$password = '';
$dbname = 'orgfinpro'; // Changed from orgfinpro to origfinpro to match the init_mysql_schema.sql

try {
    $dsn = "mysql:unix_socket=/tmp/mysql.sock;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully";
} catch(PDOException $e) {
    // echo "Connection failed: " . $e->getMessage();
    // In a real application, you would log this error and display a user-friendly message.
    die("Database connection failed. Please check server logs for details.");
}

?>