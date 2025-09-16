<?php
require_once '../../includes/auth.php';
require_once '../../includes/utils.php';
require_once '../../includes/database.php';

requireAnyRole(['admin', 'treasurer', 'president']);

$page_title = 'Reports';
$org_id = getOrganizationId();

// Get date range
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$semester = $_GET['semester'] ?? getCurrentSemester()['semester'];

// Get organization details
$org = null;
if ($org_id) {
    $stmt = $conn->prepare("SELECT * FROM organizations WHERE id = ?");
    $stmt->execute([$org_id]);
    $org = $stmt->fetch();
}

// Get financial summary
$sql = "SELECT 
    COALESCE(SUM(CASE WHEN type IN ('income', 'collection') THEN amount END), 0) as total_income,
    COALESCE(SUM(CASE WHEN type IN ('expense', 'disbursement') THEN amount END), 0) as total_expenses,
    COUNT(*) as transaction_count
    FROM financial_transactions 
    WHERE org_id = ? 
    AND YEAR(transaction_date) = ?";
if ($semester) {
    $sql .= " AND (
        (? = '1st Semester' AND MONTH(transaction_date) BETWEEN 8 AND 12) OR
        (? = '2nd Semester' AND MONTH(transaction_date) BETWEEN 1 AND 5) OR
        (? = 'Summer' AND MONTH(transaction_date) BETWEEN 6 AND 7)
    )";
}

$stmt = $conn->prepare($sql);
$params = [$org_id, $year];
if ($semester) {
    $params[] = $semester;
    $params[] = $semester;
    $params[] = $semester;
}
$stmt->execute($params);
$summary = $stmt->fetch();

// Get proposals summary
$sql = "SELECT status, COUNT(*) as count 
        FROM proposals 
        WHERE org_id = ? 
        AND YEAR(created_at) = ?";
if ($semester) {
    $sql .= " AND (
        (? = '1st Semester' AND MONTH(created_at) BETWEEN 8 AND 12) OR
        (? = '2nd Semester' AND MONTH(created_at) BETWEEN 1 AND 5) OR
        (? = 'Summer' AND MONTH(created_at) BETWEEN 6 AND 7)
    )";
}
$sql .= " GROUP BY status";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$proposals = $stmt->fetchAll();

ob_start();
include 'views/summary.php';
$content = ob_get_clean();

include '../../templates/base.php';