<?php
include 'functions.php';
requireLogin();

// Check if org_id exists in session
$org_id = $_SESSION['org_id'] ?? 1; // default to 1 for now

// Prepare and execute query using PDO
global $conn;
$stmt = $conn->prepare("SELECT * FROM proposals WHERE org_id = ? ORDER BY created_at DESC");
$stmt->execute([$org_id]);

// Fetch all proposals
$proposals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Proposals | OrgFinPro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h3 class="mb-4">📑 My Proposals</h3>
  <div class="table-responsive shadow-sm rounded">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <th>Title</th>
          <th>Type</th>
          <th>Status</th>
          <th>Current Approver</th>
          <th>Submitted At</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($proposals) > 0): ?>
          <?php foreach ($proposals as $p): ?>
            <tr>
              <td><?= htmlspecialchars($p['title']) ?></td>
              <td><?= htmlspecialchars($p['type']) ?></td>
              <td>
                <span class="badge bg-<?= ($p['status'] ?? 'Pending') == 'Approved' ? 'success' : (($p['status'] ?? 'Pending') == 'Rejected' ? 'danger' : 'warning') ?>">
                  <?= htmlspecialchars($p['status'] ?? 'Pending') ?>
                </span>
              </td>
              <td><?= htmlspecialchars($p['current_approver'] ?? 'N/A') ?></td>
              <td><?= htmlspecialchars($p['created_at'] ?? date('Y-m-d H:i:s')) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" class="text-center text-muted">No proposals found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="mt-3">
    <a href="create_proposal.php" class="btn btn-primary">Create New Proposal</a>
    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
  </div>
</div>
</body>
</html>