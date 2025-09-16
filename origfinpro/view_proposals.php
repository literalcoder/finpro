<?php
session_start();
include 'db.php'; // contains $conn (MySQLi)

// Check if org_id exists in session
if (!isset($_SESSION['org_id'])) {
    die("Organization ID not found. Please log in again.");
}

$org_id = $_SESSION['org_id'];

// Prepare and execute query
$stmt = $conn->prepare("SELECT * FROM proposals WHERE org_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $org_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch all proposals
$proposals = [];
while ($row = $result->fetch_assoc()) {
    $proposals[] = $row;
}

$stmt->close();
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
                <span class="badge bg-<?= $p['status']=='Approved'?'success':($p['status']=='Rejected'?'danger':'warning') ?>">
                  <?= htmlspecialchars($p['status']) ?>
                </span>
              </td>
              <td><?= htmlspecialchars($p['current_approver']) ?></td>
              <td><?= htmlspecialchars($p['created_at']) ?></td>
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
</div>
</body>
</html>
