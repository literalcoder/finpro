
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
    $sql = "SELECT * FROM terminal_reports WHERE org_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$org_id]);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $semester = trim($_POST['semester']);
    $academic_year = trim($_POST['academic_year']);
    $activities_summary = trim($_POST['activities_summary']);
    $achievements = trim($_POST['achievements']);
    $challenges = trim($_POST['challenges']);
    $recommendations = trim($_POST['recommendations']);
    $financial_summary = trim($_POST['financial_summary']);
    
    
    
    $sql = "INSERT INTO terminal_reports (org_id, semester, academic_year, activities_summary, achievements, challenges, recommendations, financial_summary, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([$org_id, $semester, $academic_year, $activities_summary, $achievements, $challenges, $recommendations, $financial_summary, $_SESSION['user_id']])) {
        $success = "Terminal report submitted successfully!";
        // Refresh reports data
        $sql = "SELECT * FROM terminal_reports WHERE org_id = ? ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$org_id]);
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $error = "Failed to submit terminal report.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal Reports | OrgFinPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-clipboard-data"></i> Terminal Reports</h2>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($org_id): ?>
    <!-- Submit New Terminal Report -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5><i class="bi bi-plus-circle"></i> Submit Terminal Report</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Semester</label>
                            <select name="semester" class="form-control" required>
                                <option value="">-- Select Semester --</option>
                                <option value="1st Semester">1st Semester</option>
                                <option value="2nd Semester">2nd Semester</option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Academic Year</label>
                            <input type="text" name="academic_year" class="form-control" placeholder="e.g., 2023-2024" required>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Activities Summary</label>
                    <textarea name="activities_summary" class="form-control" rows="4" placeholder="Summarize the major activities and events conducted during the semester..." required></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Key Achievements</label>
                    <textarea name="achievements" class="form-control" rows="3" placeholder="List the major achievements and accomplishments..."></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Challenges Faced</label>
                    <textarea name="challenges" class="form-control" rows="3" placeholder="Describe any challenges or difficulties encountered..."></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Recommendations</label>
                    <textarea name="recommendations" class="form-control" rows="3" placeholder="Provide recommendations for future activities or improvements..."></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Financial Summary</label>
                    <textarea name="financial_summary" class="form-control" rows="3" placeholder="Provide a summary of financial activities, income, and expenses..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Submit Terminal Report</button>
            </form>
        </div>
    </div>

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
                                    <button class="btn btn-sm btn-outline-info" onclick="viewReport(<?php echo $report['id']; ?>)">View</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function viewReport(reportId) {
    // Implementation for viewing report details
    alert('View report functionality - Report ID: ' + reportId);
}
</script>
</body>
</html>
