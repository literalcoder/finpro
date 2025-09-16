<?php defined('BASE_PATH') or exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-md-3">
        <div class="card mb-4 shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-wallet2 <?php echo $stats['balance'] >= 0 ? 'text-success' : 'text-danger'; ?>" style="font-size: 2rem;"></i>
                <h3 class="mt-2"><?php echo formatAmount($stats['balance']); ?></h3>
                <p class="text-muted mb-0">Current Balance</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card mb-4 shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-graph-up-arrow text-success" style="font-size: 2rem;"></i>
                <h3 class="mt-2"><?php echo formatAmount($stats['total_income']); ?></h3>
                <p class="text-muted mb-0">Total Income</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card mb-4 shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-graph-down-arrow text-danger" style="font-size: 2rem;"></i>
                <h3 class="mt-2"><?php echo formatAmount($stats['total_expenses']); ?></h3>
                <p class="text-muted mb-0">Total Expenses</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card mb-4 shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-receipt text-info" style="font-size: 2rem;"></i>
                <h3 class="mt-2"><?php echo $stats['total_transactions']; ?></h3>
                <p class="text-muted mb-0">Total Transactions</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Transactions</h5>
                <div>
                    <a href="./financial/transaction/new" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle"></i> New Transaction
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php include '_transactions_table.php'; ?>
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
                    <a href="transaction.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-plus-circle"></i> Record New Transaction
                    </a>
                    <a href="reports.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Generate Financial Report
                    </a>
                    <a href="../proposals/liquidations.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-receipt-cutoff"></i> Pending Liquidations
                    </a>
                    <a href="history.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-clock-history"></i> Transaction History
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>