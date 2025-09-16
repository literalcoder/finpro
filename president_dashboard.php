<?php
include 'functions.php';
requireLogin();
requireRole('president');

// Define getCurrentSemester if not already defined
if (!function_exists('getCurrentSemester')) {
    function getCurrentSemester() {
        // Example logic: adjust as needed for your application
        $month = date('n');
        $year = date('Y');
        if ($month >= 6 && $month <= 10) {
            $semester = '1st';
        } elseif ($month >= 11 || $month <= 3) {
            $semester = '2nd';
            if ($month <= 3) {
                $year = date('Y', strtotime('-1 year'));
            }
        } else {
            $semester = 'Summer';
        }
        return ['semester' => $semester, 'year' => $year];
    }
}

$org_id = getOrganizationId();

// Get organization details
$sql = "SELECT * FROM organizations WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$org_id]);
$org = $stmt->fetch(PDO::FETCH_ASSOC);

// Get proposals statistics
$sql_proposals = "SELECT 
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved,
    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected
    FROM proposals 
    WHERE org_id = ?";
$stmt = $conn->prepare($sql_proposals);
$stmt->execute([$org_id]);
$proposal_stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get financial summary
$sql_finance = "SELECT 
    COALESCE(SUM(CASE WHEN type IN ('income', 'collection') THEN amount WHEN type IN ('expense', 'disbursement') THEN -amount END), 0) as balance,
    COUNT(CASE WHEN type IN ('income', 'collection') THEN 1 END) as income_count,
    COUNT(CASE WHEN type IN ('expense', 'disbursement') THEN 1 END) as expense_count
    FROM financial_transactions 
    WHERE org_id = ?";
$stmt = $conn->prepare($sql_finance);
$stmt->execute([$org_id]);
$finance = $stmt->fetch(PDO::FETCH_ASSOC);

// Get recent proposals
$sql_recent = "SELECT * FROM proposals WHERE org_id = ? ORDER BY updated_at DESC LIMIT 5";
$stmt = $conn->prepare($sql_recent);
$stmt->execute([$org_id]);
$recent_proposals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>President Dashboard | OrgFinPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="./style.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'nav.php'; ?>
<div class="container mt-4 pb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-speedometer2"></i> <?php echo escape($org['name']); ?> Dashboard</h2>
        <div>
            <a href="create_proposal.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> New Proposal
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-hourglass-split text-warning" style="font-size: 2rem;"></i>
                    <h3 class="mt-2"><?php echo $proposal_stats['pending']; ?></h3>
                    <p class="text-muted mb-0">Pending Proposals</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-4 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                    <h3 class="mt-2"><?php echo $proposal_stats['approved']; ?></h3>
                    <p class="text-muted mb-0">Approved Proposals</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-4 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-x-circle text-danger" style="font-size: 2rem;"></i>
                    <h3 class="mt-2"><?php echo $proposal_stats['rejected']; ?></h3>
                    <p class="text-muted mb-0">Rejected Proposals</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-4 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-wallet2 <?php echo $finance['balance'] >= 0 ? 'text-success' : 'text-danger'; ?>" style="font-size: 2rem;"></i>
                    <h3 class="mt-2">₱<?php echo number_format($finance['balance'], 2); ?></h3>
                    <p class="text-muted mb-0">Current Balance</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
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
                                    <th>Status</th>
                                    <th>Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_proposals as $proposal): ?>
                                <tr>
                                    <td><?php echo escape($proposal['title']); ?></td>
                                    <td><?php echo ucfirst($proposal['type']); ?></td>
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
                                    <td><?php echo date('M j, Y', strtotime($proposal['updated_at'])); ?></td>
                                    <td>
                                        <a href="view_proposal_details.php?id=<?php echo $proposal['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
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
                        <a href="create_proposal.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-plus-circle"></i> Create New Proposal
                        </a>
                        <a href="terminal_reports.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-file-text"></i> Submit Terminal Report
                        </a>
                        <a href="financial_management.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-cash-stack"></i> Financial Management
                        </a>
                        <a href="org_settings.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-gear"></i> Organization Settings
                        </a>
                    </div>
                </div>
            </div>

            <?php
            // Check for terminal report submission
            $current_semester = getCurrentSemester();
            $sql = "SELECT COUNT(*) FROM terminal_reports 
                   WHERE org_id = ? AND semester = ? AND academic_year = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$org_id, $current_semester['semester'], $current_semester['year']]);
            $has_terminal_report = $stmt->fetchColumn() > 0;
            
            if (!$has_terminal_report):
            ?>
            <div class="card mt-4 shadow-sm border-warning">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-exclamation-triangle text-warning"></i> Reminder</h6>
                    <p class="card-text">Terminal report for this semester hasn't been submitted yet.</p>
                    <a href="terminal_reports.php" class="btn btn-warning btn-sm">Submit Now</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>