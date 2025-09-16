<?php
include 'functions.php';
requireLogin();

// Get statistics for dashboard
function getDashboardStats() {
    global $conn;
    $stats = [];
    
    if (hasRole('admin')) {
        // Admin sees system-wide stats
        $stmt = $conn->prepare("SELECT COUNT(*) FROM organizations WHERE status = 'active'");
        $stmt->execute();
        $stats['total_orgs'] = $stmt->fetchColumn();
        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE status = 'active'");
        $stmt->execute();
        $stats['total_users'] = $stmt->fetchColumn();
        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM proposals WHERE status = 'pending'");
        $stmt->execute();
        $stats['pending_proposals'] = $stmt->fetchColumn();
        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM financial_transactions WHERE type = 'liquidation'");
        $stmt->execute();
        $stats['financial_reports'] = $stmt->fetchColumn();
    } else {
        // Organization-specific stats
        $org_id = getOrganizationId();
        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM proposals WHERE org_id = ? AND status = 'pending'");
        $stmt->execute([$org_id]);
        $stats['pending_proposals'] = $stmt->fetchColumn();
        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM proposals WHERE org_id = ? AND status = 'approved'");
        $stmt->execute([$org_id]);
        $stats['approved_proposals'] = $stmt->fetchColumn();
        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM proposals WHERE org_id = ? AND status = 'rejected'");
        $stmt->execute([$org_id]);
        $stats['rejected_proposals'] = $stmt->fetchColumn();
        
        if (hasAnyRole(['president', 'treasurer'])) {
            $stmt = $conn->prepare("SELECT COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END), 0) FROM financial_transactions WHERE org_id = ?");
            $stmt->execute([$org_id]);
            $stats['org_balance'] = $stmt->fetchColumn();
        }
    }
    
    return $stats;
}

