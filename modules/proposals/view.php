<?php
require_once '../../includes/auth.php';
require_once '../../includes/utils.php';
require_once '../../includes/database.php';

requireAnyRole(['president', 'treasurer', 'adviser', 'dean', 'ssc']);

$page_title = 'View Proposal';
$proposal_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$org_id = getOrganizationId();
$error = '';
$success = isset($_GET['success']) ? 'Proposal updated successfully!' : '';

// Get proposal details with creator info
$sql = "SELECT p.*, u.name as created_by_name, o.name as org_name 
        FROM proposals p 
        LEFT JOIN users u ON p.created_by = u.id 
        LEFT JOIN organizations o ON p.org_id = o.id 
        WHERE p.id = ? AND (p.org_id = ? OR ? IS NULL)";
$stmt = $conn->prepare($sql);
$stmt->execute([$proposal_id, $org_id, hasRole('admin') ? null : $org_id]);
$proposal = $stmt->fetch();

if (!$proposal) {
    header("Location: index.php");
    exit();
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $allowed = false;
    $new_status = '';
    $action = $_POST['action'];
    
    if ($action === 'submit' && hasRole('president') && $proposal['status'] === 'draft') {
        $allowed = true;
        $new_status = 'pending';
    } elseif ($action === 'approve' && hasAnyRole(['adviser', 'dean', 'ssc']) && $proposal['status'] === 'pending') {
        $allowed = true;
        $new_status = 'approved';
    } elseif ($action === 'reject' && hasAnyRole(['adviser', 'dean', 'ssc']) && $proposal['status'] === 'pending') {
        $allowed = true;
        $new_status = 'rejected';
    }
    
    if ($allowed && $new_status) {
        $sql = "UPDATE proposals SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$new_status, $proposal_id])) {
            logActivity($_SESSION['user_id'], $org_id, ucfirst($action) . 'd proposal', 'proposal', $proposal_id);
            
            // Create notification
            $notif_type = match($new_status) {
                'approved' => 'success',
                'rejected' => 'danger',
                'pending' => 'warning',
                default => 'info'
            };
            
            createOrgNotification(
                $org_id,
                "Proposal " . ucfirst($new_status),
                "The proposal '{$proposal['title']}' has been " . ucfirst($new_status),
                $notif_type,
                'proposal',
                $proposal_id
            );
            
            header("Location: view.php?id=" . $proposal_id . "&success=1");
            exit();
        }
    }
}

ob_start();
include 'views/view.php';
$content = ob_get_clean();

include '../../templates/base.php';