<?php
    session_start();
    if (!isset($_SESSION['logged_in']) || !isset($_SESSION['username'])) {
        header("Location: ../login");
        exit();
    }
    $nome_utente = $_SESSION['username'];
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "manga";
    $conn = @new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        echo '<p>Database connection failed: ' . htmlspecialchars($conn->connect_error) . '</p>';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $nome_utente);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $_SESSION['user_id'] = $row['id'];
            $user_id = $row['id'];
            $stmt_admin = $conn->prepare("SELECT * FROM admin WHERE user_id = ?");
            $stmt_admin->bind_param("i", $user_id);
            $stmt_admin->execute();
            $result_admin = $stmt_admin->get_result();

            $_SESSION['is_admin'] = ($result_admin && $result_admin->num_rows > 0);
        }
        $stmt->close();
        if (isset($stmt_admin)) $stmt_admin->close();
        $conn->close();
    }
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }   
?>