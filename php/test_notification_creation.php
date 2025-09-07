<?php
session_start();
require_once 'notification_functions.php';

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "manga";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Test Notification Creation</h1>";

if (!isset($_SESSION['user_id'])) {
    die("<p style='color: red;'>You need to be logged in to test notifications!</p>");
}

$current_user_id = $_SESSION['user_id'];
echo "<p>Current user ID: $current_user_id</p>";

// Check if there are any manga with submitted_by set
$mangaQuery = "SELECT id, title, submitted_by FROM manga WHERE submitted_by IS NOT NULL";
$mangaResult = $conn->query($mangaQuery);

echo "<h3>Manga with submitted_by field:</h3>";
if ($mangaResult && $mangaResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Manga ID</th><th>Title</th><th>Submitted By</th></tr>";
    while ($manga = $mangaResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $manga['id'] . "</td>";
        echo "<td>" . htmlspecialchars($manga['title']) . "</td>";
        echo "<td>" . $manga['submitted_by'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>No manga found with submitted_by field set!</p>";
}

// Test creating a notification manually
if (isset($_GET['test']) && $_GET['test'] == 'create') {
    echo "<h3>Creating test notification...</h3>";
    
    $success = notifyUserAboutMangaStatus(
        $conn, 
        $current_user_id, 
        'manga_disapproved', 
        'Test Manga', 
        'This is a test disapproval notification', 
        999, 
        'Testing the notification system'
    );
    
    if ($success) {
        echo "<p style='color: green;'>✅ Test notification created successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create test notification!</p>";
    }
    
    // Check if notification was created
    $checkQuery = "SELECT * FROM notifications WHERE user_id = ? AND manga_title = 'Test Manga' ORDER BY created_at DESC LIMIT 1";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("i", $current_user_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        $notification = $result->fetch_assoc();
        echo "<h4>Created notification details:</h4>";
        echo "<pre>" . print_r($notification, true) . "</pre>";
    }
}

// Show current notifications for this user
$userNotifications = getNotifications($conn, $current_user_id);
echo "<h3>Current notifications for user $current_user_id:</h3>";

if (count($userNotifications) > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Type</th><th>Title</th><th>Message</th><th>Reason</th><th>Read</th><th>Created</th></tr>";
    foreach ($userNotifications as $notif) {
        echo "<tr>";
        echo "<td>" . $notif['id'] . "</td>";
        echo "<td>" . $notif['type'] . "</td>";
        echo "<td>" . htmlspecialchars($notif['title']) . "</td>";
        echo "<td>" . htmlspecialchars($notif['message']) . "</td>";
        echo "<td>" . htmlspecialchars($notif['reason'] ?? 'None') . "</td>";
        echo "<td>" . ($notif['is_read'] ? 'Yes' : 'No') . "</td>";
        echo "<td>" . $notif['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No notifications found for this user.</p>";
}

echo "<hr>";
echo "<p><a href='?test=create' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;'>Create Test Notification</a></p>";
echo "<p><a href='?' style='background: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;'>Refresh Page</a></p>";

$conn->close();
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { margin: 10px 0; width: 100%; }
    th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
    th { background-color: #f0f0f0; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style>