<?php
$page_title = 'Transaction History';
include 'functions.php';
requireLogin();
requireAnyRole(['treasurer', 'president']);

$org_id = getOrganizationId();

// Get all transactions
$sql = "SELECT ft.*, u.name as created_by_name 
        FROM financial_transactions ft 
        LEFT JOIN users u ON ft.created_by = u.id 
        WHERE ft.org_id = ? 
        ORDER BY ft.transaction_date DESC, ft.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$org_id]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-clock-history"></i> Transaction History</h2>
        <a href="financial_management.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Transaction
        </a>
    </div>

    <div class="card shadow">
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
                            <th>Created At</th>
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
                            <td><?php echo date('M j, Y g:i A', strtotime($transaction['created_at'])); ?></td>
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