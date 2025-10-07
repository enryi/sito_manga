<?php
    require_once __DIR__ . '/session.php';

    if (isset($_SESSION['username'])) {
        $nome_utente = $_SESSION['username'];

        $stmt = $conn->prepare("SELECT id, is_admin FROM users WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $nome_utente);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();

                    $_SESSION['user_id']  = $row['id'];
                    $_SESSION['is_admin'] = (bool)$row['is_admin'];
                }
            }
            $stmt->close();
        } else {
            error_log("Query preparation failed in php/index.php: " . $conn->error);
        }
    }

    function getUnreadNotificationsCount($conn, $user_id) {
        if (!$conn || !$user_id) return 0;

        $stmt = $conn->prepare("SELECT COUNT(*) as count 
                                FROM notifications 
                                WHERE user_id = ? AND is_read = FALSE");
        if (!$stmt) return 0;

        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result) {
                $row = $result->fetch_assoc();
                return $row['count'];
            }
        }
        return 0;
    }

    if (isset($_SESSION['user_id'])) {
        $_SESSION['unread_notifications'] = getUnreadNotificationsCount($conn, $_SESSION['user_id']);
    }
?>