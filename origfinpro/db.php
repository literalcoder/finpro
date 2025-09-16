
<?php
// MySQL Database Configuration
$host = '127.0.0.1';
$dbname = 'origfinpro';
$username = 'root';
$password = '';

try {
    $dsn = "mysql:unix_socket=/tmp/mysql.sock;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
