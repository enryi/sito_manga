<?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manga_id'])) {
        $manga_id = intval($_POST['manga_id']);
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "manga";
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die(json_encode(['success' => false, 'message' => 'Database connection failed.']));
        }

        // First get the image path before deleting the manga
        $imageQuery = "SELECT image_url FROM manga WHERE id = ?";
        $imageStmt = $conn->prepare($imageQuery);
        if (!$imageStmt) {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare image query.']);
            $conn->close();
            exit();
        }

        $imageStmt->bind_param("i", $manga_id);
        $imageStmt->execute();
        $imageResult = $imageStmt->get_result();
        $manga = $imageResult->fetch_assoc();
        $imageStmt->close();

        // Delete the manga from database
        $stmt = $conn->prepare("DELETE FROM manga WHERE id = ?");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare delete query.']);
            $conn->close();
            exit();
        }

        $stmt->bind_param("i", $manga_id);
        
        if ($stmt->execute()) {
            // Delete the image file if it exists
            if ($manga && isset($manga['image_url'])) {
                $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/enryi/' . $manga['image_url'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            // Delete related notifications
            $notifStmt = $conn->prepare("DELETE FROM notifications WHERE manga_id = ?");
            if ($notifStmt) {
                $notifStmt->bind_param("i", $manga_id);
                $notifStmt->execute();
                $notifStmt->close();
            }

            echo json_encode(['success' => true, 'message' => 'Manga disapproved and deleted successfully.']);
            header("Location: ../pending");
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to disapprove manga.']);
            echo "<script>console.error('Failed to disapprove manga.');</script>";
            header("Location: ../pending");
        }
        
        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        echo "<script>console.error('Error: Invalid request.');</script>";
        header("Location: ../pending");
    }
?>