<?php defined('BASE_PATH') or exit('No direct script access allowed'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-text"></i> Terminal Reports</h2>
    <div class="btn-group">
        <a href="submit.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Submit New Report
        </a>
    </div>
</div>

<div class="card shadow">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Semester</th>
                        <th>Academic Year</th>
                        <th>Status</th>
                        <th>Submitted By</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): ?>
                    <tr>
                        <td><?php echo escape($report['semester']); ?></td>
                        <td><?php echo escape($report['academic_year']); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo match($report['status']) {
                                    'draft' => 'secondary',
                                    'submitted' => 'warning',
                                    'approved' => 'success',
                                    'needs_revision' => 'danger'
                                }; 
                            ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $report['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo escape($report['created_by_name']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($report['created_at'])); ?></td>
                        <td>
                            <a href="view.php?id=<?php echo $report['id']; ?>" class="btn btn-sm btn-outline-primary">
                                View
                            </a>
                            <?php if ($report['status'] === 'draft' || $report['status'] === 'needs_revision'): ?>
                            <a href="edit.php?id=<?php echo $report['id']; ?>" class="btn btn-sm btn-outline-warning">
                                Edit
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($reports)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">No terminal reports found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>