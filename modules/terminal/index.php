<?php
require_once '../../includes/auth.php';
require_once '../../includes/utils.php';
require_once '../../includes/database.php';

requireRole('president');

$page_title = 'Terminal Reports';
$org_id = getOrganizationId();

// Create terminal_reports table if not exists
$createTable = "CREATE TABLE IF NOT EXISTS terminal_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    org_id INT NOT NULL,
    semester VARCHAR(50) NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    activities_summary TEXT,
    achievements TEXT,
    challenges TEXT,
    recommendations TEXT,
    financial_summary TEXT,
    status ENUM('draft', 'submitted', 'approved', 'needs_revision') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (org_id) REFERENCES organizations(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
)";
$conn->exec($createTable);

// Get existing terminal reports
$sql = "SELECT tr.*, u.name as created_by_name, o.name as org_name
        FROM terminal_reports tr 
        LEFT JOIN users u ON tr.created_by = u.id 
        LEFT JOIN organizations o ON tr.org_id = o.id
        WHERE tr.org_id = ? 
        ORDER BY tr.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$org_id]);
$reports = $stmt->fetchAll();

ob_start();
include 'views/list.php';
$content = ob_get_clean();

include '../../templates/base.php';