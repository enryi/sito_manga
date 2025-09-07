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

                    // Call notifyAllAdmins function from notification_functions.php
                    $notification_type = 'manga_pending';
                    $notification_title = $title; // manga title
                    $notification_message = "New manga '$title' needs approval";
                    
                    if (notifyAllAdmins($conn, $notification_type, $notification_title, $notification_message, $manga_id)) {
                        error_log("Notifications sent successfully for manga: $title");
                    } else {
                        error_log("Failed to send notifications for manga: $title");
                    }

                    $redirectPath = isset($_SESSION['current_path']) ? $_SESSION['current_path'] : 'localhost';
                    header("Location: $redirectPath");
                    exit();
                } else {
                    // If database insert fails, delete the uploaded image
                    unlink($uploadFile);
                    echo json_encode(['success' => false, 'message' => 'Failed to add manga.']);
                    echo "<script>console.error('Failed to add manga.');</script>";
                    header("Location: ../pending");
                }
                
                $stmt->close();
                $conn->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to upload image.']);
                echo "<script>console.error('Failed to upload image.');</script>";
                header("Location: ../pending");
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No image uploaded or upload error.']);
            echo "<script>console.error('No image uploaded or upload error.');</script>";
            header("Location: ../pending");
        }
    } else {
        echo "<script>console.error('Error: Invalid request.');</script>";
        header("Location: ../pending");
        exit();
    }
?>