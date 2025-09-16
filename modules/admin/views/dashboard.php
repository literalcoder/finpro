<?php defined('BASE_PATH') or exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-md-3">
        <div class="card mb-4 shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-building text-primary" style="font-size: 2rem;"></i>
                <h3 class="mt-2"><?php echo $stats['total_orgs']; ?></h3>
                <p class="text-muted mb-0">Active Organizations</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card mb-4 shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-people text-success" style="font-size: 2rem;"></i>
                <h3 class="mt-2"><?php echo $stats['total_users']; ?></h3>
                <p class="text-muted mb-0">Registered Users</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card mb-4 shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-file-text text-warning" style="font-size: 2rem;"></i>
                <h3 class="mt-2"><?php echo $stats['pending_proposals']; ?></h3>
                <p class="text-muted mb-0">Pending Proposals</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card mb-4 shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-journal-text text-info" style="font-size: 2rem;"></i>
                <h3 class="mt-2"><?php echo $stats['recent_reports']; ?></h3>
                <p class="text-muted mb-0">Recent Reports</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Activities</h5>
                <div class="btn-group">
                    <a href="manage_users.php" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-person-plus"></i> Add User
                    </a>
                    <a href="manage_organizations.php" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-building-add"></i> Add Organization
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>User</th>
                                <th>Organization</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activities as $activity): ?>
                            <tr>
                                <td><?php echo date('M j, g:i A', strtotime($activity['created_at'])); ?></td>
                                <td><?php echo escape($activity['user_name']); ?></td>
                                <td><?php echo escape($activity['org_name']); ?></td>
                                <td><?php echo escape($activity['action']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="manage_users.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-person-gear"></i> User Management
                    </a>
                    <a href="manage_organizations.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-building"></i> Organization Management
                    </a>
                    <a href="../proposals/manage.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-file-text"></i> View All Proposals
                    </a>
                    <a href="../reports/index.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-graph-up"></i> Generate Reports
                    </a>
                    <a href="settings.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-gear"></i> System Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>