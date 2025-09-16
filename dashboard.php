<?php
include 'functions.php';
requireLogin();

$role_dashboards = [
  'admin' => 'admin_dashboard.php',
  'treasurer' => 'treasurer_dashboard.php',
  'president' => 'president_dashboard.php',
  'adviser',
  'dean',
  'ssc' => 'pressident_dashboard.php',
  'dean' => 'reviewer',
  'ssc' => 'reviewer',
  'auditor' => 'auditor'
];

foreach ($role_dashboards as $role => $dashboard) {
  if (hasRole($role)) {
    header('Location: ' . $dashboard);
    exit();
  }
}

// Default redirect for unauthorized access
header('Location: index.php');
exit();
