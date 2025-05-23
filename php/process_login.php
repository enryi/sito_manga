<?php
    session_start();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $servername = "localhost";
        $db_username = "root";
        $db_password = "";
        $dbname = "manga";
        $conn = new mysqli($servername, $db_username, $db_password, $dbname);
        if ($conn->connect_error) {
            $_SESSION['login_error'] = 'Database connection failed.';
            header("Location: https://enryi.23hosts.com");
            exit();
        }
        $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $_SESSION['login_error'] = 'Invalid username.';
            header("Location: https://enryi.23hosts.com");
            exit();
        }
        $row = $result->fetch_assoc();
        $hashed_password = $row['password'];
        if (!password_verify($password, $hashed_password)) {
            $_SESSION['login_error'] = 'Invalid password.';
            header("Location: https://enryi.23hosts.com");
            exit();
        }
        $_SESSION['username'] = $username;
        $_SESSION['logged_in'] = true;
        if (isset($_POST['remember_username'])) {
            setcookie('remembered_username', $username, time() + (86400 * 30), "/");
        } else {
            setcookie('remembered_username', '', time() - 3600, "/");
        }
        header("Location: https://enryi.23hosts.com");
        exit();
    }
?>