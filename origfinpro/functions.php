<?php
session_start();
include 'db.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function login($email, $password) {
    global $conn;
    $sql = "SELECT * FROM users WHERE email=? AND password=? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $hashedPassword = md5($password);
    $stmt->execute([$email, $hashedPassword]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>