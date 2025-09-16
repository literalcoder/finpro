<?php
require_once 'includes/auth.php';
require_once 'includes/utils.php';
require_once 'includes/database.php';

requireRole('treasurer');

$page_title = 'Treasurer Dashboard';
$org_id = getOrganizationId();

// Get financial summary
$sql = "SELECT 
    COALESCE(SUM(CASE WHEN type IN ('income', 'collection') THEN amount END), 0) as total_income,
    COALESCE(SUM(CASE WHEN type IN ('expense', 'disbursement') THEN amount END), 0) as total_expenses,
    COUNT(*) as transaction_count
    FROM financial_transactions 
    WHERE org_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$org_id]);
$summary = $stmt->fetch();

// Get recent transactions
$sql = "SELECT ft.*, u.name as created_by_name 
        FROM financial_transactions ft 
        LEFT JOIN users u ON ft.created_by = u.id 
        WHERE ft.org_id = ? 
        ORDER BY ft.transaction_date DESC, ft.created_at DESC 
        LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->execute([$org_id]);
$transactions = $stmt->fetchAll();

// Get pending liquidations
$sql = "SELECT p.*, o.name as org_name 
        FROM proposals p 
        JOIN organizations o ON p.org_id = o.id 
        WHERE p.org_id = ? AND p.status = 'approved' 
        AND NOT EXISTS (
            SELECT 1 FROM financial_transactions 
            WHERE proposal_id = p.id AND type = 'liquidation'
        )";
$stmt = $conn->prepare($sql);
$stmt->execute([$org_id]);
$pending_liquidations = $stmt->fetchAll();

ob_start();
include 'views/dashboard.php';
$content = ob_get_clean();

include 'templates/base.php';