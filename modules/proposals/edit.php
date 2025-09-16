<?php
require_once '../../includes/auth.php';
require_once '../../includes/utils.php';
require_once '../../includes/database.php';

requireRole('president');

$page_title = 'Edit Proposal';
$proposal_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$org_id = getOrganizationId();
$error = '';
$success = '';

// Get proposal details
$sql = "SELECT * FROM proposals WHERE id = ? AND org_id = ? AND status = 'draft'";
$stmt = $conn->prepare($sql);
$stmt->execute([$proposal_id, $org_id]);
$proposal = $stmt->fetch();

if (!$proposal) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $type = $_POST['type'];
    $description = trim($_POST['description']);
    $budget_amount = floatval($_POST['budget_amount']);
    
    try {
        $sql = "UPDATE proposals 
                SET title = ?, type = ?, description = ?, budget_amount = ?, updated_at = NOW() 
                WHERE id = ? AND org_id = ? AND status = 'draft'";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$title, $type, $description, $budget_amount, $proposal_id, $org_id])) {
            logActivity($_SESSION['user_id'], $org_id, 'Updated proposal', 'proposal', $proposal_id);
            header("Location: view.php?id=" . $proposal_id . "&success=1");
            exit();
        }
    } catch (PDOException $e) {
        $error = "Failed to update proposal: " . $e->getMessage();
    }
}

ob_start();
include 'views/edit.php';
$content = ob_get_clean();

include '../../templates/base.php';