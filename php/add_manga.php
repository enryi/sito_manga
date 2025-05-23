<?php
    session_start();
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
            $uploadDir = '../uploads/manga/';
            $uploadFile = $uploadDir . basename($_FILES['manga-image']['name']);
            $imageUrl = 'uploads/manga/' . basename($_FILES['manga-image']['name']);
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            if (move_uploaded_file($_FILES['manga-image']['tmp_name'], $uploadFile)) {
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "manga";
                $conn = new mysqli($servername, $username, $password, $dbname);
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }
                $stmt = $conn->prepare("INSERT INTO manga (title, image_url, description, author, type, genre) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $title, $imageUrl, $description, $author, $type, $genre);
                if ($stmt->execute()) {
                    $redirectPath = isset($_SESSION['current_path']) ? $_SESSION['current_path'] : 'https://enryi.23hosts.com';
                    header("Location: $redirectPath");
                    exit();
                } else {
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