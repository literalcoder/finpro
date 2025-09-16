<?php
include 'functions.php';
requireLogin();
requireRole('president');

$org_id = getOrganizationId();
$success = '';
$error = '';

if (!$org_id) {
    header("Location: terminal_reports.php");
    exit();
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
        header("Location: terminal_reports.php?success=1");
        exit();
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
    <title>Submit Terminal Report | OrgFinPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="./style.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include 'nav.php'; ?>
    <div class="container mt-4 pb-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-clipboard-data"></i> Submit Terminal Report</h2>
            <a href="terminal_reports.php" class="btn btn-secondary">Back to Reports</a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card shadow">
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

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Submit Report</button>
                        <a href="terminal_reports.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>