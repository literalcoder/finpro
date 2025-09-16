<?php
include 'functions.php';
requireLogin();

$proposal_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$org_id = getOrganizationId();

if (!$proposal_id || !(hasRole('adviser') || hasRole('dean') || hasRole('ssc'))) {
    header("Location: view_proposals.php");
    exit();
}

// Get and verify proposal exists
$sql = "SELECT * FROM proposals WHERE id = ? AND status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->execute([$proposal_id]);
$proposal = $stmt->fetch(PDO::FETCH_ASSOC);

if ($proposal) {
    // Update proposal status
    $update = $conn->prepare("UPDATE proposals SET status = 'approved', updated_at = NOW() WHERE id = ?");
    $update->execute([$proposal_id]);
    
    // Redirect back with success message
    header("Location: view_proposal_details.php?id=" . $proposal_id . "&success=1");
} else {
    header("Location: view_proposals.php");
}
exit();