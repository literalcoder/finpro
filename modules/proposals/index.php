<?php
require_once 'includes/auth.php';
require_once 'includes/utils.php';
require_once 'includes/database.php';

requireAnyRole(['president', 'adviser', 'dean', 'ssc']);

$page_title = 'Proposals Management';
$org_id = getOrganizationId();

// Get proposal statistics and list
$stats = [
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0
];

$sql = "SELECT status, COUNT(*) as count FROM proposals WHERE org_id = ? GROUP BY status";
$stmt = $conn->prepare($sql);
$stmt->execute([$org_id]);
while ($row = $stmt->fetch()) {
    $stats[$row['status']] = $row['count'];
}

// Get recent proposals
$sql = "SELECT p.*, u.name as created_by_name 
        FROM proposals p 
        LEFT JOIN users u ON p.created_by = u.id 
        WHERE p.org_id = ? 
        ORDER BY p.created_at DESC LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->execute([$org_id]);
$proposals = $stmt->fetchAll();

ob_start();
include 'views/list.php';
$content = ob_get_clean();

include '../../templates/base.php';