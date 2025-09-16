
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
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $role = $_POST['role'];
        $org_id = $_POST['org_id'] ?: null;
        $status = $_POST['status'] ?? 'active';
        
        if (register($name, $email, $password, $role, $org_id)) {
            $success = "User created successfully!";
            $action = 'list';
        } else {
            $error = "Failed to create user. Email might already exist.";
        }
    } elseif ($action === 'edit') {
        $user_id = $_POST['user_id'];
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        $org_id = $_POST['org_id'] ?: null;
        $status = $_POST['status'];
        
        $sql = "UPDATE users SET name=?, email=?, role=?, org_id=?, status=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$name, $email, $role, $org_id, $status, $user_id])) {
            $success = "User updated successfully!";
            $action = 'list';
        } else {
            $error = "Failed to update user.";
        }
    } elseif ($action === 'reset_password') {
        $user_id = $_POST['user_id'];
        $new_password = $_POST['new_password'];
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $sql = "UPDATE users SET password=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$hashed_password, $user_id])) {
            $success = "Password reset successfully!";
            $action = 'list';
        } else {
            $error = "Failed to reset password.";
        }
    }
}

// Get users for listing
if ($action === 'list') {
    $sql = "SELECT u.*, o.name as org_name FROM users u LEFT JOIN organizations o ON u.org_id = o.id ORDER BY u.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get organizations for dropdowns
$sql = "SELECT id, name FROM organizations WHERE status = 'active'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$organizations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user for editing
if ($action === 'edit' && isset($_GET['id'])) {
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_GET['id']]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Management | OrgFinPro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people"></i> User Management</h2>
    <div>
      <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
      <?php if ($action !== 'add'): ?>
        <a href="?action=add" class="btn btn-primary">Add New User</a>
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
    <!-- Users List -->
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Organization</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $user): ?>
              <tr>
                <td><?php echo escape($user['name']); ?></td>
                <td><?php echo escape($user['email']); ?></td>
                <td><span class="badge bg-primary"><?php echo ucfirst($user['role']); ?></span></td>
                <td><?php echo $user['org_name'] ? escape($user['org_name']) : 'N/A'; ?></td>
                <td>
                  <span class="badge <?php echo $user['status'] === 'active' ? 'bg-success' : 'bg-danger'; ?>">
                    <?php echo ucfirst($user['status']); ?>
                  </span>
                </td>
                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                <td>
                  <a href="?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                  <button class="btn btn-sm btn-outline-warning" onclick="resetPassword(<?php echo $user['id']; ?>)">Reset Password</button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  <?php elseif ($action === 'add'): ?>
    <!-- Add User Form -->
    <div class="card">
      <div class="card-header">
        <h5>Add New User</h5>
      </div>
      <div class="card-body">
        <form method="POST">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-control" required>
                  <option value="">-- Select Role --</option>
                  <option value="president">President</option>
                  <option value="treasurer">Treasurer</option>
                  <option value="auditor">Auditor</option>
                  <option value="adviser">Adviser</option>
                  <option value="dean">Dean</option>
                  <option value="ssc">SSC</option>
                  <option value="admin">Admin</option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Organization</label>
                <select name="org_id" class="form-control">
                  <option value="">-- No Organization --</option>
                  <?php foreach ($organizations as $org): ?>
                    <option value="<?php echo $org['id']; ?>"><?php echo escape($org['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Create User</button>
            <a href="?action=list" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>

  <?php elseif ($action === 'edit' && isset($edit_user)): ?>
    <!-- Edit User Form -->
    <div class="card">
      <div class="card-header">
        <h5>Edit User</h5>
      </div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo escape($edit_user['name']); ?>" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo escape($edit_user['email']); ?>" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-control" required>
                  <option value="president" <?php echo $edit_user['role'] === 'president' ? 'selected' : ''; ?>>President</option>
                  <option value="treasurer" <?php echo $edit_user['role'] === 'treasurer' ? 'selected' : ''; ?>>Treasurer</option>
                  <option value="auditor" <?php echo $edit_user['role'] === 'auditor' ? 'selected' : ''; ?>>Auditor</option>
                  <option value="adviser" <?php echo $edit_user['role'] === 'adviser' ? 'selected' : ''; ?>>Adviser</option>
                  <option value="dean" <?php echo $edit_user['role'] === 'dean' ? 'selected' : ''; ?>>Dean</option>
                  <option value="ssc" <?php echo $edit_user['role'] === 'ssc' ? 'selected' : ''; ?>>SSC</option>
                  <option value="admin" <?php echo $edit_user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Organization</label>
                <select name="org_id" class="form-control">
                  <option value="">-- No Organization --</option>
                  <?php foreach ($organizations as $org): ?>
                    <option value="<?php echo $org['id']; ?>" <?php echo $edit_user['org_id'] == $org['id'] ? 'selected' : ''; ?>>
                      <?php echo escape($org['name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
              <option value="active" <?php echo $edit_user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
              <option value="inactive" <?php echo $edit_user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Update User</button>
            <a href="?action=list" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- Password Reset Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reset Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="reset_password">
        <input type="hidden" name="user_id" id="resetUserId">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">Reset Password</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function resetPassword(userId) {
  document.getElementById('resetUserId').value = userId;
  new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
}
</script>
</body>
</html>
