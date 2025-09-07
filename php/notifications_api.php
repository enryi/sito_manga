<?php
    session_start();
    require_once 'notification_functions.php';
    require_once 'db_connection.php';

    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Non autenticato']);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    switch ($_GET['action'] ?? '') {
        case 'get_count':
            $count = getUnreadNotificationsCount($conn, $user_id);
            echo json_encode(['success' => true, 'count' => $count]);
            break;
            
        case 'get_notifications':
            $notifications = getNotifications($conn, $user_id);
            $result = [];

            // If getNotifications returns an array
            foreach ($notifications as $row) {
                $result[] = [
                    'id' => $row['id'],
                    'type' => $row['type'],
                    'title' => $row['title'],
                    'message' => $row['message'],
                    'manga_title' => $row['manga_title'],
                    'manga_id' => $row['manga_id'],
                    'is_read' => $row['is_read'],
                    'created_at' => $row['created_at'],
                    'time_ago' => timeAgo($row['created_at'])
                ];
            }

            echo json_encode(['success' => true, 'notifications' => $result]);
            break;
            
        case 'mark_read':
            $notification_id = $_POST['notification_id'] ?? 0;
            $success = markNotificationAsRead($conn, $notification_id, $user_id);
            echo json_encode(['success' => $success]);
            break;
            
        case 'mark_all_read':
            $success = markAllNotificationsAsRead($conn, $user_id);
            echo json_encode(['success' => $success]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Azione non valida']);
    }

    function timeAgo($datetime) {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return 'ora';
        if ($time < 3600) return floor($time/60) . 'm fa';
        if ($time < 86400) return floor($time/3600) . 'h fa';
        if ($time < 2592000) return floor($time/86400) . 'd fa';
        if ($time < 31104000) return floor($time/2592000) . 'mesi fa';
        return floor($time/31104000) . 'anni fa';
    }
?>