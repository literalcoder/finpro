<?php
require_once '../../includes/auth.php';
require_once '../../includes/utils.php';
require_once '../../includes/database.php';
require_once '../../vendor/autoload.php';

requireAnyRole(['president', 'adviser', 'dean', 'ssc']);

$report_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$org_id = getOrganizationId();

// Get report details
$sql = "SELECT tr.*, u.name as created_by_name, o.name as org_name 
        FROM terminal_reports tr 
        LEFT JOIN users u ON tr.created_by = u.id 
        LEFT JOIN organizations o ON tr.org_id = o.id 
        WHERE tr.id = ? AND (tr.org_id = ? OR ? IS NULL)";
$stmt = $conn->prepare($sql);
$stmt->execute([$report_id, $org_id, hasRole('admin') ? null : $org_id]);
$report = $stmt->fetch();

if (!$report) {
    header("Location: index.php");
    exit();
}

// Create new PHPWord instance
$phpWord = new \PhpOffice\PhpWord\PhpWord();
$section = $phpWord->addSection();

// Add title
$section->addText(
    $report['org_name'] . ' - Terminal Report',
    ['bold' => true, 'size' => 16],
    ['alignment' => 'center']
);
$section->addText(
    $report['semester'] . ' - ' . $report['academic_year'],
    ['size' => 12],
    ['alignment' => 'center']
);
$section->addTextBreak(2);

// Add content sections
$headingStyle = ['bold' => true, 'size' => 12];
$textStyle = ['size' => 11];
$paragraphStyle = ['spacing' => 120];

// Activities Summary
$section->addText('Activities Summary', $headingStyle);
$section->addText($report['activities_summary'], $textStyle, $paragraphStyle);
$section->addTextBreak();

// Achievements
$section->addText('Key Achievements', $headingStyle);
$section->addText($report['achievements'], $textStyle, $paragraphStyle);
$section->addTextBreak();

// Challenges
$section->addText('Challenges Faced', $headingStyle);
$section->addText($report['challenges'], $textStyle, $paragraphStyle);
$section->addTextBreak();

// Recommendations
$section->addText('Recommendations', $headingStyle);
$section->addText($report['recommendations'], $textStyle, $paragraphStyle);
$section->addTextBreak();

// Financial Summary
$section->addText('Financial Summary', $headingStyle);
$section->addText($report['financial_summary'], $textStyle, $paragraphStyle);
$section->addTextBreak(2);

// Add footer with metadata
$section->addText(
    'Generated on: ' . date('F j, Y') . "\n" .
    'Status: ' . ucfirst(str_replace('_', ' ', $report['status'])) . "\n" .
    'Submitted by: ' . $report['created_by_name'],
    ['size' => 10, 'color' => '666666'],
    ['alignment' => 'right']
);

// Generate filename
$filename = sprintf(
    'Terminal_Report_%s_%s_%s.docx',
    str_replace(' ', '_', $report['org_name']),
    str_replace(' ', '_', $report['semester']),
    $report['academic_year']
);

// Save file
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save('php://output');