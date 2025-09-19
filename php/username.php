<?php
    session_start();    
    $password_changed = false;
    $registration_success = false;
    $login_error = null;
    $username = isset($_COOKIE['remembered_username']) ? htmlspecialchars($_COOKIE['remembered_username']) : '';
    if (isset($_SESSION['password_changed']) && $_SESSION['password_changed'] === true) {
        $password_changed = true;
        unset($_SESSION['password_changed']);
    }
    if (isset($_SESSION['registration_success']) && $_SESSION['registration_success'] === true) {
        $registration_success = true;
        unset($_SESSION['registration_success']);
    }
    if (isset($_SESSION['login_error'])) {
        $login_error = $_SESSION['login_error'];
        unset($_SESSION['login_error']);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $servername = "localhost";
        $db_username = "root";
        $db_password = "";
        $dbname = "manga";
        $conn = new mysqli($servername, $db_username, $db_password, $dbname);
        if ($conn->connect_error) {
            echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
            exit();
        }
        $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid username.']);
            exit();
        }
        $row = $result->fetch_assoc();
        $hashed_password = $row['password'];
        if (!password_verify($password, $hashed_password)) {
            echo json_encode(['success' => false, 'message' => 'Invalid password.']);
            exit();
        }
        $_SESSION['username'] = $username;
        $_SESSION['logged_in'] = true;
        if (isset($_POST['remember_username'])) {
            setcookie('remembered_username', $username, time() + (86400 * 30), "/");
        } else {
            setcookie('remembered_username', '', time() - 3600, "/");
        }
        echo json_encode(['success' => true]);
        exit();
    }
?>