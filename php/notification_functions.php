<?php
    function createNotification($conn, $user_id, $type, $manga_title, $message, $manga_id = null) {
        $query = "INSERT INTO notifications (
            user_id, 
            manga_id, 
            manga_title, 
            type, 
            message, 
            is_read, 
            created_at
        ) VALUES (?, ?, ?, ?, ?, 0, NOW())";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("Failed to prepare notification query: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("iisss", $user_id, $manga_id, $manga_title, $type, $message);
        $result = $stmt->execute();
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
        
        $success = true;
        while ($admin = $result->fetch_assoc()) {
            if (!createNotification($conn, $admin['id'], $type, $title, $message, $manga_id)) {
                $success = false;
                error_log("Failed to create notification for admin ID: " . $admin['id']);
            }
        }
        
        return $success;
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

    function markAllNotificationsAsRead($conn, $user_id) {
        $query = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("Failed to prepare mark all notifications query: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("i", $user_id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
?>