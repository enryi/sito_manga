<?php
// get_preferences.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
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

try {
    // Check if user_preferences table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'user_preferences'");
    
    if ($tableCheck->num_rows == 0) {
        // Table doesn't exist, return default preferences
        echo json_encode([
            'success' => true, 
            'preferences' => [
                'default_view' => 'list',
                'items_per_page' => '24',
                'default_sort' => 'title_asc',
                'public_list' => false,
                'show_scores' => true
            ]
        ]);
        exit();
    }

    $stmt = $conn->prepare("SELECT preference_key, preference_value FROM user_preferences WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $preferences = [
        // Default values
        'default_view' => 'list',
        'items_per_page' => '24',
        'default_sort' => 'title_asc',
        'public_list' => false,
        'show_scores' => true
    ];
    
    while ($row = $result->fetch_assoc()) {
        $key = $row['preference_key'];
        $value = $row['preference_value'];
        
        // Convert string values to appropriate types
        if ($value === '1' || $value === '0') {
            $preferences[$key] = $value === '1';
        } else {
            $preferences[$key] = $value;
        }
    }
    
    $stmt->close();
    
    echo json_encode(['success' => true, 'preferences' => $preferences]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to load preferences: ' . $e->getMessage()]);
}

$conn->close();
?>