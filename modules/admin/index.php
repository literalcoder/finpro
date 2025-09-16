<?php
require_once '../../includes/auth.php';
require_once '../../includes/utils.php';
require_once '../../includes/database.php';

requireRole('admin');

$page_title = 'Admin Dashboard';

// Get system statistics
$stats = [
    'users' => $conn->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'organizations' => $conn->query("SELECT COUNT(*) FROM organizations")->fetchColumn(),
    'active_orgs' => $conn->query("SELECT COUNT(*) FROM organizations WHERE status = 'active'")->fetchColumn(),
    'proposals' => $conn->query("SELECT COUNT(*) FROM proposals")->fetchColumn()
];

// Get recent organizations
$sql = "SELECT o.*, 
        (SELECT COUNT(*) FROM users WHERE org_id = o.id) as member_count,
        (SELECT COUNT(*) FROM proposals WHERE org_id = o.id) as proposal_count
        FROM organizations o 
        ORDER BY o.created_at DESC LIMIT 5";
$recent_orgs = $conn->query($sql)->fetchAll();

// Get recent user registrations
$sql = "SELECT u.*, o.name as org_name 
        FROM users u 
        LEFT JOIN organizations o ON u.org_id = o.id 
        ORDER BY u.created_at DESC LIMIT 5";
$recent_users = $conn->query($sql)->fetchAll();

// Get system activity log
$sql = "SELECT a.*, u.name as user_name, o.name as org_name 
        FROM audit_logs a 
        LEFT JOIN users u ON a.user_id = u.id 
        LEFT JOIN organizations o ON a.org_id = o.id 
        ORDER BY a.created_at DESC LIMIT 10";
$activities = $conn->query($sql)->fetchAll();

ob_start();
include 'views/dashboard.php';
$content = ob_get_clean();

include '../../templates/base.php';