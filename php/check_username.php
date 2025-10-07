<?php
    header('Content-Type: application/json');
    require_once 'session.php';

    try {
        $conn = new mysqli($servername, $username_db, $password_db, $dbname);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!isset($data['username']) || empty(trim($data['username']))) {
            echo json_encode(['success' => false, 'message' => 'Username is required']);
            exit;
        }

        $username = trim($data['username']);

        $stmt = $conn->prepare("SELECT username FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Username found']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Username not found']);
        }

        $stmt->close();
        $conn->close();

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
?>