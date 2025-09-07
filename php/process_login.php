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
            header("Location: ../login");
            exit();
        }
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
        if ($stmt === false) {
            $_SESSION['login_error'] = 'Query preparation failed: ' . $conn->error;
            header("Location: ../login");
            exit();
        }
        
        if (!$stmt->bind_param("s", $username)) {
            $_SESSION['login_error'] = 'Parameter binding failed: ' . $stmt->error;
            header("Location: ../login");
            exit();
        }
        
        if (!$stmt->execute()) {
            $_SESSION['login_error'] = 'Query execution failed: ' . $stmt->error;
            header("Location: ../login");
            exit();
        }
        
        $result = $stmt->get_result();
        if ($result === false) {
            $_SESSION['login_error'] = 'Failed to get result: ' . $stmt->error;
            header("Location: ../login");
            exit();
        }
        
        if ($result->num_rows === 0) {
            $_SESSION['login_error'] = 'Invalid username.';
            header("Location: ../login");
            exit();
        }
        
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
        $hashed_password = $row['password'];

        $stmt->close();
        $stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
        if ($stmt === false) {
            $_SESSION['login_error'] = 'Admin check failed: ' . $conn->error;
            header("Location: ../login");
            exit();
        }
        
        if (!$stmt->bind_param("i", $user_id)) {
            $_SESSION['login_error'] = 'Admin parameter binding failed: ' . $stmt->error;
            header("Location: ../login");
            exit();
        }
        
        if (!$stmt->execute()) {
            $_SESSION['login_error'] = 'Admin check execution failed: ' . $stmt->error;
            header("Location: ../login");
            exit();
        }
        
        $admin_result = $stmt->get_result();
        $is_admin = ($admin_result && $admin_result->num_rows > 0) ? 1 : 0;
        if (!password_verify($password, $hashed_password)) {
            $_SESSION['login_error'] = 'Invalid password.';
            header("Location: ../login");
            exit();
        }
        
        $_SESSION['username'] = $username;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['is_admin'] = $is_admin;
        $_SESSION['logged_in'] = true;
        if (isset($_POST['remember_username'])) {
            setcookie('remembered_username', $username, time() + (86400 * 30), "/");
        } else {
            setcookie('remembered_username', '', time() - 3600, "/");
        }
        header("Location: ../");
        exit();
    }
?>