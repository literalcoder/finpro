<?php defined('BASE_PATH') or exit('No direct script access allowed'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-cash"></i> Record Transaction</h2>
    <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-body">
        <form method="POST" class="needs-validation" novalidate>
            <div class="mb-3">
                <label class="form-label">Transaction Type</label>
                <select name="type" class="form-select" required>
                    <option value="">Select type</option>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                    <option value="collection">Collection</option>
                    <option value="disbursement">Disbursement</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Amount</label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Transaction Date</label>
                <input type="date" name="transaction_date" class="form-control" required 
                       value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3" required></textarea>
            </div>

            <?php if (!empty($proposals)): ?>
            <div class="mb-3">
                <label class="form-label">Related Proposal (Optional)</label>
                <select name="proposal_id" class="form-select">
                    <option value="">Select proposal</option>
                    <?php foreach ($proposals as $proposal): ?>
                        <option value="<?php echo $proposal['id']; ?>">
                            <?php echo escape($proposal['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Select a proposal if this transaction is related to an approved proposal</div>
            </div>
            <?php endif; ?>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Record Transaction</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

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