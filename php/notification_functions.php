<?php
    function createNotification($conn, $user_id, $type, $title, $message, $manga_id = null) {
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, manga_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $user_id, $type, $title, $message, $manga_id);
        return $stmt->execute();
    }

    function notifyAllAdmins($conn, $type, $title, $message, $manga_id = null) {
        $stmt = $conn->prepare("SELECT user_id FROM admin");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $success = true;
        while ($row = $result->fetch_assoc()) {
            if (!createNotification($conn, $row['user_id'], $type, $title, $message, $manga_id)) {
                $success = false;
            }
        }
        
        return $success;
    }

    function getUnreadNotificationsCount($conn, $user_id) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    function getNotifications($conn, $user_id, $limit = 10) {
        $stmt = $conn->prepare("SELECT n.*, m.title as manga_title FROM notifications n LEFT JOIN manga m ON n.manga_id = m.id WHERE n.user_id = ? ORDER BY n.created_at DESC LIMIT ?");
        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        return $stmt->get_result();
    }

    function markNotificationAsRead($conn, $notification_id, $user_id) {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notification_id, $user_id);
        return $stmt->execute();
    }

    function markAllNotificationsAsRead($conn, $user_id) {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }
?>