<?php
include 'functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $type = $_POST['type'];
    $description = $_POST['description'];
    $org_id = $_SESSION['org_id'] ?? 1; // assume org_id stored on login, default to 1 for now

    // Create uploads directory if it doesn't exist
    if (!is_dir('uploads')) {
        mkdir('uploads', 0755, true);
    }

    // Insert proposal using PDO from functions.php
    global $conn;
    $stmt = $conn->prepare("INSERT INTO proposals (org_id, title, type, description) VALUES (?, ?, ?, ?)");
    
    if ($stmt->execute([$org_id, $title, $type, $description])) {
        $proposal_id = $conn->lastInsertId();

        // Handle file uploads
        if (!empty($_FILES['attachments']['name'][0])) {
            foreach ($_FILES['attachments']['name'] as $key => $name) {
                $tmp_name = $_FILES['attachments']['tmp_name'][$key];
                $path = "uploads/" . time() . "_" . basename($name);

                if (move_uploaded_file($tmp_name, $path)) {
                    $file_type = pathinfo($name, PATHINFO_EXTENSION);

                    $stmt2 = $conn->prepare("INSERT INTO proposal_attachments (proposal_id, file_name, file_type) VALUES (?, ?, ?)");
                    $stmt2->execute([$proposal_id, $path, $file_type]);
                }
            }
        }

        $success = "✅ Proposal submitted successfully!";
    } else {
        $error = "❌ Error submitting proposal.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Proposal | OrgFinPro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="card shadow p-4">
    <h3>Create Proposal</h3>
    <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Proposal Title</label>
        <input type="text" name="title" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Proposal Type</label>
        <select name="type" class="form-control" required>
          <option value="Project">Project</option>
          <option value="Event">Event</option>
          <option value="Membership">Membership</option>
          <option value="Other">Other</option>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="4" required></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Attachments</label>
        <input type="file" name="attachments[]" class="form-control" multiple>
        <small class="text-muted">Attach all required documents (e.g., Resolution, Waiver, Canvass).</small>
      </div>
      <button class="btn btn-primary">Submit Proposal</button>
      <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </form>
  </div>
</div>
</body>
</html>