<?php
require_once 'includes/database.php';
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ./");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password";
    } else {
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] === 'active' || $user['role'] === 'admin') {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['org_id'] = $user['org_id'];

                    // Create login notification
                    createNotification(
                        $user['id'],
                        $user['org_id'],
                        "Welcome Back!",
                        "You have successfully logged in.",
                        'info'
                    );

                    $redirect = match($user['role']) {
                        'admin' => '../admin_dashboard.php',
                        'treasurer' => '../treasurer_dashboard.php',
                        'president' => '../president_dashboard.php',
                        'adviser', 'dean', 'ssc' => '../pressident_dashboard.php',
                        default => '../index.php'
                    };
                    header("Location: " . $redirect);
                    exit();
                } else {
                    $error = "Your account is not active. Please contact your organization administrator.";
                }
            } else {
                $error = "Invalid email or password";
            }
        } catch (PDOException $e) {
            $error = "Login failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | OrgFinPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }

        .brand {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand i {
            font-size: 3rem;
            color: #0d6efd;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="login-container">

            <div class="card shadow">
                <div class="card-body">
                    <div class="brand">
                        <i class="bi bi-building"></i>
                        <h2>OrgFinPro</h2>
                        <p class="text-muted">Organization Financial Management</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="form-floating mb-3">
                            <input type="email" name="email" class="form-control" id="floatingInput" placeholder="name@example.com" required>
                            <label for="floatingInput">Email address</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" required>
                            <label for="floatingPassword">Password</label>
                        </div>

                        <button type="submit" class="btn btn-primary mt-2 mb-3 w-100">Login</button>
                    </form>
                </div>
            </div>

            <div class="text-center mt-3">
                <a href="reset_password.php" class="text-decoration-none">Forgot Password?</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function() {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>

</html>