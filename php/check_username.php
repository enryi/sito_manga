<?php
header('Content-Type: application/json');
session_start();

$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "manga";

try {
    $conn = new mysqli($servername, $username_db, $password_db, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!isset($data['username']) || empty(trim($data['username']))) {
        echo json_encode(['success' => false, 'message' => 'Username is required']);
        exit;
    }

    $username = trim($data['username']);

    // Prepare statement to check if username exists
    $stmt = $conn->prepare("SELECT username FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Username exists
        echo json_encode(['success' => true, 'message' => 'Username found']);
    } else {
        // Username doesn't exist
        echo json_encode(['success' => false, 'message' => 'Username not found']);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>