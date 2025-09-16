<?php
require_once '../../includes/auth.php';
require_once '../../includes/utils.php';
require_once '../../includes/database.php';

requireRole('president');

$page_title = 'Create Proposal';
$org_id = getOrganizationId();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $type = $_POST['type'];
    $description = trim($_POST['description']);
    $budget_amount = floatval($_POST['budget_amount']);
    
    try {
        $sql = "INSERT INTO proposals (title, type, description, budget_amount, org_id, created_by, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'draft')";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$title, $type, $description, $budget_amount, $org_id, $_SESSION['user_id']])) {
            $proposal_id = $conn->lastInsertId();
            logActivity($_SESSION['user_id'], $org_id, 'Created new proposal', 'proposal', $proposal_id);
            header("Location: view.php?id=" . $proposal_id . "&success=1");
            exit();
        }
    } catch (PDOException $e) {
        $error = "Failed to create proposal: " . $e->getMessage();
    }
}

ob_start();
include 'views/create.php';
$content = ob_get_clean();

include '../../templates/base.php';