<?php
require_once '../includes/database.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$valid_token = false;
$user_id = null;

if (empty($token)) {
    $error = "Invalid reset token";
} else {
    try {
        $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user) {
            $valid_token = true;
            $user_id = $user['id'];
        } else {
            $error = "Invalid or expired reset token";
        }
    } catch (PDOException $e) {
        $error = "An error occurred. Please try again later.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
            if ($stmt->execute([$hashed_password, $user_id])) {
                // Create notification for successful password reset
                createNotification(
                    $user_id,
                    null,
                    "Password Reset Complete",
                    "Your password has been successfully reset.",
                    'success'
                );
                
                $success = "Password has been reset successfully. You can now login with your new password.";
            }
        } catch (PDOException $e) {
            $error = "Failed to update password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password | OrgFinPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .reset-container { max-width: 400px; margin: 100px auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="reset-container">
            <div class="text-center mb-4">
                <i class="bi bi-shield-lock" style="font-size: 3rem; color: #0d6efd;"></i>
                <h2>Set New Password</h2>
                <p class="text-muted">Enter your new password below</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                    <div class="mt-2">
                        <a href="login.php" class="btn btn-primary btn-sm">Go to Login</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($valid_token && !$success): ?>
            <div class="card shadow">
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" 
                                   required minlength="8">
                            <div class="form-text">Must be at least 8 characters long</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" 
                                   required minlength="8">
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Update Password</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="text-center mt-3">
                <a href="login.php" class="text-decoration-none">Back to Login</a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>