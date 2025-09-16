<?php
include 'functions.php';
requireLogin();
requireRole('admin');

$action = $_GET['action'] ?? 'list';
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $name = trim($_POST['name']);
        $code = trim($_POST['code']);
        $description = trim($_POST['description']);
        $status = $_POST['status'] ?? 'active';
        
        $sql = "INSERT INTO organizations (name, code, description, status) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$name, $code, $description, $status])) {
            $success = "Organization created successfully!";
            $action = 'list';
        } else {
            $error = "Failed to create organization. Code might already exist.";
        }
    } elseif ($action === 'edit') {
        $org_id = $_POST['org_id'];
        $name = trim($_POST['name']);
        $code = trim($_POST['code']);
        $description = trim($_POST['description']);
        $status = $_POST['status'];
        
        $sql = "UPDATE organizations SET name=?, code=?, description=?, status=?, updated_at=CURRENT_TIMESTAMP WHERE id=?";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$name, $code, $description, $status, $org_id])) {
            $success = "Organization updated successfully!";
            $action = 'list';
        } else {
            $error = "Failed to update organization.";
        }
    }
}

// Get organizations for listing
if ($action === 'list') {
    $sql = "SELECT o.*, COUNT(u.id) as user_count FROM organizations o LEFT JOIN users u ON o.id = u.org_id GROUP BY o.id ORDER BY o.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $organizations = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get organization for editing
if ($action === 'edit' && isset($_GET['id'])) {
    $sql = "SELECT * FROM organizations WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_GET['id']]);
    $edit_org = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <div>
      <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
      <?php if ($action !== 'add'): ?>
        <a href="?action=add" class="btn btn-primary">Add New Organization</a>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
  <?php endif; ?>

  <?php if ($action === 'list'): ?>
    <!-- Organizations List -->
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Name</th>
                <th>Code</th>
                <th>Description</th>
                <th>Status</th>
                <th>Members</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($organizations as $org): ?>
              <tr>
                <td><?php echo escape($org['name']); ?></td>
                <td><code><?php echo escape($org['code']); ?></code></td>
                <td><?php echo escape(substr($org['description'], 0, 50)) . (strlen($org['description']) > 50 ? '...' : ''); ?></td>
                <td>
                  <span class="badge <?php echo $org['status'] === 'active' ? 'bg-success' : 'bg-danger'; ?>">
                    <?php echo ucfirst($org['status']); ?>
                  </span>
                </td>
                <td><?php echo $org['user_count']; ?> users</td>
                <td><?php echo date('M j, Y', strtotime($org['created_at'])); ?></td>
                <td>
                  <a href="?action=edit&id=<?php echo $org['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                  <a href="admin_org_details.php?id=<?php echo $org['id']; ?>" class="btn btn-sm btn-outline-info">View Details</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  <?php elseif ($action === 'add'): ?>
    <!-- Add Organization Form -->
    <div class="card">
      <div class="card-header">
        <h5>Add New Organization</h5>
      </div>
      <div class="card-body">
        <form method="POST">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Organization Name</label>
                <input type="text" name="name" class="form-control" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Organization Code</label>
                <input type="text" name="code" class="form-control" placeholder="e.g., CSS, BAC" required>
                <small class="text-muted">Unique identifier for the organization</small>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Brief description of the organization"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Create Organization</button>
            <a href="?action=list" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>

  <?php elseif ($action === 'edit' && isset($edit_org)): ?>
    <!-- Edit Organization Form -->
    <div class="card">
      <div class="card-header">
        <h5>Edit Organization</h5>
      </div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="org_id" value="<?php echo $edit_org['id']; ?>">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Organization Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo escape($edit_org['name']); ?>" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Organization Code</label>
                <input type="text" name="code" class="form-control" value="<?php echo escape($edit_org['code']); ?>" required>
                <small class="text-muted">Unique identifier for the organization</small>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"><?php echo escape($edit_org['description']); ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
              <option value="active" <?php echo $edit_org['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
              <option value="inactive" <?php echo $edit_org['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Update Organization</button>
            <a href="?action=list" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
