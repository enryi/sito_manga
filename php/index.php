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
        // Modified query to get both id and is_admin in one query
        $stmt = $conn->prepare("SELECT id, is_admin FROM users WHERE username = ?");
        
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

                    // Use the is_admin column from users table instead of querying admin table
                    $_SESSION['is_admin'] = (bool)$row['is_admin'];
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