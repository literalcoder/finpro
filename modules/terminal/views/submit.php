<?php defined('BASE_PATH') or exit('No direct script access allowed'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-earmark-plus"></i> Submit Terminal Report</h2>
    <a href="index.php" class="btn btn-secondary">Back to Reports</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-header">
        <h5 class="mb-0"><?php echo $current['semester'] . ' - ' . $current['year']; ?></h5>
    </div>
    <div class="card-body">
        <form method="POST" class="needs-validation" novalidate>
            <div class="mb-4">
                <h6>Financial Overview</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="border rounded p-3 mb-3">
                            <div class="text-muted">Total Income</div>
                            <div class="h4 text-success mb-0"><?php echo formatAmount($finances['total_income']); ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 mb-3">
                            <div class="text-muted">Total Expenses</div>
                            <div class="h4 text-danger mb-0"><?php echo formatAmount($finances['total_expenses']); ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 mb-3">
                            <div class="text-muted">Net Balance</div>
                            <div class="h4 <?php echo ($finances['total_income'] - $finances['total_expenses'] >= 0) ? 'text-success' : 'text-danger'; ?> mb-0">
                                <?php echo formatAmount($finances['total_income'] - $finances['total_expenses']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Activities Summary</label>
                <textarea name="activities_summary" class="form-control" rows="5" required 
                          placeholder="List all major activities conducted during the semester..."></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Key Achievements</label>
                <textarea name="achievements" class="form-control" rows="4" required
                          placeholder="Highlight significant accomplishments and milestones..."></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Challenges Faced</label>
                <textarea name="challenges" class="form-control" rows="4" required
                          placeholder="Describe major challenges and how they were addressed..."></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Recommendations</label>
                <textarea name="recommendations" class="form-control" rows="4" required
                          placeholder="Suggest improvements and recommendations for future activities..."></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Financial Summary</label>
                <textarea name="financial_summary" class="form-control" rows="4" required
                          placeholder="Provide detailed breakdown of income sources and major expenses..."></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Submit Report</button>
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