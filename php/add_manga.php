<?php
    session_start();
    require_once 'notification_functions.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['manga-title'];
        $description = $_POST['manga-description'];
        $author = $_POST['manga-author'];
        $type = $_POST['manga-type'];
        $genre = $_POST['manga-genre'];
        
        // Get the user ID who is submitting the manga
        $submitted_by = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
        
        // Debug logging
        error_log("DEBUG: Session user_id = " . var_export($_SESSION['user_id'] ?? 'NOT SET', true));
        error_log("DEBUG: submitted_by = " . var_export($submitted_by, true));
        
        if ($submitted_by === null) {
            error_log("ERROR: No user_id in session - user might not be logged in");
        }
        
        if (!in_array($type, ['Manga', 'Manwha', 'Manhua'])) {
            die("Invalid type: $type");
        }
        
        if (isset($_FILES['manga-image']) && $_FILES['manga-image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['manga-image'];
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');

            if (!in_array($file_extension, $allowed_extensions)) {
                die(json_encode(['success' => false, 'message' => 'Invalid file type.']));
            }

            // Generate unique filename
            $timestamp = time();
            $random_string = bin2hex(random_bytes(8));
            $new_filename = $timestamp . '_' . $random_string . '.' . $file_extension;
            
            $uploadDir = '../uploads/manga/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $uploadFile = $uploadDir . $new_filename;
            $imageUrl = 'uploads/manga/' . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "manga";
                $conn = new mysqli($servername, $username, $password, $dbname);
                
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }
                
                // Debug: Show what we're about to insert
                error_log("DEBUG: About to insert manga with submitted_by = " . var_export($submitted_by, true));
                
                // Fixed SQL query - remove the user_id column since it's not being used
                $stmt = $conn->prepare("INSERT INTO manga (title, image_url, description, author, type, genre, approved, submitted_by) VALUES (?, ?, ?, ?, ?, ?, 0, ?)");
                
                if (!$stmt) {
                    error_log("ERROR: Failed to prepare statement: " . $conn->error);
                    die("Database error occurred");
                }
                
                // Bind parameters correctly
                $stmt->bind_param("ssssssi", $title, $imageUrl, $description, $author, $type, $genre, $submitted_by);
                
                if ($stmt->execute()) {
                    $manga_id = $conn->insert_id;
                    error_log("SUCCESS: Manga inserted with ID: $manga_id, submitted_by: $submitted_by");

                    // Verify the insertion
                    $verifyStmt = $conn->prepare("SELECT submitted_by FROM manga WHERE id = ?");
                    $verifyStmt->bind_param("i", $manga_id);
                    $verifyStmt->execute();
                    $verifyResult = $verifyStmt->get_result();
                    $verifyRow = $verifyResult->fetch_assoc();
                    error_log("VERIFY: submitted_by in database = " . var_export($verifyRow['submitted_by'], true));
                    $verifyStmt->close();

                    // Check if we have admin users first
                    $adminCheckQuery = "SELECT COUNT(*) as admin_count FROM users WHERE is_admin = 1";
                    $adminCheckResult = $conn->query($adminCheckQuery);
                    $adminCount = $adminCheckResult->fetch_assoc()['admin_count'];
                    error_log("Number of admin users found: $adminCount");

                    if ($adminCount > 0) {
                        // Call notifyAllAdmins function from notification_functions.php
                        $notification_type = 'manga_pending';
                        $notification_title = $title; // manga title
                        $notification_message = "New manga '$title' needs approval";
                        
                        error_log("Attempting to send notifications for manga: $title");
                        
                        if (notifyAllAdmins($conn, $notification_type, $notification_title, $notification_message, $manga_id)) {
                            error_log("Notifications sent successfully for manga: $title");
                        } else {
                            error_log("Failed to send notifications for manga: $title");
                        }
                    } else {
                        error_log("No admin users found - notifications not sent");
                    }

                    $redirectPath = isset($_SESSION['current_path']) ? $_SESSION['current_path'] : '/';
                    header("Location: $redirectPath");
                    exit();
                } else {
                    // If database insert fails, delete the uploaded image
                    unlink($uploadFile);
                    error_log("Failed to insert manga: " . $stmt->error);
                    echo json_encode(['success' => false, 'message' => 'Failed to add manga.']);
                    header("Location: ../pending");
                }
                
                $stmt->close();
                $conn->close();
            } else {
                error_log("Failed to move uploaded file");
                echo json_encode(['success' => false, 'message' => 'Failed to upload image.']);
                header("Location: ../pending");
            }
        } else {
            error_log("No image uploaded or upload error. Error code: " . ($_FILES['manga-image']['error'] ?? 'No file'));
            echo json_encode(['success' => false, 'message' => 'No image uploaded or upload error.']);
            header("Location: ../pending");
        }
    } else {
        error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
        echo "<script>console.error('Error: Invalid request.');</script>";
        header("Location: ../pending");
        exit();
    }
?>