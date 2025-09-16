<?php
include 'functions.php';
requireLogin();
requireRole('treasurer');

$proposal_id = isset($_GET['proposal_id']) ? intval($_GET['proposal_id']) : 0;
$org_id = getOrganizationId();
$error = '';
$success = '';

// Get proposal details
$sql = "SELECT p.*, o.name as org_name 
        FROM proposals p 
        JOIN organizations o ON p.org_id = o.id 
        WHERE p.id = ? AND p.org_id = ? AND p.status = 'approved'";
$stmt = $conn->prepare($sql);
$stmt->execute([$proposal_id, $org_id]);
$proposal = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$proposal) {
    header("Location: treasurer_dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);
    $description = trim($_POST['description']);
    $transaction_date = $_POST['transaction_date'];
    
    if ($amount <= 0) {
        $error = "Amount must be greater than 0";
    } else {
        try {
            $conn->beginTransaction();
            
            // Record liquidation transaction
            $sql = "INSERT INTO financial_transactions (org_id, type, amount, description, created_by, transaction_date, proposal_id) 
                   VALUES (?, 'expense', ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$org_id, $amount, $description, $_SESSION['user_id'], $transaction_date, $proposal_id]);
            
            $conn->commit();
            $success = "Liquidation recorded successfully!";
        } catch (Exception $e) {
            $conn->rollBack();
            $error = "Failed to record liquidation: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Liquidation | OrgFinPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="./style.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'nav.php'; ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-receipt"></i> Upload Liquidation</h2>
        <a href="treasurer_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-header">
            <h5 class="mb-0">Proposal Details</h5>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <p><strong>Title:</strong> <?php echo escape($proposal['title']); ?></p>
                    <p><strong>Type:</strong> <?php echo ucfirst($proposal['type']); ?></p>
                    <p><strong>Budget:</strong> ₱<?php echo number_format($proposal['budget_amount'], 2); ?></p>
                </div>
            </div>

            <form method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label class="form-label">Amount</label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" name="amount" class="form-control" step="0.01" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Transaction Date</label>
                    <input type="date" name="transaction_date" class="form-control" required 
                           value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" required 
                              placeholder="Enter liquidation details..."><?php echo "Liquidation for Proposal: " . $proposal['title']; ?></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Submit Liquidation</button>
                    <a href="treasurer_dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>
</body>
</html>