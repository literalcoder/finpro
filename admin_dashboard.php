<?php
include 'functions.php';
requireLogin();
requireRole('admin');

// Get statistics
$sql_stats = "SELECT 
    (SELECT COUNT(*) FROM organizations WHERE status = 'active') as total_orgs,
    (SELECT COUNT(*) FROM users WHERE status = 'active') as total_users,
    (SELECT COUNT(*) FROM proposals WHERE status = 'pending') as pending_proposals,
    (SELECT COUNT(*) FROM terminal_reports WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)) as recent_reports";
$stats = $conn->query($sql_stats)->fetch(PDO::FETCH_ASSOC);

// Get latest activities
$sql_activities = "SELECT a.*, u.name as user_name, o.name as org_name 
                  FROM audit_logs a 
                  LEFT JOIN users u ON a.user_id = u.id 
                  LEFT JOIN organizations o ON a.org_id = o.id 
                  ORDER BY a.created_at DESC LIMIT 10";
$activities = $conn->query($sql_activities)->fetchAll(PDO::FETCH_ASSOC);

// Get pending liquidations
$sql_pending = "SELECT p.*, o.name as org_name 
                FROM proposals p 
                JOIN organizations o ON p.org_id = o.id 
                WHERE p.status = 'approved' 
                AND NOT EXISTS (
                    SELECT 1 FROM financial_transactions 
                    WHERE proposal_id = p.id AND type = 'liquidation'
                )";
$pending_liquidations = $conn->query($sql_pending)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | OrgFinPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="./style.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include 'nav.php'; ?>

    <div class="container-fluid mt-4">
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
                        <p class="text-muted mb-0">Financial Reports</p>
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
                        <?php if (empty($activities)): ?>
                            <p class="text-center text-muted mb-0">No recent activities</p>
                        <?php else: ?>
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
                        <?php endif; ?>
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
                            <a href="manage_proposals.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-file-text"></i> View All Proposals
                            </a>
                            <a href="reports.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-graph-up"></i> Generate Reports
                            </a>
                            <a href="system_settings.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-gear"></i> System Settings
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card mt-4 shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">Missing Liquidations</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pending_liquidations)): ?>
                            <p class="text-muted mb-0">No pending liquidations</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($pending_liquidations as $item): ?>
                                    <a href="view_proposal_details.php?id=<?php echo $item['id']; ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo escape($item['org_name']); ?></h6>
                                            <small><?php echo date('M j', strtotime($item['created_at'])); ?></small>
                                        </div>
                                        <p class="mb-1"><?php echo escape($item['title']); ?></p>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>