$stats = getDashboardStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | OrgFinPro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      min-height: 100vh;
      overflow-x: hidden;
      background: #f5f6fa;
    }

    /* Sidebar */
    .collapsed a {
      justify-content: center !important;
    }
    .sidebar {
      height: 100vh;
      position: fixed;
      left: 0;
      top: 0;
      background: linear-gradient(135deg, #1e3c72, #2a5298);
      color: #fff;
      width: 250px;
      transition: all 0.3s;
      z-index: 100;
    }
    .sidebar.collapsed { width: 80px; }

    /* Brand */
    .sidebar .brand {
      font-size: 1.2rem;
      font-weight: bold;
      padding: 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      background: rgba(255, 255, 255, 0.05);
      white-space: nowrap;
    }
    .sidebar .brand i { font-size: 1.5rem; color: #0d6efd; }
    .sidebar.collapsed .brand-text { display: none; }

    /* Sidebar links */
    .sidebar a {
      color: #adb5bd;
      text-decoration: none;
      padding: 12px 20px;
      display: flex;
      align-items: center;
      gap: 10px;
      transition: 0.2s;
    }
    .sidebar a:hover { background: #343a40; color: #fff; }
    .sidebar a i { color: #0d6efd; }
    .sidebar.collapsed a span { display: none; }

    /* Content */
    .content {
      margin-left: 250px;
      transition: all 0.3s;
      padding: 20px;
    }
    .sidebar.collapsed~.content { margin-left: 80px; }

    /* Stats cards */
    .stat-card {
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
      border-radius: 10px;
      padding: 20px;
      text-align: center;
    }
    .stat-card.success { background: linear-gradient(135deg, #4facfe, #00f2fe); }
    .stat-card.warning { background: linear-gradient(135deg, #ff9a9e, #fecfef); }
    .stat-card.danger { background: linear-gradient(135deg, #ffecd2, #fcb69f); }

    /* Responsive */
    @media (max-width: 768px) {
      .sidebar { left: -250px; }
      .sidebar.active { left: 0; }
      .content { margin-left: 0; }
      .sidebar.collapsed~.content { margin-left: 0; }
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="brand">
      <i class="bi bi-layers"></i>
      <span class="brand-text">OrgFinPro</span>
    </div>
    <a href="dashboard.php"><i class="bi bi-house"></i><span> Dashboard</span></a>
    
    <?php if (hasRole('admin')): ?>
      <a href="admin_users.php"><i class="bi bi-people"></i><span> User Management</span></a>
      <a href="admin_organizations.php"><i class="bi bi-building"></i><span> Organizations</span></a>
      <a href="admin_proposals.php"><i class="bi bi-file-earmark-text"></i><span> All Proposals</span></a>
      <a href="admin_financial.php"><i class="bi bi-graph-up"></i><span> Financial Overview</span></a>
      <a href="admin_audit.php"><i class="bi bi-journal-text"></i><span> Audit Logs</span></a>
    <?php else: ?>
      <a href="view_proposals.php"><i class="bi bi-file-earmark-text"></i><span> Proposals</span></a>
      <?php if (hasAnyRole(['president', 'treasurer'])): ?>
        <a href="financial_management.php"><i class="bi bi-cash-stack"></i><span> Finances</span></a>
      <?php endif; ?>
      <?php if (hasRole('president')): ?>
        <a href="organization_management.php"><i class="bi bi-building"></i><span> Organization</span></a>
        <a href="terminal_reports.php"><i class="bi bi-clipboard-data"></i><span> Terminal Reports</span></a>
      <?php endif; ?>
      <a href="reports.php"><i class="bi bi-graph-up"></i><span> Reports</span></a>
    <?php endif; ?>
  </div>

  <!-- Content -->
  <div class="content" id="content">
    <!-- Top Navbar -->
    <nav class="navbar navbar-light bg-white shadow-sm mb-4 px-3 rounded">
      <button class="btn btn-outline-primary me-2" id="toggleSidebar">
        <i class="bi bi-list"></i>
      </button>
      <div class="ms-auto d-flex align-items: center">
        <span class="me-3 fw-semibold">👋 <?php echo escape($_SESSION['name']); ?> (<?php echo ucfirst($_SESSION['role']); ?>)</span>
        <a href="logout.php" class="btn btn-danger btn-sm">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
      </div>
    </nav>

    <!-- Dashboard Content -->
    <?php if (hasRole('admin')): ?>
      <!-- Admin Dashboard -->
      <h2>Admin Dashboard</h2>
      <p>System-wide management and oversight</p>

      <div class="row mt-4 g-3">
        <div class="col-md-3">
          <div class="stat-card">
            <i class="bi bi-building fs-2 mb-2"></i>
            <h4><?php echo $stats['total_orgs']; ?></h4>
            <p>Active Organizations</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card success">
            <i class="bi bi-people fs-2 mb-2"></i>
            <h4><?php echo $stats['total_users']; ?></h4>
            <p>Registered Users</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card warning">
            <i class="bi bi-clock fs-2 mb-2"></i>
            <h4><?php echo $stats['pending_proposals']; ?></h4>
            <p>Pending Proposals</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card danger">
            <i class="bi bi-file-earmark-bar-graph fs-2 mb-2"></i>
            <h4><?php echo $stats['financial_reports']; ?></h4>
            <p>Financial Reports</p>
          </div>
        </div>
      </div>

      <div class="row mt-4 g-3">
        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <h5><i class="bi bi-person-plus"></i> Add User</h5>
              <p class="text-muted">Create new user accounts and assign roles.</p>
              <a href="admin_users.php?action=add" class="btn btn-primary btn-sm">Add User</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <h5><i class="bi bi-building-add"></i> Add Organization</h5>
              <p class="text-muted">Register new student organizations.</p>
              <a href="admin_organizations.php?action=add" class="btn btn-primary btn-sm">Add Organization</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <h5><i class="bi bi-gear"></i> System Settings</h5>
              <p class="text-muted">Configure proposal workflows and requirements.</p>
              <a href="admin_settings.php" class="btn btn-primary btn-sm">Manage Settings</a>
            </div>
          </div>
        </div>
      </div>

    <?php elseif (hasRole('president')): ?>
      <!-- President Dashboard -->
      <h2>Welcome, President <?php echo escape($_SESSION['name']); ?>!</h2>
      <p>Manage your organization's proposals and activities</p>

      <div class="row mt-4 g-3">
        <div class="col-md-3">
          <div class="stat-card warning">
            <i class="bi bi-clock fs-2 mb-2"></i>
            <h4><?php echo $stats['pending_proposals']; ?></h4>
            <p>Pending Proposals</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card success">
            <i class="bi bi-check-circle fs-2 mb-2"></i>
            <h4><?php echo $stats['approved_proposals']; ?></h4>
            <p>Approved Proposals</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card danger">
            <i class="bi bi-x-circle fs-2 mb-2"></i>
            <h4><?php echo $stats['rejected_proposals']; ?></h4>
            <p>Rejected Proposals</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card">
            <i class="bi bi-currency-dollar fs-2 mb-2"></i>
            <h4>₱<?php echo number_format($stats['org_balance'], 2); ?></h4>
            <p>Organization Balance</p>
          </div>
        </div>
      </div>

      <div class="row mt-4 g-3">
        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <h5><i class="bi bi-plus-circle"></i> Create New Proposal</h5>
              <p class="text-muted">Submit proposals for events, projects, or resolutions.</p>
              <a href="create_proposal.php" class="btn btn-primary btn-sm">Create Proposal</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <h5><i class="bi bi-file-earmark-text"></i> Terminal Report</h5>
              <p class="text-muted">Submit end-of-semester activity summary.</p>
              <a href="terminal_reports.php" class="btn btn-primary btn-sm">Submit Report</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <h5><i class="bi bi-building"></i> Organization Management</h5>
              <p class="text-muted">Manage organization details and officers.</p>
              <a href="organization_management.php" class="btn btn-primary btn-sm">Manage Org</a>
            </div>
          </div>
        </div>
      </div>

    <?php elseif (hasRole('treasurer')): ?>
      <!-- Treasurer Dashboard -->
      <h2>Welcome, Treasurer <?php echo escape($_SESSION['name']); ?>!</h2>
      <p>Manage organization finances and transactions</p>

      <div class="row mt-4 g-3">
        <div class="col-md-3">
          <div class="stat-card">
            <i class="bi bi-currency-dollar fs-2 mb-2"></i>
            <h4>₱<?php echo number_format($stats['org_balance'], 2); ?></h4>
            <p>Current Balance</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card success">
            <i class="bi bi-arrow-down-circle fs-2 mb-2"></i>
            <h4>₱0.00</h4>
            <p>Collections This Month</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card warning">
            <i class="bi bi-arrow-up-circle fs-2 mb-2"></i>
            <h4>₱0.00</h4>
            <p>Expenses This Month</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card danger">
            <i class="bi bi-exclamation-triangle fs-2 mb-2"></i>
            <h4>0</h4>
            <p>Pending Liquidations</p>
          </div>
        </div>
      </div>

      <div class="row mt-4 g-3">
        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <h5><i class="bi bi-plus-circle"></i> Record Transaction</h5>
              <p class="text-muted">Add income or expense transactions.</p>
              <a href="financial_management.php?action=add" class="btn btn-primary btn-sm">Add Transaction</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <h5><i class="bi bi-file-earmark-arrow-up"></i> Upload Liquidation</h5>
              <p class="text-muted">Submit liquidation reports for projects.</p>
              <a href="liquidation_reports.php" class="btn btn-primary btn-sm">Upload Report</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <h5><i class="bi bi-graph-up"></i> Financial Statement</h5>
              <p class="text-muted">Generate comprehensive financial reports.</p>
              <a href="reports.php?type=financial" class="btn btn-primary btn-sm">Generate Report</a>
            </div>
          </div>
        </div>
      </div>

    <?php else: ?>
      <!-- Default Dashboard for other roles -->
      <h2>Welcome, <?php echo escape($_SESSION['name']); ?>!</h2>
      <p>Access your assigned functions based on your role: <?php echo ucfirst($_SESSION['role']); ?></p>

      <div class="row mt-4 g-3">
        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <h5><i class="bi bi-file-earmark-text"></i> View Proposals</h5>
              <p class="text-muted">Review and process organization proposals.</p>
              <a href="view_proposals.php" class="btn btn-primary btn-sm">View Proposals</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <h5><i class="bi bi-graph-up"></i> Reports</h5>
              <p class="text-muted">Access relevant reports for your role.</p>
              <a href="reports.php" class="btn btn-primary btn-sm">View Reports</a>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <script>
    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');

    toggleBtn.addEventListener('click', () => {
      if (window.innerWidth <= 768) {
        sidebar.classList.toggle('active');
      } else {
        sidebar.classList.toggle('collapsed');
        document.getElementById('content').classList.toggle('collapsed');
      }
    });
  </script>
</body>
</html>
