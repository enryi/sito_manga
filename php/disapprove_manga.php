<?php
    require_once 'notification_functions.php';
    require_once 'session.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manga_id'])) {
        $manga_id = intval($_POST['manga_id']);
        $disapproval_reason = isset($_POST['reason']) ? trim($_POST['reason']) : 'No reason provided';

        $mangaQuery = "SELECT title, image_url, submitted_by FROM manga WHERE id = ?";
        $mangaStmt = $conn->prepare($mangaQuery);
        if (!$mangaStmt) {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare manga query.']);
            $conn->close();
            exit();
        }

        $mangaStmt->bind_param("i", $manga_id);
        $mangaStmt->execute();
        $mangaResult = $mangaStmt->get_result();
        $manga = $mangaResult->fetch_assoc();
        $mangaStmt->close();

        if ($manga) {
            $manga_title = $manga['title'];
            $submitted_by = $manga['submitted_by'];
            $image_url = $manga['image_url'];

            $stmt = $conn->prepare("DELETE FROM manga WHERE id = ?");
            if (!$stmt) {
                echo json_encode(['success' => false, 'message' => 'Failed to prepare delete query.']);
                $conn->close();
                exit();
            }

            $stmt->bind_param("i", $manga_id);
            
            if ($stmt->execute()) {
                if ($image_url) {
                    $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/enryi/' . $image_url;
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }

                if ($submitted_by) {
                    $disapproval_message = "Unfortunately, your manga '$manga_title' has been disapproved.";
                    if (notifyUserAboutMangaStatus($conn, $submitted_by, 'manga_disapproved', $manga_title, $disapproval_message, null, $disapproval_reason)) {
                        error_log("Disapproval notification sent to user ID: $submitted_by with reason: $disapproval_reason");
                    } else {
                        error_log("Failed to send disapproval notification to user ID: $submitted_by");
                    }
                }

                $deleteNotifQuery = "DELETE FROM notifications WHERE manga_id = ? AND type = 'manga_pending'";
                $deleteStmt = $conn->prepare($deleteNotifQuery);
                if ($deleteStmt) {
                    $deleteStmt->bind_param("i", $manga_id);
                    $deleteStmt->execute();
                    $deleteStmt->close();
                }

                echo json_encode(['success' => true, 'message' => 'Manga disapproved successfully.']);
                header("Location: ../pending");
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to disapprove manga.']);
                header("Location: ../pending");
            }
            
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Manga not found.']);
            header("Location: ../pending");
        }
        
        $conn->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        header("Location: ../pending");
    }
?>