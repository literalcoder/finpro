<?php
require_once '../../includes/auth.php';
require_once '../../includes/utils.php';
require_once '../../includes/database.php';

requireAnyRole(['president', 'adviser', 'dean', 'ssc']);

$page_title = 'View Terminal Report';
$report_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$org_id = getOrganizationId();
$error = '';
$success = isset($_GET['success']) ? 'Report updated successfully!' : '';

// Get report details
$sql = "SELECT tr.*, u.name as created_by_name, o.name as org_name 
        FROM terminal_reports tr 
        LEFT JOIN users u ON tr.created_by = u.id 
        LEFT JOIN organizations o ON tr.org_id = o.id 
        WHERE tr.id = ? AND (tr.org_id = ? OR ? IS NULL)";
$stmt = $conn->prepare($sql);
$stmt->execute([$report_id, $org_id, hasRole('admin') ? null : $org_id]);
$report = $stmt->fetch();

if (!$report) {
    header("Location: index.php");
    exit();
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $allowed = false;
    $new_status = '';
    $action = $_POST['action'];
    
    if ($action === 'submit' && hasRole('president') && $report['status'] === 'draft') {
        $allowed = true;
        $new_status = 'submitted';
    } elseif ($action === 'approve' && hasAnyRole(['adviser', 'dean', 'ssc']) && $report['status'] === 'submitted') {
        $allowed = true;
        $new_status = 'approved';
    } elseif ($action === 'revise' && hasAnyRole(['adviser', 'dean', 'ssc']) && $report['status'] === 'submitted') {
        $allowed = true;
        $new_status = 'needs_revision';
    }
    
    if ($allowed && $new_status) {
        $sql = "UPDATE terminal_reports SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$new_status, $report_id])) {
            logActivity($_SESSION['user_id'], $org_id, 'Updated terminal report status', 'terminal_report', $report_id);
            
            // Create notification
            $notif_type = match($new_status) {
                'approved' => 'success',
                'needs_revision' => 'warning',
                'submitted' => 'info',
                default => 'info'
            };
            
            createOrgNotification(
                $org_id,
                "Terminal Report " . ucfirst(str_replace('_', ' ', $new_status)),
                "The terminal report for {$report['semester']} - {$report['academic_year']} has been " . str_replace('_', ' ', $new_status),
                $notif_type,
                'terminal_report',
                $report_id
            );
            
            header("Location: view.php?id=" . $report_id . "&success=1");
            exit();
        }
    }
}

ob_start();
include 'views/view.php';
$content = ob_get_clean();

include '../../templates/base.php';