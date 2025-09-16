
<?php
include 'functions.php';
requireLogin();
requireAnyRole(['president', 'treasurer']);

$success = '';
$error = '';
$org_id = getOrganizationId();

if (!$org_id) {
    $error = "You must be assigned to an organization to create proposals.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $org_id) {
    $title = trim($_POST['title']);
    $type = $_POST['type'];
    $description = trim($_POST['description']);
    $budget_amount = floatval($_POST['budget_amount'] ?? 0);
    
    $sql = "INSERT INTO proposals (title, description, type, org_id, created_by, budget_amount, status) VALUES (?, ?, ?, ?, ?, ?, 'draft')";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([$title, $description, $type, $org_id, $_SESSION['user_id'], $budget_amount])) {
        $success = "Proposal created successfully!";
    } else {
        $error = "Failed to create proposal.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Proposal | OrgFinPro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="card shadow p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3><i class="bi bi-plus-circle"></i> Create Proposal</h3>
      <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    
    <?php if ($success): ?>
      <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($org_id): ?>
    <form method="POST" enctype="multipart/form-data">
      <div class="row">
        <div class="col-md-8">
          <div class="mb-3">
            <label class="form-label">Proposal Title</label>
            <input type="text" name="title" class="form-control" placeholder="Enter proposal title" required>
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label">Proposal Type</label>
            <select name="type" class="form-control" required>
              <option value="">-- Select Type --</option>
              <option value="event">Event</option>
              <option value="project">Project</option>
              <option value="resolution">Resolution</option>
              <option value="membership_fee">Membership Fee</option>
              <option value="uniform">Uniform/Shirt</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>
      </div>
      
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="4" placeholder="Detailed description of the proposal" required></textarea>
      </div>
      
      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">Budget Amount (Optional)</label>
            <div class="input-group">
              <span class="input-group-text">₱</span>
              <input type="number" name="budget_amount" class="form-control" step="0.01" min="0" placeholder="0.00">
            </div>
          </div>
        </div>
      </div>
      
      <div class="mb-3">
        <label class="form-label">Attachments</label>
        <input type="file" name="attachments[]" class="form-control" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
        <small class="text-muted">Attach all required documents (e.g., Resolution, Waiver, Canvass, Quotations).</small>
      </div>
      
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Create Proposal</button>
        <a href="view_proposals.php" class="btn btn-outline-secondary">View All Proposals</a>
      </div>
    </form>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
