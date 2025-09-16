<?php
require_once '../../includes/auth.php';
require_once '../../includes/utils.php';
require_once '../../includes/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';
$notification_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$user_id = $_SESSION['user_id'];
$response = ['success' => false];

try {
    switch ($action) {
        case 'mark_read':
            $response['success'] = markNotificationRead($notification_id, $user_id);
            $response['unread_count'] = getUnreadNotificationsCount($user_id);
            break;
            
        case 'mark_all_read':
            $response['success'] = markAllNotificationsRead($user_id);
            $response['unread_count'] = 0;
            break;
            
        case 'delete':
            $response['success'] = deleteNotification($notification_id, $user_id);
            $response['unread_count'] = getUnreadNotificationsCount($user_id);
            break;
            
        default:
            http_response_code(400);
            $response['error'] = 'Invalid action';
    }
} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = 'Server error';
}

echo json_encode($response);