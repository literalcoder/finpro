<?php
include 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $role     = $_POST['role'];
    $org      = $_POST['organization'] ?? null;

    if (register($name, $email, $password, $role, $org)) {
        header("Location: login.php?registered=1");
        exit();
    } else {
        $error = "Registration failed. Email might already exist.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | OrgFinPro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #1e3c72, #2a5298);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .register-card {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 15px;
      padding: 2rem;
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
      width: 100%;
      max-width: 450px;
    }
  </style>
</head>
<body>
  <div class="register-card">
    <h3 class="text-center mb-3">Create Account</h3>
    <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST">
      <div class="mb-3">
        <input type="text" class="form-control" name="name" placeholder="Full Name" required>
      </div>
      <div class="mb-3">
        <input type="email" class="form-control" name="email" placeholder="Email" required>
      </div>
      <div class="mb-3">
        <input type="password" class="form-control" name="password" placeholder="Password" required>
      </div>
      <div class="mb-3">
        <select class="form-control" name="role" required>
          <option value="">-- Select Role --</option>
          <option value="president">President</option>
          <option value="treasurer">Treasurer</option>
          <option value="auditor">Auditor</option>
          <option value="adviser">Adviser</option>
          <option value="dean">Dean</option>
          <option value="ssc">SSC</option>
        </select>
      </div>
      <div class="mb-3">
        <input type="text" class="form-control" name="organization" placeholder="Organization (if applicable)">
      </div>
      <button class="btn btn-primary w-100">Register</button>
    </form>
    <div class="text-center mt-3">
      <small>Already have an account? <a href="login.php">Login here</a></small>
    </div>
  </div>
</body>
</html>
