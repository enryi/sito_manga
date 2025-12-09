<?php
    header('Content-Type: application/json');
    require_once 'session.php';
    require_once 'image_security.php';

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
        exit();
    }

    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if (empty($user) || empty($pass)) {
        echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
        exit();
    }

    $check_stmt = $conn->prepare("SELECT username FROM users WHERE username = ? LIMIT 1");
    $check_stmt->bind_param("s", $user);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'The username already exists. Please choose a different username.']);
        $check_stmt->close();
        $conn->close();
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
        echo json_encode(['success' => false, 'message' => 'The password you entered was found in a list of stolen passwords. Choose a different password.']);
        exit();
    }

    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

    $pfp_path = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/profiles/';
        
        if (!ensureSecureUploadDirectory($upload_dir)) {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare upload directory.']);
            exit();
        }
        
        $validation_result = validateAndSanitizeImage($_FILES['profile_picture']['tmp_name']);
        
        if (!$validation_result['success']) {
            echo json_encode(['success' => false, 'message' => $validation_result['message']]);
            exit();
        }
        
        $filename = generateSecureFilename($validation_result['extension'], 'profile_');
        $target_path = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_path)) {
            $pfp_path = 'uploads/profiles/' . $filename;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save profile picture.']);
            exit();
        }
    }

    $stmt = $conn->prepare("INSERT INTO users (username, password, pfp) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $user, $hashed_password, $pfp_path);

    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        
        $_SESSION['registration_success'] = true;
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $user;
        $_SESSION['user_id'] = $user_id;
        
        echo json_encode(['success' => true, 'message' => 'Registration completed successfully!']);
        
        $stmt->close();
        $conn->close();
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error during registration: ' . $stmt->error]);
        
        if ($pfp_path && file_exists('../' . $pfp_path)) {
            deleteImageSafely('../' . $pfp_path);
        }
        
        $stmt->close();
        $conn->close();
        exit();
    }
?>