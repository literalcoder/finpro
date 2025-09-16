<?php defined('BASE_PATH') or exit('No direct script access allowed'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-text"></i> Proposal Details</h2>
    <div class="btn-group">
        <a href="index.php" class="btn btn-secondary">Back to List</a>
        <?php if (hasRole('president') && $proposal['status'] === 'draft'): ?>
            <a href="edit.php?id=<?php echo $proposal['id']; ?>" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><?php echo escape($proposal['title']); ?></h5>
            <span class="badge bg-<?php 
                echo match($proposal['status']) {
                    'draft' => 'secondary',
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                    default => 'secondary'
                }; 
            ?>">
                <?php echo ucfirst($proposal['status']); ?>
            </span>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Organization:</strong></td>
                        <td><?php echo escape($proposal['org_name']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Type:</strong></td>
                        <td><?php echo ucfirst($proposal['type']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Budget:</strong></td>
                        <td><?php echo formatAmount($proposal['budget_amount']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Created By:</strong></td>
                        <td><?php echo escape($proposal['created_by_name']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Created:</strong></td>
                        <td><?php echo date('M j, Y g:i A', strtotime($proposal['created_at'])); ?></td>
                    </tr>
                    <?php if ($proposal['updated_at'] !== $proposal['created_at']): ?>
                    <tr>
                        <td><strong>Last Updated:</strong></td>
                        <td><?php echo date('M j, Y g:i A', strtotime($proposal['updated_at'])); ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <div class="mb-4">
            <h6>Description</h6>
            <div class="bg-light p-3 rounded">
                <?php echo nl2br(escape($proposal['description'])); ?>
            </div>
        </div>

        <?php if ($proposal['status'] === 'draft' && hasRole('president')): ?>
        <form method="POST" class="d-inline">
            <input type="hidden" name="action" value="submit">
            <button type="submit" class="btn btn-primary">Submit for Review</button>
        </form>
        <?php endif; ?>

        <?php if ($proposal['status'] === 'pending' && hasAnyRole(['adviser', 'dean', 'ssc'])): ?>
        <div class="btn-group">
            <form method="POST" class="d-inline">
                <input type="hidden" name="action" value="approve">
                <button type="submit" class="btn btn-success me-2">Approve</button>
            </form>
            <form method="POST" class="d-inline">
                <input type="hidden" name="action" value="reject">
                <button type="submit" class="btn btn-danger">Reject</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>