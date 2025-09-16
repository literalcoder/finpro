<?php
include 'functions.php';
requireLogin();
requireRole('president');

$org_id = getOrganizationId();
$success = '';
$error = '';

if (!$org_id) {
    $error = "You must be assigned to an organization to submit terminal reports.";
}

// Ensure terminal_reports table exists
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
$reports = [];
if ($org_id) {
    $sql = "SELECT tr.*, u.name as created_by_name 
            FROM terminal_reports tr 
            LEFT JOIN users u ON tr.created_by = u.id 
            WHERE tr.org_id = ? 
            ORDER BY tr.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$org_id]);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// No form processing needed here as it's moved to submit_terminal_report.php
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal Reports | OrgFinPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="./style.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include 'nav.php'; ?>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-clipboard-data"></i> Terminal Reports</h2>
            <div>
                <?php if ($org_id): ?>
                    <a href="submit_terminal_report.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Submit New Report
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Terminal report submitted successfully!</div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Previous Terminal Reports -->
        <div class="card shadow">
            <div class="card-header">
                <h5><i class="bi bi-clock-history"></i> Previous Reports</h5>
            </div>
            <div class="card-body">
                <?php if (empty($reports)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-clipboard-data text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">No terminal reports found</h5>
                        <p class="text-muted">Submit your first terminal report using the form above.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Semester</th>
                                    <th>Academic Year</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports as $report): ?>
                                    <tr>
                                        <td><?php echo escape($report['semester']); ?></td>
                                        <td><?php echo escape($report['academic_year']); ?></td>
                                        <td>
                                            <?php
                                            $status_colors = [
                                                'draft' => 'secondary',
                                                'submitted' => 'warning',
                                                'approved' => 'success',
                                                'needs_revision' => 'danger'
                                            ];
                                            $color = $status_colors[$report['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst(str_replace('_', ' ', $report['status'])); ?></span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($report['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="view_terminal_report.php?id=<?php echo $report['id']; ?>" class="btn btn-sm btn-outline-info">View</a>
                                                <?php if ((hasRole('adviser') || hasRole('dean') || hasRole('ssc')) && $report['status'] === 'submitted'): ?>
                                                    <a href="edit_terminal_report.php?id=<?php echo $report['id']; ?>" class="btn btn-sm btn-outline-primary">Review</a>
                                                <?php elseif (hasRole('president') && in_array($report['status'], ['draft', 'needs_revision'])): ?>
                                                    <a href="edit_terminal_report.php?id=<?php echo $report['id']; ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>