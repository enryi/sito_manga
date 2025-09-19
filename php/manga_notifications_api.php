<?php
    session_start();
    require_once '../notification_functions.php';

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "manga";
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'message' => 'Database connection failed']));
    }

    if (!isset($_SESSION['user_id'])) {
        die(json_encode(['success' => false, 'message' => 'User not logged in']));
    }

    $user_id = $_SESSION['user_id'];
    $action = $_GET['action'] ?? '';

    header('Content-Type: application/json');

    switch ($action) {
        case 'get_count':
            $count = getUnreadNotificationsCount($conn, $user_id);
            echo json_encode(['success' => true, 'count' => $count]);
            break;
            
        case 'get_notifications':
            $notifications = getNotificationsWithTimeAgo($conn, $user_id);
            echo json_encode(['success' => true, 'notifications' => $notifications]);
            break;
            
        case 'mark_read':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_id'])) {
                $notification_id = intval($_POST['notification_id']);
                $success = markNotificationAsRead($conn, $notification_id, $user_id);
                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to mark notification as read']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid request parameters']);
            }
            break;
            
        case 'mark_all_read':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $success = markAllNotificationsAsRead($conn, $user_id);
                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to mark all notifications as read']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            }
            break;
            
        case 'delete_notification':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_id'])) {
                $notification_id = intval($_POST['notification_id']);
                $success = deleteNotification($conn, $notification_id, $user_id);
                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'Notification deleted']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to delete notification']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid request parameters']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action specified']);
            break;
    }

    $conn->close();

    function getNotificationsWithTimeAgo($conn, $user_id) {
        $query = "SELECT *, 
                CASE 
                    WHEN TIMESTAMPDIFF(MINUTE, created_at, NOW()) < 1 THEN 'Just now'
                    WHEN TIMESTAMPDIFF(MINUTE, created_at, NOW()) < 60 THEN CONCAT(TIMESTAMPDIFF(MINUTE, created_at, NOW()), ' min ago')
                    WHEN TIMESTAMPDIFF(HOUR, created_at, NOW()) < 24 THEN CONCAT(TIMESTAMPDIFF(HOUR, created_at, NOW()), ' h ago')
                    WHEN TIMESTAMPDIFF(DAY, created_at, NOW()) < 7 THEN CONCAT(TIMESTAMPDIFF(DAY, created_at, NOW()), ' d ago')
                    ELSE DATE_FORMAT(created_at, '%b %d')
                END as time_ago
                FROM notifications 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 50";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("Failed to prepare get notifications query: " . $conn->error);
            return [];
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $notifications = [];
        
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        
        $stmt->close();
        return $notifications;
    }
?>