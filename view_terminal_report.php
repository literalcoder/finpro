<?php
include 'functions.php';
requireLogin();

$report_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$org_id = getOrganizationId();
$error = '';

if (!$report_id) {
    header("Location: terminal_reports.php");
    exit();
}

// Get report details
$sql = "SELECT tr.*, u.name as created_by_name 
        FROM terminal_reports tr 
        LEFT JOIN users u ON tr.created_by = u.id 
        WHERE tr.id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$report_id]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

// Verify access
if (!$report || ($report['org_id'] != $org_id && !hasRole('admin'))) {
    header("Location: terminal_reports.php");
    exit();
}

$status_colors = [
    'draft' => 'secondary',
    'submitted' => 'warning',
    'approved' => 'success',
    'needs_revision' => 'danger'
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Terminal Report | OrgFinPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="./style.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include 'nav.php'; ?>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-file-text"></i> Terminal Report Details</h2>
            <div>
                <a href="terminal_reports.php" class="btn btn-secondary me-2">Back to Reports</a>
                <?php if ((hasRole('president') && in_array($report['status'], ['draft', 'needs_revision'])) ||
                    (hasAnyRole(['adviser', 'dean', 'ssc']) && $report['status'] === 'submitted')
                ): ?>
                    <a href="edit_terminal_report.php?id=<?php echo $report['id']; ?>" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Edit Report
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4><?php echo escape($report['semester']); ?> - <?php echo escape($report['academic_year']); ?></h4>
                    <span class="badge bg-<?php echo $status_colors[$report['status']] ?? 'secondary'; ?> fs-6">
                        <?php echo ucfirst(str_replace('_', ' ', $report['status'])); ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Submitted By:</strong></td>
                                <td><?php echo escape($report['created_by_name']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Submitted On:</strong></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($report['created_at'])); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Last Updated:</strong></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($report['updated_at'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="mt-4">
                    <h5>Activities Summary</h5>
                    <div class="bg-light p-3 rounded mb-4">
                        <?php echo nl2br(escape($report['activities_summary'])); ?>
                    </div>

                    <h5>Key Achievements</h5>
                    <div class="bg-light p-3 rounded mb-4">
                        <?php echo nl2br(escape($report['achievements'])); ?>
                    </div>

                    <h5>Challenges Faced</h5>
                    <div class="bg-light p-3 rounded mb-4">
                        <?php echo nl2br(escape($report['challenges'])); ?>
                    </div>

                    <h5>Recommendations</h5>
                    <div class="bg-light p-3 rounded mb-4">
                        <?php echo nl2br(escape($report['recommendations'])); ?>
                    </div>

                    <h5>Financial Summary</h5>
                    <div class="bg-light p-3 rounded">
                        <?php echo nl2br(escape($report['financial_summary'])); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>