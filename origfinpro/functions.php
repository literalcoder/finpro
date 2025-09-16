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

function register($name, $email, $password, $role, $organization = null) {
    global $conn;
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return false; // Email already exists
    }
    
    // Insert new user (organization column doesn't exist in current schema)
    $hashedPassword = md5($password);
    $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    return $stmt->execute([$name, $email, $hashedPassword, $role]);
}
?>