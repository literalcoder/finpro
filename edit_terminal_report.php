<?php
include 'functions.php';
requireLogin();

$report_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$org_id = getOrganizationId();
$error = '';
$success = '';

// Verify access rights
if (!$report_id) {
    header("Location: terminal_reports.php");
    exit();
}

// Check role-based access
$is_president = hasRole('president');
$is_reviewer = hasRole('adviser') || hasRole('dean') || hasRole('ssc');

if (!$is_president && !$is_reviewer) {
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

if (!$report || ($report['org_id'] != $org_id && !hasRole('admin'))) {
    header("Location: terminal_reports.php");
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $new_status = $_POST['status'];
    $allowed_statuses = [];

    // Define allowed status transitions based on role
    if (hasRole('president')) {
        $allowed_statuses = ['draft', 'submitted'];
    } elseif (hasAnyRole(['adviser', 'dean', 'ssc'])) {
        $allowed_statuses = ['approved', 'needs_revision'];
    }

    if (in_array($new_status, $allowed_statuses)) {
        $update = $conn->prepare("UPDATE terminal_reports SET status = ?, updated_at = NOW() WHERE id = ?");
        if ($update->execute([$new_status, $report_id])) {
            header("Location: terminal_reports.php?success=1");
            exit();
        } else {
            $error = "Failed to update status.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Terminal Report | OrgFinPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="./style.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include 'nav.php'; ?>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-pencil-square"></i> Edit Terminal Report</h2>
            <a href="terminal_reports.php" class="btn btn-secondary">Back to Reports</a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Report Details</h5>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Semester:</strong></td>
                                <td><?php echo escape($report['semester']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Academic Year:</strong></td>
                                <td><?php echo escape($report['academic_year']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <?php
                                    $status_colors = [
                                        'draft' => 'secondary',
                                        'submitted' => 'warning',
                                        'approved' => 'success',
                                        'needs_revision' => 'danger'
                                    ];
                                    ?>
                                    <span class="badge bg-<?php echo $status_colors[$report['status']] ?? 'secondary'; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $report['status'])); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Created By:</strong></td>
                                <td><?php echo escape($report['created_by_name']); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <?php if ((hasRole('president') && in_array($report['status'], ['draft', 'needs_revision'])) ||
                            (hasAnyRole(['adviser', 'dean', 'ssc']) && $report['status'] === 'submitted')
                        ): ?>
                            <h5>Update Status</h5>
                            <form method="POST" class="mt-3">
                                <div class="mb-3">
                                    <select name="status" class="form-select" required>
                                        <option value="">Select new status</option>
                                        <?php if (hasRole('president')): ?>
                                            <option value="draft" <?php echo $report['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                            <option value="submitted" <?php echo $report['status'] === 'submitted' ? 'selected' : ''; ?>>Submit for Review</option>
                                        <?php endif; ?>
                                        <?php if (hasAnyRole(['adviser', 'dean', 'ssc'])): ?>
                                            <option value="approved" <?php echo $report['status'] === 'approved' ? 'selected' : ''; ?>>Approve</option>
                                            <option value="needs_revision" <?php echo $report['status'] === 'needs_revision' ? 'selected' : ''; ?>>Needs Revision</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Status</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-4">
                    <h5>Report Content</h5>
                    <div class="mb-3">
                        <label class="form-label">Activities Summary</label>
                        <div class="form-control-plaintext"><?php echo nl2br(escape($report['activities_summary'])); ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Achievements</label>
                        <div class="form-control-plaintext"><?php echo nl2br(escape($report['achievements'])); ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Challenges</label>
                        <div class="form-control-plaintext"><?php echo nl2br(escape($report['challenges'])); ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Recommendations</label>
                        <div class="form-control-plaintext"><?php echo nl2br(escape($report['recommendations'])); ?></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Financial Summary</label>
                        <div class="form-control-plaintext"><?php echo nl2br(escape($report['financial_summary'])); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>