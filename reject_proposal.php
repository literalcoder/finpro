<?php
include 'functions.php';
requireLogin();

$proposal_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$reason = isset($_GET['reason']) ? trim($_GET['reason']) : '';
$org_id = getOrganizationId();

if (!$proposal_id || !(hasRole('adviser') || hasRole('dean') || hasRole('ssc')) || empty($reason)) {
    header("Location: view_proposals.php");
    exit();
}

// Get and verify proposal exists
$sql = "SELECT * FROM proposals WHERE id = ? AND status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->execute([$proposal_id]);
$proposal = $stmt->fetch(PDO::FETCH_ASSOC);

if ($proposal) {
    // Begin transaction
    $conn->beginTransaction();
    try {
        // Update proposal status
        $update = $conn->prepare("UPDATE proposals SET status = 'rejected', updated_at = NOW() WHERE id = ?");
        $update->execute([$proposal_id]);
        
        // Store rejection reason
        $saveReason = $conn->prepare("INSERT INTO proposal_comments (proposal_id, user_id, comment_type, comment, created_at) VALUES (?, ?, 'rejection', ?, NOW())");
        $saveReason->execute([$proposal_id, $_SESSION['user_id'], $reason]);
        
        $conn->commit();
        header("Location: view_proposal_details.php?id=" . $proposal_id . "&success=1");
    } catch (Exception $e) {
        $conn->rollBack();
        header("Location: view_proposal_details.php?id=" . $proposal_id . "&error=1");
    }
} else {
    header("Location: view_proposals.php");
}
exit();