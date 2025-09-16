<?php defined('BASE_PATH') or exit('No direct script access allowed'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-graph-up"></i> Reports & Analytics</h2>
    <div class="btn-group">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Print Report
        </button>
        <a href="export.php?year=<?php echo $year; ?>&semester=<?php echo urlencode($semester); ?>" 
           class="btn btn-success">
            <i class="bi bi-download"></i> Export Excel
        </a>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header">
        <form class="row g-3">
            <div class="col-auto">
                <label class="col-form-label">Academic Year:</label>
            </div>
            <div class="col-auto">
                <select name="year" class="form-select">
                    <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto">
                <label class="col-form-label">Semester:</label>
            </div>
            <div class="col-auto">
                <select name="semester" class="form-select">
                    <option value="">All</option>
                    <option value="1st Semester" <?php echo $semester === '1st Semester' ? 'selected' : ''; ?>>1st Semester</option>
                    <option value="2nd Semester" <?php echo $semester === '2nd Semester' ? 'selected' : ''; ?>>2nd Semester</option>
                    <option value="Summer" <?php echo $semester === 'Summer' ? 'selected' : ''; ?>>Summer</option>
                </select>
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
                <h3 class="text-success"><?php echo formatAmount($summary['total_income']); ?></h3>
                <small class="text-muted"><?php echo $summary['transaction_count']; ?> transactions</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">Total Expenses</h6>
                <h3 class="text-danger"><?php echo formatAmount($summary['total_expenses']); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">Net Balance</h6>
                <h3 class="<?php echo ($summary['total_income'] - $summary['total_expenses']) >= 0 ? 'text-success' : 'text-danger'; ?>">
                    <?php echo formatAmount($summary['total_income'] - $summary['total_expenses']); ?>
                </h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0">Proposals Summary</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $status_colors = [
                                'draft' => 'secondary',
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger'
                            ];
                            foreach ($proposals as $p): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-<?php echo $status_colors[$p['status']] ?? 'secondary'; ?>">
                                        <?php echo ucfirst($p['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $p['count']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($proposals)): ?>
                            <tr>
                                <td colspan="2" class="text-center text-muted">No proposals found</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0">Quick Links</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="../financial/transactions.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-clock-history"></i> View Transaction History
                    </a>
                    <a href="../proposals/index.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-files"></i> View All Proposals
                    </a>
                    <a href="../terminal/index.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-file-text"></i> Terminal Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>