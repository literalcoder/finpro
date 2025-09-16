<!-- dashboard.php -->
<?php
include 'functions.php';
requireLogin();
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
    <a href="#"><i class="bi bi-house"></i><span> Dashboard</span></a>
    <a href="#"><i class="bi bi-file-earmark-text"></i><span> Proposals</span></a>
    <a href="#"><i class="bi bi-cash-stack"></i><span> Finances</span></a>
    <a href="#"><i class="bi bi-graph-up"></i><span> Reports</span></a>
  </div>

  <!-- Content -->
  <div class="content" id="content">
    <!-- Top Navbar -->
    <nav class="navbar navbar-light bg-white shadow-sm mb-4 px-3 rounded">
      <button class="btn btn-outline-primary me-2" id="toggleSidebar">
        <i class="bi bi-list"></i>
      </button>
      <div class="ms-auto d-flex align-items-center">
        <span class="me-3 fw-semibold">👋 <?php echo $_SESSION['name']; ?></span>
        <a href="logout.php" class="btn btn-danger btn-sm">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
      </div>
    </nav>

    <!-- Dashboard Content -->
    <h2>Welcome, <?php echo $_SESSION['name']; ?>!</h2>
    <p>This is your dashboard. Use the sidebar to navigate.</p>

    <div class="row mt-4 g-3">
      <div class="col-md-4">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h5><i class="bi bi-file-earmark-text"></i> Proposals</h5>
            <p class="text-muted">Submit and track your proposals.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h5><i class="bi bi-cash-stack"></i> Finances</h5>
            <p class="text-muted">Monitor expenses and income.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h5><i class="bi bi-graph-up"></i> Reports</h5>
            <p class="text-muted">Generate financial reports.</p>
          </div>
        </div>
      </div>
    </div>
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
