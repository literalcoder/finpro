<?php
require_once '../../includes/auth.php';
require_once '../../includes/utils.php';
require_once '../../includes/database.php';
require_once '../../includes/excel.php';

requireAnyRole(['admin', 'treasurer', 'president']);

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$semester = $_GET['semester'] ?? '';
$org_id = getOrganizationId();

// Get organization name
$org_name = '';
if ($org_id) {
    $stmt = $conn->prepare("SELECT name FROM organizations WHERE id = ?");
    $stmt->execute([$org_id]);
    $org_name = $stmt->fetchColumn();
}

// Get financial transactions
$sql = "SELECT 
    t.transaction_date,
    t.type,
    t.amount,
    t.description,
    u.name as recorded_by
    FROM financial_transactions t
    LEFT JOIN users u ON t.created_by = u.id 
    WHERE t.org_id = ? 
    AND YEAR(t.transaction_date) = ?";

if ($semester) {
    $sql .= " AND (
        (? = '1st Semester' AND MONTH(t.transaction_date) BETWEEN 8 AND 12) OR
        (? = '2nd Semester' AND MONTH(t.transaction_date) BETWEEN 1 AND 5) OR
        (? = 'Summer' AND MONTH(t.transaction_date) BETWEEN 6 AND 7)
    )";
}
$sql .= " ORDER BY t.transaction_date";

$stmt = $conn->prepare($sql);
$params = [$org_id, $year];
if ($semester) {
    $params[] = $semester;
    $params[] = $semester;
    $params[] = $semester;
}
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Prepare data for Excel
$headers = ['Date', 'Type', 'Amount', 'Description', 'Recorded By'];
$data = [];
foreach ($transactions as $t) {
    $data[] = [
        date('M j, Y', strtotime($t['transaction_date'])),
        ucfirst($t['type']),
        $t['amount'],
        $t['description'],
        $t['recorded_by']
    ];
}

// Generate filename
$filename = sprintf(
    'Financial_Report_%s_%s%s.xlsx',
    $org_name ? str_replace(' ', '_', $org_name) . '_' : '',
    $year,
    $semester ? '_' . str_replace(' ', '_', $semester) : ''
);

// Generate and output Excel file
generateExcelFile($data, $headers, $filename);