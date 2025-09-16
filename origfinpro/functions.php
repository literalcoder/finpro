<?php
// Set secure session configuration
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.cookie_samesite', 'Lax');

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
    $sql = "SELECT * FROM users WHERE email=? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['org_id'] = $user['org_id'];
        // Generate CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return true;
    }
    return false;
}

function logout() {
    $_SESSION = [];
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    session_destroy();
    header("Location: login.php");
    exit();
}

function register($name, $email, $password, $role = 'president', $org_name = null) {
    global $conn;
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return false; // Email already exists
    }
    
    // Server-side role validation - prevent privilege escalation
    $allowedRoles = ['president', 'treasurer', 'auditor', 'adviser', 'dean', 'ssc', 'admin'];
    if (!in_array($role, $allowedRoles)) {
        $role = 'president'; // Default to safe role
    }
    
    // Find organization ID by name if provided
    $org_id = null;
    if ($org_name) {
        $stmt = $conn->prepare("SELECT id FROM organizations WHERE name = ? OR code = ? LIMIT 1");
        $stmt->execute([$org_name, $org_name]);
        $org = $stmt->fetch();
        if ($org) {
            $org_id = $org['id'];
        }
    }
    
    // Hash password securely
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (name, email, password, role, org_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    return $stmt->execute([$name, $email, $hashedPassword, $role, $org_id]);
}

// CSRF protection functions
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Output escaping function
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Role-based access control
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

function hasAnyRole($roles) {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], $roles);
}

function requireRole($role) {
    if (!hasRole($role)) {
        http_response_code(403);
        die("Access denied. Insufficient privileges.");
    }
}

function requireAnyRole($roles) {
    if (!hasAnyRole($roles)) {
        http_response_code(403);
        die("Access denied. Insufficient privileges.");
    }
}

// Organization scoping
function getOrganizationId() {
    return isset($_SESSION['org_id']) ? $_SESSION['org_id'] : null;
}

function requireOrganization() {
    if (!getOrganizationId() && !hasRole('admin')) {
        http_response_code(403);
        die("Access denied. Organization context required.");
    }
}
?>