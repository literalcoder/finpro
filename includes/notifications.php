<?php
function createNotification($user_id, $org_id, $title, $message, $type = 'info', $related_type = null, $related_id = null) {
    global $conn;
    
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
    
    $sql = "INSERT INTO notifications (user_id, org_id, title, message, type, related_type, related_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$user_id, $org_id, $title, $message, $type, $related_type, $related_id]);
}

function createOrgNotification($org_id, $title, $message, $type = 'info', $related_type = null, $related_id = null) {
    global $conn;
    
    // Get all users in the organization
    $sql = "SELECT id FROM users WHERE org_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$org_id]);
    $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($users as $user_id) {
        createNotification($user_id, $org_id, $title, $message, $type, $related_type, $related_id);
    }
}

function getUnreadNotificationsCount($user_id) {
    global $conn;
    
    $sql = "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

function markNotificationRead($notification_id, $user_id) {
    global $conn;
    
    $sql = "UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$notification_id, $user_id]);
}

function markAllNotificationsRead($user_id) {
    global $conn;
    
    $sql = "UPDATE notifications SET is_read = TRUE WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$user_id]);
}

function deleteNotification($notification_id, $user_id) {
    global $conn;
    
    $sql = "DELETE FROM notifications WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$notification_id, $user_id]);
}

function getRecentNotifications($user_id, $limit = 5) {
    global $conn;
    
    $sql = "SELECT n.*, o.name as org_name 
            FROM notifications n 
            LEFT JOIN organizations o ON n.org_id = o.id 
            WHERE n.user_id = ? 
            ORDER BY n.created_at DESC 
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll();
}