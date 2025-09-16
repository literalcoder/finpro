<?php
require_once '../../includes/auth.php';
require_once '../../includes/utils.php';
require_once '../../includes/database.php';

requireRole('president');

$page_title = 'Submit Terminal Report';
$org_id = getOrganizationId();
$error = '';
$success = '';

// Get current semester info
$current = getCurrentSemester();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $activities = trim($_POST['activities_summary']);
    $achievements = trim($_POST['achievements']);
    $challenges = trim($_POST['challenges']);
    $recommendations = trim($_POST['recommendations']);
    $financial_summary = trim($_POST['financial_summary']);
    
    try {
        $sql = "INSERT INTO terminal_reports (
                    org_id, semester, academic_year, activities_summary, 
                    achievements, challenges, recommendations, financial_summary,
                    created_by, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft')";
        
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([
            $org_id, 
            $current['semester'],
            $current['year'],
            $activities,
            $achievements,
            $challenges,
            $recommendations,
            $financial_summary,
            $_SESSION['user_id']
        ])) {
            $report_id = $conn->lastInsertId();
            logActivity($_SESSION['user_id'], $org_id, 'Created terminal report', 'terminal_report', $report_id);
            header("Location: view.php?id=" . $report_id . "&success=1");
            exit();
        }
    } catch (PDOException $e) {
        $error = "Failed to submit report: " . $e->getMessage();
    }
}

// Get financial summary for the current semester
$sql = "SELECT 
    COALESCE(SUM(CASE WHEN type IN ('income', 'collection') THEN amount END), 0) as total_income,
    COALESCE(SUM(CASE WHEN type IN ('expense', 'disbursement') THEN amount END), 0) as total_expenses
    FROM financial_transactions 
    WHERE org_id = ? 
    AND (
        (? = '1st Semester' AND MONTH(transaction_date) BETWEEN 8 AND 12) OR
        (? = '2nd Semester' AND MONTH(transaction_date) BETWEEN 1 AND 5) OR
        (? = 'Summer' AND MONTH(transaction_date) BETWEEN 6 AND 7)
    )
    AND YEAR(transaction_date) = ?";

$stmt = $conn->prepare($sql);
$stmt->execute([
    $org_id, 
    $current['semester'],
    $current['semester'],
    $current['semester'],
    date('Y')
]);
$finances = $stmt->fetch();

ob_start();
include 'views/submit.php';
$content = ob_get_clean();

include '../../templates/base.php';