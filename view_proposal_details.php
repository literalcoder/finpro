<?php
include 'functions.php';
requireLogin();

$proposal_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$org_id = getOrganizationId();

if (!$proposal_id) {
    header("Location: view_proposals.php");
    exit();
}

// Get proposal details with organization and user info
if (hasRole('admin')) {
    $sql = "SELECT p.*, o.name as org_name, u.name as created_by_name 
            FROM proposals p 
            LEFT JOIN organizations o ON p.org_id = o.id 
            LEFT JOIN users u ON p.created_by = u.id 
            WHERE p.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$proposal_id]);
} else {
    $sql = "SELECT p.*, u.name as created_by_name 
            FROM proposals p 
            LEFT JOIN users u ON p.created_by = u.id 
            WHERE p.id = ? AND p.org_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$proposal_id, $org_id]);
}

$proposal = $stmt->fetch(PDO::FETCH_ASSOC);

// President status update logic
if (hasRole('president') && isset($_POST['update_status']) && isset($_POST['new_status']) && $proposal) {
    $allowed_statuses = ['draft', 'pending', 'approved', 'rejected'];
    $new_status = $_POST['new_status'];
    if (in_array($new_status, $allowed_statuses)) {
        $update = $conn->prepare("UPDATE proposals SET status = ?, updated_at = NOW() WHERE id = ?");
        $update->execute([$new_status, $proposal_id]);
        // Refresh proposal data
        header("Location: view_proposal_details.php?id=" . $proposal_id);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposal Details | OrgFinPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="./style.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include 'nav.php'; ?>
    <div class="container mt-4 pb-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-file-earmark-text"></i> Proposal Details</h2>
            <div>
                <a href="view_proposals.php" class="btn btn-secondary">Back to Proposals</a>
                <?php if (hasAnyRole(['president', 'treasurer']) && $proposal['status'] === 'draft'): ?>
                    <a href="edit_proposal.php?id=<?php echo $proposal['id']; ?>" class="btn btn-primary">Edit</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4><?php echo escape($proposal['title']); ?></h4>
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
                    <span class="badge bg-<?php echo $color; ?> fs-6"><?php echo ucfirst(str_replace('_', ' ', $proposal['status'])); ?></span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h5>Description</h5>
                        <p><?php echo nl2br(escape($proposal['description'])); ?></p>

                        <h5>Proposal Type</h5>
                        <span class="badge bg-info"><?php echo ucfirst(str_replace('_', ' ', $proposal['type'])); ?></span>
                    </div>
                    <div class="col-md-4">
                        <h5>Details</h5>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Created By:</strong></td>
                                <td><?php echo escape($proposal['created_by_name']); ?></td>
                            </tr>
                            <?php if (hasRole('admin')): ?>
                                <tr>
                                    <td><strong>Organization:</strong></td>
                                    <td><?php echo escape($proposal['org_name']); ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <td><strong>Budget:</strong></td>
                                <td>
                                    <?php if ($proposal['budget_amount'] > 0): ?>
                                        ₱<?php echo number_format($proposal['budget_amount'], 2); ?>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Created:</strong></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($proposal['created_at'])); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Updated:</strong></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($proposal['updated_at'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <?php if ((hasRole('adviser') || hasRole('dean') || hasRole('ssc')) && $proposal['status'] === 'pending'): ?>
                    <div class="mt-4">
                        <h5>Review Actions</h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-success" onclick="approveProposal(<?php echo $proposal['id']; ?>)">
                                <i class="bi bi-check-circle"></i> Approve
                            </button>
                            <button class="btn btn-danger" onclick="rejectProposal(<?php echo $proposal['id']; ?>)">
                                <i class="bi bi-x-circle"></i> Reject
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (hasRole('president') && $proposal['status'] !== 'approved' && $proposal['status'] !== 'rejected'): ?>
                    <div class="mt-4">
                        <h5>Update Status</h5>
                        <form method="POST">
                            <input type="hidden" name="update_status" value="1">
                            <div class="d-flex gap-2">
                                <select name="new_status" class="form-select">
                                    <option value="">Select new status</option>
                                    <option value="draft" <?php echo $proposal['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="pending" <?php echo $proposal['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="approved" <?php echo $proposal['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="rejected" <?php echo $proposal['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                                <button type="submit" class="btn btn-primary">Update Status</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function approveProposal(proposalId) {
            if (confirm('Are you sure you want to approve this proposal?')) {
                window.location.href = 'approve_proposal.php?id=' + proposalId;
            }
        }

        function rejectProposal(proposalId) {
            const reason = prompt('Please provide a reason for rejection:');
            if (reason) {
                window.location.href = 'reject_proposal.php?id=' + proposalId + '&reason=' + encodeURIComponent(reason);
            }
        }
    </script>
</body>

</html>