<?php
require_once 'includes/auth.php';
require_once 'includes/utils.php';
require_once 'includes/database.php';

requireRole('treasurer');

$page_title = 'Record Transaction';
$org_id = getOrganizationId();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $amount = floatval($_POST['amount']);
    $description = trim($_POST['description']);
    $transaction_date = $_POST['transaction_date'];
    $proposal_id = !empty($_POST['proposal_id']) ? intval($_POST['proposal_id']) : null;
    
    if ($amount <= 0) {
        $error = "Amount must be greater than 0";
    } else {
        try {
            $conn->beginTransaction();
            
            $sql = "INSERT INTO financial_transactions (org_id, type, amount, description, created_by, transaction_date, proposal_id) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if ($stmt->execute([$org_id, $type, $amount, $description, $_SESSION['user_id'], $transaction_date, $proposal_id])) {
                $transaction_id = $conn->lastInsertId();
                
                // Create notification for organization members
                $notif_message = sprintf(
                    "New %s transaction recorded: %s - %s",
                    $type,
                    formatAmount($amount),
                    $description
                );
                
                createOrgNotification(
                    $org_id,
                    "New Financial Transaction",
                    $notif_message,
                    in_array($type, ['income', 'collection']) ? 'success' : 'warning',
                    'transaction',
                    $transaction_id
                );
                
                $conn->commit();
                $success = "Transaction recorded successfully!";
            }
        } catch (Exception $e) {
            $conn->rollBack();
            $error = "Failed to record transaction: " . $e->getMessage();
        }
    }
}

// Get pending proposals for liquidation
$sql = "SELECT id, title FROM proposals WHERE org_id = ? AND status = 'approved'";
$stmt = $conn->prepare($sql);
$stmt->execute([$org_id]);
$proposals = $stmt->fetchAll();

ob_start();
include 'views/transaction_form.php';
$content = ob_get_clean();

include 'templates/base.php';