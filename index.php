<?php
// index.php - Entry point for OrgFinPro application

// Turn off error display
ini_set('display_errors', 1);

// Ensure errors are still logged
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/errors.log'); // You can specify the path to your error log

define('ALLOW_DIRECT_ACCESS', true);
// Base directory for includes
define('BASE_PATH', __DIR__);

// Include core files
require_once 'config/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/utils.php';

requireLogin();

if (isset($_GET['page'])) {
    $page = $_GET['page'];
    switch ($page) {
        case 'auth/login':
            include('auth/login.php');
            break;
        case 'admin':
            include('modules/admin/index.php');
            break;
        case 'financial':
            include('modules/financial/index.php');
            break;
        case 'proposals':
            include('modules/proposals/index.php');
            break;
        case 'review':
            include('modules/proposals/review.php');
            break;
        case 'financial/transaction/new':
            include('modules/financial/transaction.php');
            break;
        case "dashboard":
            include('dashboard.php');
        default:
            echo ("Page not found.");
            break;
    }
}

// Route to appropriate dashboard based on role
$redirect = match ($_SESSION['user_role']) {
    'admin' => 'admin',
    'treasurer' => 'financial',
    'president' => 'proposals',
    'adviser', 'dean', 'ssc' => 'review',
    default => 'home'
};

header("Location: " . $redirect);
exit();
?>