<?php
    session_start();
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "manga";

    $conn = @new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        $conn = null;
    } elseif (isset($_SESSION['username'])) {
        $nome_utente = $_SESSION['username'];
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        
        if ($stmt === false) {
            error_log("Query preparation failed in index.php: " . $conn->error);
        } else {
            $stmt->bind_param("s", $nome_utente);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $_SESSION['user_id'] = $row['id'];
                    $user_id = $row['id'];

                    $stmt_admin = $conn->prepare("SELECT 1 FROM admin WHERE user_id = ?");
                    if ($stmt_admin === false) {
                        error_log("Admin query preparation failed: " . $conn->error);
                    } else {
                        $stmt_admin->bind_param("i", $user_id);
                        if ($stmt_admin->execute()) {
                            $result_admin = $stmt_admin->get_result();
                            $_SESSION['is_admin'] = ($result_admin && $result_admin->num_rows > 0);
                        }
                        $stmt_admin->close();
                    }
                }
            }
            $stmt->close();
        }
    }

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    function getUnreadNotificationsCount($conn, $user_id) {
        if (!$conn || !$user_id) return 0;
        
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE");
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