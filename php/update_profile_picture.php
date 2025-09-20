<?php
// update_profile_picture.php
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

// Handle file upload
if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit();
}

$file = $_FILES['profile_picture'];

// Validate file type
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type']);
    exit();
}

// Validate file size (5MB max)
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 5MB']);
    exit();
}

// Create upload directory
$upload_dir = '../uploads/profiles/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generate unique filename
$file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('profile_' . $user_id . '_') . '.' . $file_extension;
$target_path = $upload_dir . $filename;

// Get current pfp to delete old one
$stmt = $conn->prepare("SELECT pfp FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$current_pfp = null;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $current_pfp = $row['pfp'];
}
$stmt->close();

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $target_path)) {
    // Update database
    $pfp_path = 'uploads/profiles/' . $filename;
    $update_stmt = $conn->prepare("UPDATE users SET pfp = ? WHERE id = ?");
    $update_stmt->bind_param("si", $pfp_path, $user_id);
    
    if ($update_stmt->execute()) {
        // Delete old profile picture if it exists
        if ($current_pfp && file_exists('../' . $current_pfp)) {
            unlink('../' . $current_pfp);
        }
        
        echo json_encode(['success' => true, 'message' => 'Profile picture updated successfully']);
    } else {
        // Delete uploaded file if database update fails
        unlink($target_path);
        echo json_encode(['success' => false, 'message' => 'Failed to update database']);
    }
    
    $update_stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
}

$conn->close();
?>