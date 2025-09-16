
<?php
include 'functions.php';
requireLogin();

$org_id = getOrganizationId();

// Get proposals based on user role
if (hasRole('admin')) {
    // Admin can see all proposals
    $sql = "SELECT p.*, o.name as org_name, u.name as created_by_name FROM proposals p 
            LEFT JOIN organizations o ON p.org_id = o.id 
            LEFT JOIN users u ON p.created_by = u.id 
            ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
} else {
    // Organization members can only see their org's proposals
    $sql = "SELECT p.*, u.name as created_by_name FROM proposals p 
            LEFT JOIN users u ON p.created_by = u.id 
            WHERE p.org_id = ? 
            ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$org_id]);
}

$proposals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Proposals | OrgFinPro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-earmark-text"></i> Proposals</h2>
    <div>
      <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
      <?php if (hasAnyRole(['president', 'treasurer'])): ?>
        <a href="create_proposal.php" class="btn btn-primary">Create New Proposal</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Proposals List -->
  <div class="card">
    <div class="card-body">
      <?php if (empty($proposals)): ?>
        <div class="text-center py-5">
          <i class="bi bi-file-earmark-text text-muted" style="font-size: 3rem;"></i>
          <h5 class="text-muted mt-3">No proposals found</h5>
          <p class="text-muted">
            <?php if (hasAnyRole(['president', 'treasurer'])): ?>
              <a href="create_proposal.php" class="btn btn-primary">Create your first proposal</a>
            <?php else: ?>
              No proposals have been submitted yet.
            <?php endif; ?>
          </p>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Title</th>
                <th>Type</th>
                <?php if (hasRole('admin')): ?>
                  <th>Organization</th>
                <?php endif; ?>
                <th>Status</th>
                <th>Budget</th>
                <th>Created By</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($proposals as $proposal): ?>
              <tr>
                <td>
                  <strong><?php echo escape($proposal['title']); ?></strong>
                  <br>
                  <small class="text-muted"><?php echo escape(substr($proposal['description'], 0, 50)) . (strlen($proposal['description']) > 50 ? '...' : ''); ?></small>
                </td>
                <td><span class="badge bg-info"><?php echo ucfirst(str_replace('_', ' ', $proposal['type'])); ?></span></td>
                <?php if (hasRole('admin')): ?>
                  <td><?php echo escape($proposal['org_name']); ?></td>
                <?php endif; ?>
                <td>
                  <?php
                  $status_colors = [
                    'draft' => 'secondary',
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                    'needs_revision' => 'info'
                  ];
                  $color = $status_colors[$proposal['status']] ?? 'secondary';
                  ?>
                  <span class="badge bg-<?php echo $color; ?>"><?php echo ucfirst(str_replace('_', ' ', $proposal['status'])); ?></span>
                </td>
                <td>
                  <?php if ($proposal['budget_amount'] > 0): ?>
                    ₱<?php echo number_format($proposal['budget_amount'], 2); ?>
                  <?php else: ?>
                    <span class="text-muted">N/A</span>
                  <?php endif; ?>
                </td>
                <td><?php echo escape($proposal['created_by_name']); ?></td>
                <td><?php echo date('M j, Y', strtotime($proposal['created_at'])); ?></td>
                <td>
                  <button class="btn btn-sm btn-outline-info" onclick="viewProposal(<?php echo $proposal['id']; ?>)">View</button>
                  <?php if (hasAnyRole(['president', 'treasurer']) && $proposal['status'] === 'draft'): ?>
                    <button class="btn btn-sm btn-outline-primary" onclick="editProposal(<?php echo $proposal['id']; ?>)">Edit</button>
                  <?php endif; ?>
                  <?php if (hasAnyRole(['adviser', 'dean', 'ssc']) && $proposal['status'] === 'pending'): ?>
                    <button class="btn btn-sm btn-outline-success" onclick="approveProposal(<?php echo $proposal['id']; ?>)">Approve</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="rejectProposal(<?php echo $proposal['id']; ?>)">Reject</button>
                  <?php endif; ?>
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
<script>
function viewProposal(proposalId) {
  window.location.href = 'view_proposal_details.php?id=' + proposalId;
}

function editProposal(proposalId) {
  window.location.href = 'edit_proposal.php?id=' + proposalId;
}

function approveProposal(proposalId) {
  if (confirm('Are you sure you want to approve this proposal?')) {
    // Implementation for approval
    window.location.href = 'approve_proposal.php?id=' + proposalId;
  }
}

function rejectProposal(proposalId) {
  const reason = prompt('Please provide a reason for rejection:');
  if (reason) {
    // Implementation for rejection
    window.location.href = 'reject_proposal.php?id=' + proposalId + '&reason=' + encodeURIComponent(reason);
  }
}
</script>
</body>
</html>
