<?php
    require_once 'session.php';

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        $_SESSION['registration_error'] = "Database connection failed.";
        header("Location: ../register");
        exit();
    }

    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if (empty(trim($user))) {
        $_SESSION['registration_error'] = "Username is required.";
        header("Location: ../register");
        exit();
    }

    if (empty(trim($pass))) {
        $_SESSION['registration_error'] = "Password is required.";
        header("Location: ../register");
        exit();
    }

    $user = trim($user);
    $_SESSION['registration_username'] = $user;

    $check_stmt = $conn->prepare("SELECT username FROM users WHERE username = ? LIMIT 1");
    $check_stmt->bind_param("s", $user);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $_SESSION['registration_error'] = "The username already exists. Please choose a different username.";
        $check_stmt->close();
        $conn->close();
        header("Location: ../register");
        exit();
    }

    $check_stmt->close();

    function is_password_pwned($password) {
        $hashed_password = sha1($password);
        $prefix = substr($hashed_password, 0, 5);
        $suffix = substr($hashed_password, 5);
        $url = "https://api.pwnedpasswords.com/range/" . $prefix;
        $response = @file_get_contents($url);

        if ($response === false) {
            return false;
        }
        $lines = explode("\n", $response);
        foreach ($lines as $line) {
            if (empty($line)) continue;
            $parts = explode(":", $line);
            if (count($parts) >= 2 && strcasecmp(trim($parts[0]), $suffix) === 0) {
                return true;
            }
        }
        return false;
    }

    if (is_password_pwned($pass)) {
        $_SESSION['registration_error'] = "The password you entered was found in a list of stolen passwords. Choose a different password.";
        header("Location: ../register");
        exit();
    }

    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $user, $hashed_password);

    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        
        $_SESSION['registration_success'] = true;
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $user;
        $_SESSION['user_id'] = $user_id;
        unset($_SESSION['registration_username']);
        header("Location: ../login?registered=1");
        $stmt->close();
        $conn->close();
        exit();
    } else {
        $_SESSION['registration_error'] = "Error during registration. Please try again.";
        header("Location: ../register");
        $stmt->close();
        $conn->close();
        exit();
    }
?>