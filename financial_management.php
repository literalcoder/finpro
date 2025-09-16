<?php
include 'functions.php';
requireLogin();
requireAnyRole(['president', 'treasurer']);

$action = $_GET['action'] ?? 'list';
$success = '';
$error = '';
$org_id = getOrganizationId();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($action === 'add') {
    $type = $_POST['type'];
    $amount = floatval($_POST['amount']);
    $description = trim($_POST['description']);
    $transaction_date = $_POST['transaction_date'];

    $sql = "INSERT INTO financial_transactions (org_id, type, amount, description, created_by, transaction_date) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$org_id, $type, $amount, $description, $_SESSION['user_id'], $transaction_date])) {
      $success = "Transaction recorded successfully!";
      $action = 'list';
    } else {
      $error = "Failed to record transaction.";
    }
  }
}

// Get transactions for listing
if ($action === 'list') {
  $sql = "SELECT ft.*, u.name as created_by_name FROM financial_transactions ft LEFT JOIN users u ON ft.created_by = u.id WHERE ft.org_id = ? ORDER BY ft.transaction_date DESC, ft.created_at DESC";
  $stmt = $conn->prepare($sql);
  $stmt->execute([$org_id]);
  $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Calculate balance
  $sql = "SELECT COALESCE(SUM(CASE WHEN type IN ('income', 'collection') THEN amount WHEN type IN ('expense', 'disbursement') THEN -amount END), 0) as balance FROM financial_transactions WHERE org_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->execute([$org_id]);
  $balance = $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Financial Management | OrgFinPro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="./style.css" rel="stylesheet">
</head>

<body class="bg-light">
  <?php include 'nav.php'; ?>
  <div class="container mt-4 pb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2><i class="bi bi-cash-stack"></i> Financial Management</h2>
      <div>
        <?php if ($action !== 'add'): ?>
          <a href="?action=add" class="btn btn-primary">Add Transaction</a>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($success): ?>
      <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($action === 'list'): ?>
      <!-- Financial Summary -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="card text-center" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
            <div class="card-body">
              <i class="bi bi-currency-dollar fs-2 mb-2"></i>
              <h4>₱<?php echo number_format($balance, 2); ?></h4>
              <p>Current Balance</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card text-center" style="background: linear-gradient(135deg, #4facfe, #00f2fe); color: white;">
            <div class="card-body">
              <i class="bi bi-arrow-down-circle fs-2 mb-2"></i>
              <h4>₱0.00</h4>
              <p>Monthly Income</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card text-center" style="background: linear-gradient(135deg, #ff9a9e, #fecfef); color: white;">
            <div class="card-body">
              <i class="bi bi-arrow-up-circle fs-2 mb-2"></i>
              <h4>₱0.00</h4>
              <p>Monthly Expenses</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card text-center" style="background: linear-gradient(135deg, #ffecd2, #fcb69f); color: white;">
            <div class="card-body">
              <i class="bi bi-file-earmark-bar-graph fs-2 mb-2"></i>
              <h4><?php echo count($transactions); ?></h4>
              <p>Total Transactions</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Transactions List -->
      <div class="card">
        <div class="card-header">
          <h5>Transaction History</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Type</th>
                  <th>Description</th>
                  <th>Amount</th>
                  <th>Recorded By</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($transactions)): ?>
                  <tr>
                    <td colspan="7" class="text-center text-muted">No transactions recorded yet.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($transactions as $transaction): ?>
                    <tr>
                      <td><?php echo date('M j, Y', strtotime($transaction['transaction_date'])); ?></td>
                      <td>
                        <span class="badge <?php echo in_array($transaction['type'], ['income', 'collection']) ? 'bg-success' : 'bg-danger'; ?>">
                          <?php echo ucfirst($transaction['type']); ?>
                        </span>
                      </td>
                      <td><?php echo escape($transaction['description']); ?></td>
                      <td class="<?php echo in_array($transaction['type'], ['income', 'collection']) ? 'text-success' : 'text-danger'; ?>">
                        <?php echo in_array($transaction['type'], ['income', 'collection']) ? '+' : '-'; ?>₱<?php echo number_format($transaction['amount'], 2); ?>
                      </td>
                      <td><?php echo escape($transaction['created_by_name']); ?></td>
                      <td>
                        <button class="btn btn-sm btn-outline-info" onclick="viewTransaction(<?php echo $transaction['id']; ?>)">View</button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    <?php elseif ($action === 'add'): ?>
      <!-- Add Transaction Form -->
      <div class="card">
        <div class="card-header">
          <h5>Add New Transaction</h5>
        </div>
        <div class="card-body">
          <form method="POST">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Transaction Type</label>
                  <select name="type" class="form-control" required>
                    <option value="">-- Select Type --</option>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                    <option value="collection">Collection</option>
                    <option value="disbursement">Disbursement</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Amount</label>
                  <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" name="amount" class="form-control" step="0.01" min="0" required>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Transaction Date</label>
                  <input type="date" name="transaction_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="3" placeholder="Detailed description of the transaction" required></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Receipt/Proof (Optional)</label>
              <input type="file" name="receipt" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
              <small class="text-muted">Upload receipt or proof of transaction</small>
            </div>
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary">Record Transaction</button>
              <a href="?action=list" class="btn btn-secondary">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function viewTransaction(transactionId) {
      // Implementation for viewing transaction details
      alert('View transaction details for ID: ' + transactionId);
    }
  </script>
</body>

</html>