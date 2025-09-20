<?php
// save_preferences.php
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
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

try {
    // Create user_preferences table if it doesn't exist
    $createTableQuery = "
        CREATE TABLE IF NOT EXISTS user_preferences (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            preference_key VARCHAR(50) NOT NULL,
            preference_value TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_preference (user_id, preference_key),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
    $conn->query($createTableQuery);

    $conn->autocommit(false);

    // Clear existing preferences for this user
    $deleteStmt = $conn->prepare("DELETE FROM user_preferences WHERE user_id = ?");
    $deleteStmt->bind_param("i", $user_id);
    $deleteStmt->execute();
    $deleteStmt->close();

    // Insert new preferences
    $insertStmt = $conn->prepare("INSERT INTO user_preferences (user_id, preference_key, preference_value) VALUES (?, ?, ?)");
    
    foreach ($input as $key => $value) {
        $valueStr = is_bool($value) ? ($value ? '1' : '0') : (string)$value;
        $insertStmt->bind_param("iss", $user_id, $key, $valueStr);
        $insertStmt->execute();
    }
    
    $insertStmt->close();
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Preferences saved successfully']);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to save preferences: ' . $e->getMessage()]);
}

$conn->close();
?>