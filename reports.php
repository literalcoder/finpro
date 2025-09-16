<?php
include 'functions.php';
requireLogin();

$org_id = getOrganizationId();
$report_type = isset($_GET['type']) ? $_GET['type'] : 'overview';

// Get data for different types of reports
function getFinancialData($org_id)
{
    global $conn;
    $data = [];

    if ($org_id) {
        // Income vs Expenses
        $sql = "SELECT 
                    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expenses,
                    COUNT(*) as total_transactions
                FROM financial_transactions WHERE org_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$org_id]);
        $data['summary'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Recent transactions
        $sql = "SELECT * FROM financial_transactions WHERE org_id = ? ORDER BY transaction_date DESC LIMIT 10";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$org_id]);
        $data['recent_transactions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return $data;
}

function getProposalData($org_id)
{
    global $conn;
    $data = [];

    if (hasRole('admin')) {
        $sql = "SELECT status, COUNT(*) as count FROM proposals GROUP BY status";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    } else {
        $sql = "SELECT status, COUNT(*) as count FROM proposals WHERE org_id = ? GROUP BY status";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$org_id]);
    }

    $data['status_counts'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $data;
}

$financial_data = getFinancialData($org_id);
$proposal_data = getProposalData($org_id);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | OrgFinPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="./style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-light">
    <?php include 'nav.php'; ?>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-graph-up"></i> Reports & Analytics</h2>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <!-- Report Type Tabs -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?php echo $report_type === 'overview' ? 'active' : ''; ?>" href="?type=overview">Overview</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $report_type === 'financial' ? 'active' : ''; ?>" href="?type=financial">Financial</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $report_type === 'proposals' ? 'active' : ''; ?>" href="?type=proposals">Proposals</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $report_type === 'activities' ? 'active' : ''; ?>" href="?type=activities">Activities</a>
            </li>
        </ul>

        <?php if ($report_type === 'overview'): ?>
            <!-- Overview Report -->
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body text-center">
                            <i class="bi bi-file-earmark-text fs-2"></i>
                            <h4><?php echo count($proposal_data['status_counts']); ?></h4>
                            <p>Total Proposals</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body text-center">
                            <i class="bi bi-currency-dollar fs-2"></i>
                            <h4>₱<?php echo number_format($financial_data['summary']['total_income'] ?? 0, 2); ?></h4>
                            <p>Total Income</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body text-center">
                            <i class="bi bi-arrow-up-circle fs-2"></i>
                            <h4>₱<?php echo number_format($financial_data['summary']['total_expenses'] ?? 0, 2); ?></h4>
                            <p>Total Expenses</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body text-center">
                            <i class="bi bi-calculator fs-2"></i>
                            <h4>₱<?php echo number_format(($financial_data['summary']['total_income'] ?? 0) - ($financial_data['summary']['total_expenses'] ?? 0), 2); ?></h4>
                            <p>Net Balance</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Proposal Status Distribution</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="proposalChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Financial Overview</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="financialChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($report_type === 'financial'): ?>
            <!-- Financial Report -->
            <div class="card shadow">
                <div class="card-header">
                    <h5><i class="bi bi-cash-stack"></i> Financial Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-success">₱<?php echo number_format($financial_data['summary']['total_income'] ?? 0, 2); ?></h4>
                                <p class="mb-0">Total Income</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-danger">₱<?php echo number_format($financial_data['summary']['total_expenses'] ?? 0, 2); ?></h4>
                                <p class="mb-0">Total Expenses</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-primary">₱<?php echo number_format(($financial_data['summary']['total_income'] ?? 0) - ($financial_data['summary']['total_expenses'] ?? 0), 2); ?></h4>
                                <p class="mb-0">Net Balance</p>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($financial_data['recent_transactions'])): ?>
                        <h6>Recent Transactions</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($financial_data['recent_transactions'] as $transaction): ?>
                                        <tr>
                                            <td><?php echo date('M j, Y', strtotime($transaction['transaction_date'])); ?></td>
                                            <td><?php echo escape($transaction['description']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $transaction['type'] === 'income' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($transaction['type']); ?>
                                                </span>
                                            </td>
                                            <td class="text-<?php echo $transaction['type'] === 'income' ? 'success' : 'danger'; ?>">
                                                <?php echo $transaction['type'] === 'income' ? '+' : '-'; ?>₱<?php echo number_format($transaction['amount'], 2); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($report_type === 'proposals'): ?>
            <!-- Proposals Report -->
            <div class="card shadow">
                <div class="card-header">
                    <h5><i class="bi bi-file-earmark-text"></i> Proposal Analytics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($proposal_data['status_counts'] as $status): ?>
                            <div class="col-md-3 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h4><?php echo $status['count']; ?></h4>
                                    <p class="mb-0"><?php echo ucfirst(str_replace('_', ' ', $status['status'])); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Activities Report -->
            <div class="card shadow">
                <div class="card-header">
                    <h5><i class="bi bi-activity"></i> Activities Report</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="bi bi-activity text-muted" style="font-size: 4rem;"></i>
                        <h5 class="text-muted mt-3">Activities Report</h5>
                        <p class="text-muted">Detailed activity reporting functionality coming soon.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Export Options -->
        <div class="card shadow mt-4">
            <div class="card-header">
                <h5><i class="bi bi-download"></i> Export Reports</h5>
            </div>
            <div class="card-body">
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="window.print()">
                        <i class="bi bi-printer"></i> Print Report
                    </button>
                    <button class="btn btn-outline-success">
                        <i class="bi bi-file-earmark-excel"></i> Export to Excel
                    </button>
                    <button class="btn btn-outline-danger">
                        <i class="bi bi-filetype-pdf"></i> Export to PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Proposal Status Chart
        <?php if ($report_type === 'overview'): ?>
            const proposalCtx = document.getElementById('proposalChart').getContext('2d');
            const proposalChart = new Chart(proposalCtx, {
                type: 'doughnut',
                data: {
                    labels: [<?php echo implode(',', array_map(function ($item) {
                                    return '"' . ucfirst(str_replace('_', ' ', $item['status'])) . '"';
                                }, $proposal_data['status_counts'])); ?>],
                    datasets: [{
                        data: [<?php echo implode(',', array_column($proposal_data['status_counts'], 'count')); ?>],
                        backgroundColor: ['#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6c757d']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Financial Chart
            const financialCtx = document.getElementById('financialChart').getContext('2d');
            const financialChart = new Chart(financialCtx, {
                type: 'bar',
                data: {
                    labels: ['Income', 'Expenses'],
                    datasets: [{
                        data: [<?php echo $financial_data['summary']['total_income'] ?? 0; ?>, <?php echo $financial_data['summary']['total_expenses'] ?? 0; ?>],
                        backgroundColor: ['#198754', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        <?php endif; ?>
    </script>
</body>

</html>