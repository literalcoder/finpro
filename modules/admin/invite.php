<?php
require_once '../../includes/auth.php';
require_once '../../includes/utils.php';
require_once '../../includes/database.php';

requireAnyRole(['admin', 'president']);

$page_title = 'Invite Members';
$org_id = getOrganizationId();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emails = array_map('trim', explode(',', $_POST['emails']));
    $role = $_POST['role'];
    $message = trim($_POST['message']);
    
    try {
        $conn->beginTransaction();
        
        foreach ($emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            
            // Check if user exists
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Update existing user
                if (!$user['org_id']) {
                    $stmt = $conn->prepare("UPDATE users SET org_id = ?, role = ?, status = 'pending' WHERE id = ?");
                    $stmt->execute([$org_id, $role, $user['id']]);
                    
                    createNotification(
                        $user['id'],
                        $org_id,
                        "Organization Invitation",
                        $message ?: "You have been invited to join an organization",
                        'info',
                        'membership',
                        $user['id']
                    );
                }
            } else {
                // Create new user with temporary password
                $temp_password = bin2hex(random_bytes(8));
                $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("INSERT INTO users (email, name, password, role, org_id, status) VALUES (?, ?, ?, ?, ?, 'pending')");
                $stmt->execute([$email, explode('@', $email)[0], $hashed_password, $role, $org_id]);
                
                // Send invitation email with temporary password
                require_once '../../includes/mailer.php';
                sendInvitationEmail($email, $temp_password, $message);
            }
        }
        
        $conn->commit();
        $success = "Invitations sent successfully!";
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Failed to send invitations: " . $e->getMessage();
    }
}

// Get available roles
$sql = "SELECT code, name FROM roles WHERE code != 'admin'";
$roles = $conn->query($sql)->fetchAll();

ob_start();
include 'views/invite.php';
$content = ob_get_clean();

include '../../templates/base.php';