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
        $stmt = $conn->prepare("DELETE FROM manga WHERE id = ?");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare query.']);
            $conn->close();
            exit();
        }
        $stmt->bind_param("i", $manga_id);
        if ($stmt->execute()) {
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