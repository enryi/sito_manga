<?php
// update_username.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "manga";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$user_id = $_SESSION['user_id'];
$new_username = trim($_POST['new_username'] ?? '');

// Validate username
if (empty($new_username)) {
    echo json_encode(['success' => false, 'message' => 'Username cannot be empty']);
    exit();
}

if (strlen($new_username) < 3 || strlen($new_username) > 20) {
    echo json_encode(['success' => false, 'message' => 'Username must be between 3-20 characters']);
    exit();
}

if (!preg_match('/^[a-zA-Z0-9]+$/', $new_username)) {
    echo json_encode(['success' => false, 'message' => 'Username can only contain letters and numbers']);
    exit();
}

// Check if username already exists (excluding current user)
$check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
$check_stmt->bind_param("si", $new_username, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Username already exists']);
    $check_stmt->close();
    $conn->close();
    exit();
}
$check_stmt->close();

// Update username
$update_stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
$update_stmt->bind_param("si", $new_username, $user_id);

if ($update_stmt->execute()) {
    $_SESSION['username'] = $new_username;
    echo json_encode(['success' => true, 'message' => 'Username updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update username']);
}

$update_stmt->close();
$conn->close();
?>