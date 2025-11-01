<?php
    require_once 'notification_functions.php';
    require_once 'session.php';
    require_once 'image_security.php';

    function redirectWithNotification($status, $message, $redirectPath = null) {
        if ($redirectPath === null) {
            $redirectPath = isset($_SESSION['current_path']) ? $_SESSION['current_path'] : '/';
        }
        
        $encodedMessage = urlencode($message);
        $separator = strpos($redirectPath, '?') !== false ? '&' : '?';
        $redirectUrl = $redirectPath . $separator . "status=" . $status . "&message=" . $encodedMessage;
        
        header("Location: " . $redirectUrl);
        exit();
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $submitted_by = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
        
        if ($submitted_by === null) {
            error_log("ERROR: No user_id in session - user might not be logged in");
            redirectWithNotification('error', 'You must be logged in to upload manga.');
        }
        
        $required_fields = ['manga-title', 'manga-description', 'manga-author', 'manga-type', 'manga-genre'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                redirectWithNotification('error', 'Please fill in all required fields.');
            }
        }
        
        $title = trim($_POST['manga-title']);
        $description = trim($_POST['manga-description']);
        $author = trim($_POST['manga-author']);
        $type = $_POST['manga-type'];
        $genre = trim($_POST['manga-genre']);
        
        if (strlen($title) > 255) {
            redirectWithNotification('error', 'Title is too long. Maximum 255 characters allowed.');
        }
        if (strlen($description) > 1000) {
            redirectWithNotification('error', 'Description is too long. Maximum 1000 characters allowed.');
        }
        if (strlen($author) > 255) {
            redirectWithNotification('error', 'Author name is too long. Maximum 255 characters allowed.');
        }
        if (strlen($genre) > 255) {
            redirectWithNotification('error', 'Genre is too long. Maximum 255 characters allowed.');
        }
        
        if (!in_array($type, ['Manga', 'Manwha', 'Manhua'])) {
            redirectWithNotification('error', 'Invalid manga type selected.');
        }
        
        $checkTitleStmt = $conn->prepare("SELECT id FROM manga WHERE title = ?");
        $checkTitleStmt->bind_param("s", $title);
        $checkTitleStmt->execute();
        $titleResult = $checkTitleStmt->get_result();
        
        if ($titleResult->num_rows > 0) {
            $checkTitleStmt->close();
            $conn->close();
            redirectWithNotification('error', 'A manga with this title already exists.');
        }
        $checkTitleStmt->close();
        
        if (!isset($_FILES['manga-image'])) {
            $conn->close();
            redirectWithNotification('error', 'No image file was uploaded.');
        }
        
        $file = $_FILES['manga-image'];
        
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                $conn->close();
                redirectWithNotification('error', 'No image file was uploaded.');
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $conn->close();
                redirectWithNotification('error', 'The uploaded file is too large. Maximum file size is 5MB.');
                break;
            case UPLOAD_ERR_PARTIAL:
                $conn->close();
                redirectWithNotification('error', 'The file was only partially uploaded. Please try again.');
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $conn->close();
                redirectWithNotification('error', 'Server error: Missing temporary folder.');
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $conn->close();
                redirectWithNotification('error', 'Server error: Failed to write file to disk.');
                break;
            case UPLOAD_ERR_EXTENSION:
                $conn->close();
                redirectWithNotification('error', 'Server error: File upload stopped by extension.');
                break;
            default:
                $conn->close();
                redirectWithNotification('error', 'Unknown upload error occurred.');
                break;
        }
        
        $uploadDir = '../uploads/manga/';
        
        if (!ensureSecureUploadDirectory($uploadDir)) {
            $conn->close();
            redirectWithNotification('error', 'Failed to prepare upload directory.');
        }
        
        $validation_result = validateAndSanitizeImage(
            $file['tmp_name'],
            ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'],
            5 * 1024 * 1024
        );
        
        if (!$validation_result['success']) {
            $conn->close();
            error_log("Image validation failed: " . $validation_result['message']);
            redirectWithNotification('error', $validation_result['message']);
        }
        
        if ($validation_result['width'] > 2000 || $validation_result['height'] > 3000) {
            $conn->close();
            redirectWithNotification('error', 'Image dimensions too large. Maximum: 2000x3000 pixels.');
        }
        
        if ($validation_result['width'] < 100 || $validation_result['height'] < 100) {
            $conn->close();
            redirectWithNotification('error', 'Image dimensions too small. Minimum: 100x100 pixels.');
        }
        
        $filename = generateSecureFilename($validation_result['extension'], 'manga_');
        $uploadFile = $uploadDir . $filename;
        $imageUrl = 'uploads/manga/' . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $uploadFile)) {
            $conn->close();
            error_log("Failed to move uploaded file from " . $file['tmp_name'] . " to " . $uploadFile);
            redirectWithNotification('error', 'Failed to save uploaded image.');
        }
        
        $stmt = $conn->prepare("INSERT INTO manga (title, image_url, description, author, type, genre, approved, submitted_by) VALUES (?, ?, ?, ?, ?, ?, 0, ?)");
        
        if (!$stmt) {
            deleteImageSafely($uploadFile);
            $conn->close();
            error_log("ERROR: Failed to prepare statement: " . $conn->error);
            redirectWithNotification('error', 'Database error occurred. Please try again.');
        }
        
        $stmt->bind_param("ssssssi", $title, $imageUrl, $description, $author, $type, $genre, $submitted_by);
        
        if (!$stmt->execute()) {
            deleteImageSafely($uploadFile);
            $stmt->close();
            $conn->close();
            error_log("Failed to insert manga: " . $stmt->error);
            redirectWithNotification('error', 'Failed to save manga information. Please try again.');
        }
        
        $manga_id = $conn->insert_id;
        error_log("SUCCESS: Manga inserted with ID: $manga_id, submitted_by: $submitted_by");

        $adminCheckQuery = "SELECT COUNT(*) as admin_count FROM users WHERE is_admin = 1";
        $adminCheckResult = $conn->query($adminCheckQuery);
        $adminCount = $adminCheckResult->fetch_assoc()['admin_count'];
        
        if ($adminCount > 0) {
            $notification_type = 'manga_pending';
            $notification_title = $title;
            $notification_message = "New manga '$title' needs approval";
            
            if (notifyAllAdmins($conn, $notification_type, $notification_title, $notification_message, $manga_id)) {
                error_log("Notifications sent successfully for manga: $title");
            } else {
                error_log("Failed to send notifications for manga: $title");
            }
        } else {
            error_log("No admin users found - notifications not sent");
        }

        $stmt->close();
        $conn->close();
        
        redirectWithNotification('success', "Manga '$title' has been uploaded successfully! It's now pending approval by administrators.");
        
    } else {
        error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
        redirectWithNotification('error', 'Invalid request method.');
    }
?>