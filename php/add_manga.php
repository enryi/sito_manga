<?php
    session_start();
    require_once 'notification_functions.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['manga-title'];
        $description = $_POST['manga-description'];
        $author = $_POST['manga-author'];
        $type = $_POST['manga-type'];
        $genre = $_POST['manga-genre'];
        
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
                
                $stmt = $conn->prepare("INSERT INTO manga (title, image_url, description, author, type, genre, approved) VALUES (?, ?, ?, ?, ?, ?, 0)");
                $stmt->bind_param("ssssss", $title, $imageUrl, $description, $author, $type, $genre);
                
                if ($stmt->execute()) {
                    $manga_id = $conn->insert_id;
                    error_log("Manga inserted successfully with ID: $manga_id");

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
                            
                            // Verify notifications were created
                            $verifyQuery = "SELECT COUNT(*) as notification_count FROM notifications WHERE manga_id = ?";
                            $verifyStmt = $conn->prepare($verifyQuery);
                            if ($verifyStmt) {
                                $verifyStmt->bind_param("i", $manga_id);
                                $verifyStmt->execute();
                                $verifyResult = $verifyStmt->get_result();
                                if ($verifyResult) {
                                    $verifyRow = $verifyResult->fetch_assoc();
                                    $notificationCount = $verifyRow['notification_count'];
                                    error_log("Notifications created in database: $notificationCount");
                                } else {
                                    error_log("Failed to get verification result: " . $verifyStmt->error);
                                }
                                $verifyStmt->close();
                            } else {
                                error_log("Failed to prepare verification query: " . $conn->error);
                            }
                        } else {
                            error_log("Failed to send notifications for manga: $title");
                        }
                    } else {
                        error_log("No admin users found - notifications not sent");
                    }

                    $redirectPath = isset($_SESSION['current_path']) ? $_SESSION['current_path'] : 'localhost';
                    header("Location: $redirectPath");
                    exit();
                } else {
                    // If database insert fails, delete the uploaded image
                    unlink($uploadFile);
                    error_log("Failed to insert manga: " . $stmt->error);
                    echo json_encode(['success' => false, 'message' => 'Failed to add manga.']);
                    echo "<script>console.error('Failed to add manga.');</script>";
                    header("Location: ../pending");
                }
                
                $stmt->close();
                $conn->close();
            } else {
                error_log("Failed to move uploaded file");
                echo json_encode(['success' => false, 'message' => 'Failed to upload image.']);
                echo "<script>console.error('Failed to upload image.');</script>";
                header("Location: ../pending");
            }
        } else {
            error_log("No image uploaded or upload error. Error code: " . ($_FILES['manga-image']['error'] ?? 'No file'));
            echo json_encode(['success' => false, 'message' => 'No image uploaded or upload error.']);
            echo "<script>console.error('No image uploaded or upload error.');</script>";
            header("Location: ../pending");
        }
    } else {
        error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
        echo "<script>console.error('Error: Invalid request.');</script>";
        header("Location: ../pending");
        exit();
    }
?>