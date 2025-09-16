<?php
require_once '../../includes/auth.php';
require_once '../../includes/utils.php';
require_once '../../includes/database.php';

requireAnyRole(['admin', 'president']);

$page_title = 'Manage Members';
$org_id = getOrganizationId();
$error = '';
$success = '';

// Handle member status changes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    $action = $_POST['action'];
    
    try {
        switch ($action) {
            case 'approve':
                $sql = "UPDATE users SET status = 'active' WHERE id = ? AND org_id = ?";
                $message = "Your membership has been approved!";
                $type = 'success';
                break;
            case 'deactivate':
                $sql = "UPDATE users SET status = 'inactive' WHERE id = ? AND org_id = ?";
                $message = "Your account has been deactivated";
                $type = 'warning';
                break;
            case 'remove':
                $sql = "UPDATE users SET org_id = NULL, role = 'user' WHERE id = ?";
                $message = "You have been removed from the organization";
                $type = 'danger';
                break;
        }
        
        $stmt = $conn->prepare($sql);
        if ($action === 'remove') {
            $stmt->execute([$user_id]);
        } else {
            $stmt->execute([$user_id, $org_id]);
        }
        
        createNotification(
            $user_id,
            $org_id,
            "Membership Status Updated",
            $message,
            $type,
            'membership',
            $user_id
        );
        
        $success = "Member status updated successfully!";
    } catch (Exception $e) {
        $error = "Failed to update member status: " . $e->getMessage();
    }
}

// Get organization members
$sql = "SELECT u.*, r.name as role_name 
        FROM users u 
        LEFT JOIN roles r ON u.role = r.code
        WHERE u.org_id = ? 
        ORDER BY u.name";
$stmt = $conn->prepare($sql);
$stmt->execute([$org_id]);
$members = $stmt->fetchAll();

ob_start();
include 'views/members.php';
$content = ob_get_clean();

include '../../templates/base.php';