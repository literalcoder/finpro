<?php
require_once '../../includes/auth.php';
require_once '../../includes/utils.php';
require_once '../../includes/database.php';

requireLogin();

$page_title = 'Notifications';
$user_id = $_SESSION['user_id'];

// Create notifications table if not exists
$createTable = "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    org_id INT,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    related_type VARCHAR(50),
    related_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (org_id) REFERENCES organizations(id)
)";
$conn->exec($createTable);

// Mark notifications as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ? AND id = ?");
    $stmt->execute([$user_id, intval($_POST['mark_read'])]);
}

// Get user's notifications
$sql = "SELECT n.*, o.name as org_name 
        FROM notifications n 
        LEFT JOIN organizations o ON n.org_id = o.id 
        WHERE n.user_id = ? 
        ORDER BY n.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

ob_start();
include 'views/list.php';
$content = ob_get_clean();

include '../../templates/base.php';