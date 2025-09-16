<?php
require_once '../includes/database.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $error = "Please enter your email address";
    } else {
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
                if ($stmt->execute([$token, $expiry, $user['id']])) {
                    // Create notification for password reset
                    createNotification(
                        $user['id'],
                        $user['org_id'],
                        "Password Reset Requested",
                        "A password reset has been requested for your account.",
                        'warning'
                    );
                    
                    // Send reset email
                    require_once '../includes/mailer.php';
                    sendPasswordResetEmail($email, $token);
                    
                    $success = "If an account exists with this email, you will receive password reset instructions.";
                }
            } else {
                // Show same message to prevent email enumeration
                $success = "If an account exists with this email, you will receive password reset instructions.";
            }
        } catch (PDOException $e) {
            $error = "An error occurred. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | OrgFinPro</title>
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
                <i class="bi bi-key" style="font-size: 3rem; color: #0d6efd;"></i>
                <h2>Reset Password</h2>
                <p class="text-muted">Enter your email to receive reset instructions</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="card shadow">
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Request Reset</button>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-3">
                <a href="login.php" class="text-decoration-none">Back to Login</a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>