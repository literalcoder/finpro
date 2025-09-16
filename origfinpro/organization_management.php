
<?php
include 'functions.php';
requireLogin();
requireRole('president');

$org_id = getOrganizationId();
$success = '';
$error = '';

if (!$org_id) {
    $error = "You must be assigned to an organization to manage it.";
}

// Get organization details
if ($org_id) {
    $sql = "SELECT * FROM organizations WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$org_id]);
    $organization = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get organization members
    $sql = "SELECT u.*, o.name as org_name FROM users u 
            LEFT JOIN organizations o ON u.org_id = o.id 
            WHERE u.org_id = ? ORDER BY u.role, u.name";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$org_id]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_org'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $code = trim($_POST['code']);
    
    $sql = "UPDATE organizations SET name = ?, description = ?, code = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([$name, $description, $code, $org_id])) {
        $success = "Organization updated successfully!";
        // Refresh organization data
        $sql = "SELECT * FROM organizations WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$org_id]);
        $organization = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = "Failed to update organization.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Management | OrgFinPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-building"></i> Organization Management</h2>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($org_id && $organization): ?>
    <div class="row">
        <div class="col-md-8">
            <!-- Organization Details -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-info-circle"></i> Organization Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Organization Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo escape($organization['name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Organization Code</label>
                            <input type="text" name="code" class="form-control" value="<?php echo escape($organization['code']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4"><?php echo escape($organization['description']); ?></textarea>
                        </div>
                        
                        <button type="submit" name="update_org" class="btn btn-primary">Update Organization</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Organization Stats -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-bar-chart"></i> Quick Stats</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Total Members:</strong> <?php echo count($members); ?>
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong> 
                        <span class="badge bg-<?php echo $organization['status'] === 'active' ? 'success' : 'secondary'; ?>">
                            <?php echo ucfirst($organization['status']); ?>
                        </span>
                    </div>
                    <div class="mb-3">
                        <strong>Created:</strong> <?php echo date('M j, Y', strtotime($organization['created_at'])); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Organization Members -->
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5><i class="bi bi-people"></i> Organization Members</h5>
        </div>
        <div class="card-body">
            <?php if (empty($members)): ?>
                <div class="text-center py-4">
                    <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                    <h5 class="text-muted mt-3">No members found</h5>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $member): ?>
                            <tr>
                                <td><?php echo escape($member['name']); ?></td>
                                <td><?php echo escape($member['email']); ?></td>
                                <td>
                                    <span class="badge bg-info"><?php echo ucfirst($member['role']); ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $member['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($member['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($member['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
