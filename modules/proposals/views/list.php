<?php defined('BASE_PATH') or exit('No direct script access allowed'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-files"></i> Proposals</h2>
    <?php if (hasRole('president')): ?>
    <a href="create.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> New Proposal
    </a>
    <?php endif; ?>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-hourglass text-warning" style="font-size: 2rem;"></i>
                <h3 class="mt-2"><?php echo $stats['pending'] ?? 0; ?></h3>
                <p class="text-muted mb-0">Pending</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                <h3 class="mt-2"><?php echo $stats['approved'] ?? 0; ?></h3>
                <p class="text-muted mb-0">Approved</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-x-circle text-danger" style="font-size: 2rem;"></i>
                <h3 class="mt-2"><?php echo $stats['rejected'] ?? 0; ?></h3>
                <p class="text-muted mb-0">Rejected</p>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Recent Proposals</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Budget</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proposals as $proposal): ?>
                    <tr>
                        <td><?php echo escape($proposal['title']); ?></td>
                        <td><?php echo ucfirst($proposal['type']); ?></td>
                        <td><?php echo formatAmount($proposal['budget_amount']); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo match($proposal['status']) {
                                    'pending' => 'warning',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    default => 'secondary'
                                };
                            ?>">
                                <?php echo ucfirst($proposal['status']); ?>
                            </span>
                        </td>
                        <td><?php echo escape($proposal['created_by_name']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($proposal['created_at'])); ?></td>
                        <td>
                            <a href="view.php?id=<?php echo $proposal['id']; ?>" class="btn btn-sm btn-outline-primary">
                                View
                            </a>
                            <?php if (hasRole('president') && $proposal['status'] === 'draft'): ?>
                            <a href="edit.php?id=<?php echo $proposal['id']; ?>" class="btn btn-sm btn-outline-warning">
                                Edit
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($proposals)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">No proposals found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>