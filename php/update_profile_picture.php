<?php
    require_once 'session.php';
    require_once 'image_security.php';
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit();
    }

    $user_id = $_SESSION['user_id'];

    if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
        exit();
    }

    $file = $_FILES['profile_picture'];

    $upload_dir = '../uploads/profiles/';
    
    if (!ensureSecureUploadDirectory($upload_dir)) {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare upload directory']);
        exit();
    }
    
    $validation_result = validateAndSanitizeImage($file['tmp_name']);
    
    if (!$validation_result['success']) {
        echo json_encode(['success' => false, 'message' => $validation_result['message']]);
        exit();
    }

    $filename = generateSecureFilename($validation_result['extension'], 'profile_' . $user_id . '_');
    $target_path = $upload_dir . $filename;

    $stmt = $conn->prepare("SELECT pfp FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_pfp = null;
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $current_pfp = $row['pfp'];
    }
    $stmt->close();

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        $pfp_path = 'uploads/profiles/' . $filename;
        $update_stmt = $conn->prepare("UPDATE users SET pfp = ? WHERE id = ?");
        $update_stmt->bind_param("si", $pfp_path, $user_id);
        
        if ($update_stmt->execute()) {
            if ($current_pfp) {
                deleteImageSafely('../' . $current_pfp);
            }
            
            echo json_encode(['success' => true, 'message' => 'Profile picture updated successfully']);
        } else {
            deleteImageSafely($target_path);
            echo json_encode(['success' => false, 'message' => 'Failed to update database']);
        }
        
        $update_stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
    }

    $conn->close();
?>