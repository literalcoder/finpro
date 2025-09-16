<?php defined('BASE_PATH') or exit('No direct script access allowed'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-text"></i> Terminal Report</h2>
    <div class="btn-group">
        <a href="index.php" class="btn btn-secondary">Back to List</a>
        <a href="export.php?id=<?php echo $report['id']; ?>" class="btn btn-success">
            <i class="bi bi-download"></i> Export Report
        </a>
        <?php if (hasRole('president') && ($report['status'] === 'draft' || $report['status'] === 'needs_revision')): ?>
        <a href="edit.php?id=<?php echo $report['id']; ?>" class="btn btn-warning">
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
            <div>
                <h5 class="mb-0"><?php echo escape($report['semester']); ?> - <?php echo escape($report['academic_year']); ?></h5>
                <small class="text-muted"><?php echo escape($report['org_name']); ?></small>
            </div>
            <span class="badge bg-<?php 
                echo match($report['status']) {
                    'draft' => 'secondary',
                    'submitted' => 'warning',
                    'approved' => 'success',
                    'needs_revision' => 'danger'
                }; 
            ?> fs-6">
                <?php echo ucfirst(str_replace('_', ' ', $report['status'])); ?>
            </span>
        </div>
    </div>
    <div class="card-body">
        <div class="mb-4">
            <h5>Activities Summary</h5>
            <div class="bg-light p-3 rounded">
                <?php echo nl2br(escape($report['activities_summary'])); ?>
            </div>
        </div>

        <div class="mb-4">
            <h5>Key Achievements</h5>
            <div class="bg-light p-3 rounded">
                <?php echo nl2br(escape($report['achievements'])); ?>
            </div>
        </div>

        <div class="mb-4">
            <h5>Challenges Faced</h5>
            <div class="bg-light p-3 rounded">
                <?php echo nl2br(escape($report['challenges'])); ?>
            </div>
        </div>

        <div class="mb-4">
            <h5>Recommendations</h5>
            <div class="bg-light p-3 rounded">
                <?php echo nl2br(escape($report['recommendations'])); ?>
            </div>
        </div>

        <div class="mb-4">
            <h5>Financial Summary</h5>
            <div class="bg-light p-3 rounded">
                <?php echo nl2br(escape($report['financial_summary'])); ?>
            </div>
        </div>

        <div class="border-top pt-3">
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted">
                        Submitted by: <?php echo escape($report['created_by_name']); ?><br>
                        Created: <?php echo date('M j, Y g:i A', strtotime($report['created_at'])); ?><br>
                        <?php if ($report['updated_at'] !== $report['created_at']): ?>
                        Last Updated: <?php echo date('M j, Y g:i A', strtotime($report['updated_at'])); ?>
                        <?php endif; ?>
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <?php if ($report['status'] === 'draft' && hasRole('president')): ?>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="submit">
                        <button type="submit" class="btn btn-primary">Submit for Review</button>
                    </form>
                    <?php endif; ?>

                    <?php if ($report['status'] === 'submitted' && hasAnyRole(['adviser', 'dean', 'ssc'])): ?>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-success me-2">Approve</button>
                    </form>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="revise">
                        <button type="submit" class="btn btn-danger">Request Revision</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>