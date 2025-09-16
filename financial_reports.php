<?php
$page_title = 'Financial Reports';
include 'functions.php';
requireLogin();
requireAnyRole(['treasurer', 'president', 'adviser', 'dean', 'ssc']);

$org_id = getOrganizationId();
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Get transactions for the period
$sql = "SELECT ft.*, u.name as created_by_name 
        FROM financial_transactions ft 
        LEFT JOIN users u ON ft.created_by = u.id 
        WHERE ft.org_id = ? 
        AND ft.transaction_date BETWEEN ? AND ?
        ORDER BY ft.transaction_date, ft.created_at";
$stmt = $conn->prepare($sql);
$stmt->execute([$org_id, $start_date, $end_date]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate summaries
$total_income = 0;
$total_expenses = 0;
foreach ($transactions as $t) {
    if (in_array($t['type'], ['income', 'collection'])) {
        $total_income += $t['amount'];
    } else {
        $total_expenses += $t['amount'];
    }
}
$balance = $total_income - $total_expenses;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | OrgFinPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="./style.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'nav.php'; ?>

<div class="container mt-4 pb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-graph-up"></i> Financial Reports</h2>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Print Report
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header">
            <form class="row g-3">
                <div class="col-auto">
                    <label class="col-form-label">Date Range:</label>
                </div>
                <div class="col-auto">
                    <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                </div>
                <div class="col-auto">
                    <label class="col-form-label">to</label>
                </div>
                <div class="col-auto">
                    <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Income</h6>
                    <h3 class="text-success">₱<?php echo number_format($total_income, 2); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Expenses</h6>
                    <h3 class="text-danger">₱<?php echo number_format($total_expenses, 2); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="text-muted">Net Balance</h6>
                    <h3 class="<?php echo $balance >= 0 ? 'text-success' : 'text-danger'; ?>">
                        ₱<?php echo number_format($balance, 2); ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header">
            <h5 class="mb-0">Transaction Details</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
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
                        <?php foreach ($transactions as $transaction): ?>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>