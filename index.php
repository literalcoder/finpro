<?php
// index.php - Entry point for OrgFinPro application
include 'functions.php';

// If user is already logged in, redirect to dashboard
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

// Otherwise, redirect to login page
header("Location: login.php");
exit();
?>
