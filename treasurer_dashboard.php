<?php
include 'functions.php';
requireLogin();
requireRole('treasurer');

$org_id = getOrganizationId();

// Get financial statistics
$sql_finance = "SELECT 
    COALESCE(SUM(CASE WHEN type IN ('income', 'collection') THEN amount WHEN type IN ('expense', 'disbursement') THEN -amount END), 0) as balance,
    COALESCE(SUM(CASE WHEN type IN ('income', 'collection') THEN amount END), 0) as total_income,
    COALESCE(SUM(CASE WHEN type IN ('expense', 'disbursement') THEN amount END), 0) as total_expenses,
    COUNT(*) as total_transactions
    FROM financial_transactions 
    WHERE org_id = ?";
$stmt = $conn->prepare($sql_finance);
$stmt->execute([$org_id]);
$finance = $stmt->fetch(PDO::FETCH_ASSOC);

// Get recent transactions
$sql_transactions = "SELECT ft.*, u.name as created_by_name 
                    FROM financial_transactions ft 
                    LEFT JOIN users u ON ft.created_by = u.id 
                    WHERE ft.org_id = ? 
                    ORDER BY ft.created_at DESC LIMIT 5";
$stmt = $conn->prepare($sql_transactions);
$stmt->execute([$org_id]);
$recent_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get pending liquidations
$sql_liquidations = "SELECT p.* 
                    FROM proposals p 
                    WHERE p.org_id = ? 
                    AND p.status = 'approved' 
                    AND NOT EXISTS (
                        SELECT 1 FROM financial_transactions ft 
                        WHERE ft.proposal_id = p.id 
                        AND ft.type = 'expense'
                    )";
$stmt = $conn->prepare($sql_liquidations);
$stmt->execute([$org_id]);
$pending_liquidations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treasurer Dashboard | OrgFinPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="./style.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'nav.php'; ?>
<div class="container mt-4 pb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-cash-stack"></i> Financial Dashboard</h2>
        <div>
            <a href="financial_management.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Record Transaction
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-wallet2 <?php echo $finance['balance'] >= 0 ? 'text-success' : 'text-danger'; ?>" style="font-size: 2rem;"></i>
                    <h3 class="mt-2">₱<?php echo number_format($finance['balance'], 2); ?></h3>
                    <p class="text-muted mb-0">Current Balance</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-4 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up-arrow text-success" style="font-size: 2rem;"></i>
                    <h3 class="mt-2">₱<?php echo number_format($finance['total_income'], 2); ?></h3>
                    <p class="text-muted mb-0">Total Income</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-4 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-graph-down-arrow text-danger" style="font-size: 2rem;"></i>
                    <h3 class="mt-2">₱<?php echo number_format($finance['total_expenses'], 2); ?></h3>
                    <p class="text-muted mb-0">Total Expenses</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-4 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-receipt text-info" style="font-size: 2rem;"></i>
                    <h3 class="mt-2"><?php echo $finance['total_transactions']; ?></h3>
                    <p class="text-muted mb-0">Total Transactions</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Recent Transactions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Recorded By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_transactions as $transaction): ?>
                                <tr>
                                    <td><?php echo date('M j, Y', strtotime($transaction['transaction_date'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo in_array($transaction['type'], ['income', 'collection']) ? 'success' : 'danger';
                                        ?>">
                                            <?php echo ucfirst($transaction['type']); ?>
                                        </span>
                                    </td>
                                    <td class="<?php echo in_array($transaction['type'], ['income', 'collection']) ? 'text-success' : 'text-danger'; ?>">
                                        ₱<?php echo number_format($transaction['amount'], 2); ?>
                                    </td>
                                    <td><?php echo escape($transaction['description']); ?></td>
                                    <td><?php echo escape($transaction['created_by_name']); ?></td>
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
                        <a href="financial_management.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-plus-circle"></i> Record New Transaction
                        </a>
                        <a href="financial_reports.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-file-earmark-spreadsheet"></i> Generate Financial Report
                        </a>
                        <a href="upload_liquidation.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-upload"></i> Upload Liquidation Report
                        </a>
                        <a href="transaction_history.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-clock-history"></i> View Transaction History
                        </a>
                    </div>
                </div>
            </div>

            <?php if (!empty($pending_liquidations)): ?>
            <div class="card mt-4 shadow-sm border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Pending Liquidations</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach ($pending_liquidations as $item): ?>
                        <a href="upload_liquidation.php?proposal_id=<?php echo $item['id']; ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><?php echo escape($item['title']); ?></h6>
                                <small><?php echo date('M j', strtotime($item['created_at'])); ?></small>
                            </div>
                            <small class="text-muted">Click to upload liquidation report</small>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>