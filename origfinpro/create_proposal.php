<?php
session_start();
include 'db.php'; // this has $conn = new mysqli(...)

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $type = $_POST['type'];
    $description = $_POST['description'];
    $org_id = $_SESSION['org_id']; // assume org_id stored on login

    // Insert proposal
    $stmt = $conn->prepare("INSERT INTO proposals (org_id, title, type, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $org_id, $title, $type, $description);

    if ($stmt->execute()) {
        $proposal_id = $stmt->insert_id;
        $stmt->close();

        // Handle file uploads
        if (!empty($_FILES['attachments']['name'][0])) {
            foreach ($_FILES['attachments']['name'] as $key => $name) {
                $tmp_name = $_FILES['attachments']['tmp_name'][$key];
                $path = "uploads/" . time() . "_" . basename($name);

                if (move_uploaded_file($tmp_name, $path)) {
                    $file_type = pathinfo($name, PATHINFO_EXTENSION);

                    $stmt = $conn->prepare("INSERT INTO proposal_attachments (proposal_id, file_name, file_type) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $proposal_id, $path, $file_type);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        $success = "✅ Proposal submitted successfully!";
    } else {
        $error = "❌ Error: " . $stmt->error;
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
        <input type="file" name="attachments[]" class="form-control" multiple required>
        <small class="text-muted">Attach all required documents (e.g., Resolution, Waiver, Canvass).</small>
      </div>
      <button class="btn btn-primary">Submit Proposal</button>
    </form>
  </div>
</div>
</body>
</html>
