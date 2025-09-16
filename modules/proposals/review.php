<?php
require_once '../../includes/auth.php';
require_once '../../includes/utils.php';
require_once '../../includes/database.php';

requireAnyRole(['adviser', 'dean', 'ssc']);

$page_title = 'Reviewer Dashboard';
$user_role = $_SESSION['user_role'];

// Get pending proposals
$sql = "SELECT p.*, o.name as org_name, u.name as submitted_by 
        FROM proposals p 
        JOIN organizations o ON p.org_id = o.id 
        JOIN users u ON p.created_by = u.id 
        WHERE p.status = 'pending' 
        ORDER BY p.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$pending_proposals = $stmt->fetchAll();

// Get recent approvals/rejections
$sql = "SELECT p.*, o.name as org_name, u.name as submitted_by 
        FROM proposals p 
        JOIN organizations o ON p.org_id = o.id 
        JOIN users u ON p.created_by = u.id 
        WHERE p.status IN ('approved', 'rejected') 
        ORDER BY p.updated_at DESC 
        LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->execute();
$recent_reviews = $stmt->fetchAll();

ob_start();
include 'views/review_dashboard.php';
$content = ob_get_clean();

include '../../templates/base.php';