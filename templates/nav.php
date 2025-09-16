<?php
// Get user info from session
$user_name = $_SESSION['name'] ?? 'User';
$user_role = ucfirst($_SESSION['role'] ?? 'None');
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white text-black border-bottom mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">OrgFinPro</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="./">Dashboard</a>
                </li>
                <?php if (hasRole('president') || hasRole('treasurer')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="./financial">Finances</a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="view_proposals.php">Proposals</a>
                </li>
            </ul>
            <div class="navbar-nav">
                <a href="modules/notifications/index.php" class="nav-link position-relative">
                    <i class="bi bi-bell-fill"></i>
                    <?php 
                    $unread_count = getUnreadNotificationsCount($_SESSION['user_id']);
                    if ($unread_count > 0): 
                    ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php echo $unread_count; ?>
                    </span>
                    <?php endif; ?>
                </a>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?php echo escape($user_name); ?> (<?php echo escape($user_role); ?>)
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>