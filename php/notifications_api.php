<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "manga";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Check if user is logged in
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
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
        }
        break;
        
    case 'mark_all_read':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $success = markAllNotificationsAsRead($conn, $user_id);
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

$conn->close();

// Helper function to get notifications with time ago
function getNotificationsWithTimeAgo($conn, $user_id) {
    $query = "SELECT *, 
              CASE 
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

function createNotification($conn, $user_id, $type, $title, $message, $manga_id = null, $reason = null) {
    $query = "INSERT INTO notifications (
        user_id, 
        manga_id, 
        manga_title, 
        type, 
        title,
        message,
        reason,
        is_read, 
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Failed to prepare notification query: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("iisssss", $user_id, $manga_id, $title, $type, $title, $message, $reason);
    $result = $stmt->execute();
    
    if (!$result) {
        error_log("Failed to execute notification query: " . $stmt->error);
    }
    
    $stmt->close();
    return $result;
}

function notifyAllAdmins($conn, $type, $title, $message, $manga_id = null) {
    $adminQuery = "SELECT id FROM users WHERE is_admin = 1";
    $result = $conn->query($adminQuery);
    
    if (!$result) {
        error_log("Failed to fetch admin users: " . $conn->error);
        return false;
    }
    
    if ($result->num_rows === 0) {
        error_log("No admin users found in database");
        return false;
    }
    
    $success = true;
    while ($admin = $result->fetch_assoc()) {
        if (!createNotification($conn, $admin['id'], $type, $title, $message, $manga_id)) {
            $success = false;
            error_log("Failed to create notification for admin ID: " . $admin['id']);
        }
    }
    
    return $success;
}

function notifyUserAboutMangaStatus($conn, $user_id, $type, $manga_title, $message, $manga_id = null, $reason = null) {
    $title = $type === 'manga_approved' ? "Manga Approved: $manga_title" : "Manga Disapproved: $manga_title";
    return createNotification($conn, $user_id, $type, $title, $message, $manga_id, $reason);
}

function getNotifications($conn, $user_id) {
    $query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
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

function getUnreadNotificationsCount($conn, $user_id) {
    $query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Failed to prepare get unread count query: " . $conn->error);
        return 0;
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return (int)$row['count'];
}

function markNotificationAsRead($conn, $notification_id, $user_id) {
    $query = "UPDATE notifications SET is_read = 1 
              WHERE id = ? AND user_id = ?";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Failed to prepare mark notification query: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("ii", $notification_id, $user_id);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

// MODIFIED: Now marks all notifications as read instead of deleting them
function markAllNotificationsAsRead($conn, $user_id) {
    $query = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Failed to prepare mark all notifications as read query: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("i", $user_id);
    $result = $stmt->execute();
    
    if ($result) {
        $updatedCount = $stmt->affected_rows;
        error_log("Successfully marked $updatedCount notifications as read for user ID: $user_id");
    } else {
        error_log("Failed to mark notifications as read for user ID: $user_id - Error: " . $stmt->error);
    }
    
    $stmt->close();
    
    return $result;
}

// NEW FUNCTION: Delete a specific notification
function deleteNotification($conn, $notification_id, $user_id) {
    $query = "DELETE FROM notifications WHERE id = ? AND user_id = ?";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Failed to prepare delete notification query: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("ii", $notification_id, $user_id);
    $result = $stmt->execute();
    
    if ($result) {
        $deletedCount = $stmt->affected_rows;
        if ($deletedCount > 0) {
            error_log("Successfully deleted notification ID: $notification_id for user ID: $user_id");
        } else {
            error_log("No notification found to delete with ID: $notification_id for user ID: $user_id");
        }
    }
    
    $stmt->close();
    
    return $result;
}
?>