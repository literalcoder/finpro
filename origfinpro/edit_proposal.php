
<?php
include 'functions.php';
requireLogin();
requireAnyRole(['president', 'treasurer']);

$proposal_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$org_id = getOrganizationId();
$success = '';
$error = '';

if (!$proposal_id || !$org_id) {
    header("Location: view_proposals.php");
    exit();
}

// Get proposal for editing
$sql = "SELECT * FROM proposals WHERE id = ? AND org_id = ? AND status = 'draft'";
$stmt = $conn->prepare($sql);
$stmt->execute([$proposal_id, $org_id]);
$proposal = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$proposal) {
    header("Location: view_proposals.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $type = $_POST['type'];
    $description = trim($_POST['description']);
    $budget_amount = floatval($_POST['budget_amount'] ?? 0);
    
    $sql = "UPDATE proposals SET title = ?, description = ?, type = ?, budget_amount = ?, updated_at = NOW() WHERE id = ? AND org_id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([$title, $description, $type, $budget_amount, $proposal_id, $org_id])) {
        $success = "Proposal updated successfully!";
        // Refresh proposal data
        $sql = "SELECT * FROM proposals WHERE id = ? AND org_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$proposal_id, $org_id]);
        $proposal = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = "Failed to update proposal.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Proposal | OrgFinPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3><i class="bi bi-pencil-square"></i> Edit Proposal</h3>
            <div>
                <a href="view_proposal_details.php?id=<?php echo $proposal['id']; ?>" class="btn btn-secondary">Back to Details</a>
                <a href="view_proposals.php" class="btn btn-outline-secondary">All Proposals</a>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Proposal Title</label>
                        <input type="text" name="title" class="form-control" value="<?php echo escape($proposal['title']); ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Proposal Type</label>
                        <select name="type" class="form-control" required>
                            <option value="">-- Select Type --</option>
                            <option value="event" <?php echo $proposal['type'] === 'event' ? 'selected' : ''; ?>>Event</option>
                            <option value="project" <?php echo $proposal['type'] === 'project' ? 'selected' : ''; ?>>Project</option>
                            <option value="fundraising" <?php echo $proposal['type'] === 'fundraising' ? 'selected' : ''; ?>>Fundraising</option>
                            <option value="resolution" <?php echo $proposal['type'] === 'resolution' ? 'selected' : ''; ?>>Resolution</option>
                            <option value="budget_request" <?php echo $proposal['type'] === 'budget_request' ? 'selected' : ''; ?>>Budget Request</option>
                            <option value="other" <?php echo $proposal['type'] === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="8" required><?php echo escape($proposal['description']); ?></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Budget Amount (₱)</label>
                        <input type="number" name="budget_amount" class="form-control" step="0.01" min="0" value="<?php echo $proposal['budget_amount']; ?>">
                        <small class="form-text text-muted">Leave blank or 0 if no budget required</small>
                    </div>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update Proposal</button>
                <a href="view_proposal_details.php?id=<?php echo $proposal['id']; ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
