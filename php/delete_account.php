<?php
// delete_account.php
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
$password_confirmation = $_POST['password'] ?? '';

if (empty($password_confirmation)) {
    echo json_encode(['success' => false, 'message' => 'Password confirmation required']);
    exit();
}

// Verify password
$stmt = $conn->prepare("SELECT password, pfp FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

$user_data = $result->fetch_assoc();
$stmt->close();

if (!password_verify($password_confirmation, $user_data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Incorrect password']);
    exit();
}

try {
    // Start transaction
    $conn->autocommit(false);
    
    // Delete profile picture file
    if ($user_data['pfp'] && file_exists('../' . $user_data['pfp'])) {
        unlink('../' . $user_data['pfp']);
    }
    
    // Delete related data
    $tables = ['bookmarks', 'notifications'];
    foreach ($tables as $table) {
        // Check if table exists before trying to delete
        $table_check = $conn->query("SHOW TABLES LIKE '$table'");
        if ($table_check->num_rows > 0) {
            $stmt = $conn->prepare("DELETE FROM `$table` WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    // Delete manga uploads by user
    $manga_check = $conn->query("SHOW TABLES LIKE 'manga'");
    if ($manga_check->num_rows > 0) {
        $stmt = $conn->prepare("DELETE FROM manga WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Delete user account
    $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $delete_stmt->bind_param("i", $user_id);
    $delete_stmt->execute();
    $delete_stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    // Destroy session
    session_destroy();
    
    echo json_encode(['success' => true, 'message' => 'Account deleted successfully']);
    
} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to delete account: ' . $e->getMessage()]);
}

$conn->close();
?>