<?php
require_once __DIR__ . '/../config/config.php';
session_start();

function requireLogin()
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . SITE_URL . '/auth/login');
        exit();
    }
}

function requireRole($role)
{
    requireLogin();
    if ($_SESSION['user_role'] !== $role) {
        header('Location: ' . SITE_URL . '/index.php');
        exit();
    }
}

function requireAnyRole($roles)
{
    requireLogin();
    if (!in_array($_SESSION['user_role'], $roles)) {
        header('Location: ' . SITE_URL . '/index.php');
        exit();
    }
}

function hasRole($role)
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

function hasAnyRole($roles)
{
    return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], $roles);
}

function getCurrentUser()
{
    global $conn;
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function getOrganizationId()
{
    return $_SESSION['org_id'] ?? null;
}
