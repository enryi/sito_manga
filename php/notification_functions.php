<?php
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