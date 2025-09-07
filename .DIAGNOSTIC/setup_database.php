<?php
// Save this as setup_database.php and run it once to fix your database

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "manga";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Database Setup</h2>";

// 1. Add is_admin column to users table if it doesn't exist
echo "<h3>1. Adding is_admin column to users table...</h3>";
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'is_admin'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0";
    if ($conn->query($sql) === TRUE) {
        echo "✓ is_admin column added successfully<br>";
    } else {
        echo "✗ Error adding is_admin column: " . $conn->error . "<br>";
    }
} else {
    echo "✓ is_admin column already exists<br>";
}

// 2. Drop and recreate notifications table with correct structure
echo "<h3>2. Setting up notifications table...</h3>";
$conn->query("DROP TABLE IF EXISTS notifications");

$sql = "CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    manga_id INT,
    manga_title VARCHAR(255),
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (manga_id) REFERENCES manga(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created_at (created_at)
)";

if ($conn->query($sql) === TRUE) {
    echo "✓ Notifications table created successfully<br>";
} else {
    echo "✗ Error creating notifications table: " . $conn->error . "<br>";
}

// 3. Set up admin users
echo "<h3>3. Setting up admin users...</h3>";

// First, show current users
$result = $conn->query("SELECT id, username FROM users");
if ($result && $result->num_rows > 0) {
    echo "Current users in database:<br>";
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>ID: " . $row['id'] . ", Username: " . $row['username'] . "</li>";
    }
    echo "</ul>";
    
    // You can modify this to set your username as admin
    // Replace 'admin' with your actual username
    $admin_username = 'admin'; // CHANGE THIS TO YOUR USERNAME
    
    $update_sql = "UPDATE users SET is_admin = 1 WHERE username = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("s", $admin_username);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "✓ User '$admin_username' set as admin<br>";
        } else {
            echo "⚠ User '$admin_username' not found or already admin<br>";
        }
    } else {
        echo "✗ Error setting admin: " . $stmt->error . "<br>";
    }
    
    // Also update the admin table
    $user_id_result = $conn->query("SELECT id FROM users WHERE username = '$admin_username'");
    if ($user_id_result && $user_id_result->num_rows > 0) {
        $user_row = $user_id_result->fetch_assoc();
        $user_id = $user_row['id'];
        
        $admin_insert_sql = "INSERT IGNORE INTO admin (user_id) VALUES (?)";
        $stmt2 = $conn->prepare($admin_insert_sql);
        $stmt2->bind_param("i", $user_id);
        
        if ($stmt2->execute()) {
            echo "✓ Admin table entry created<br>";
        } else {
            echo "✗ Error creating admin table entry: " . $stmt2->error . "<br>";
        }
    }
} else {
    echo "No users found in database. Please create a user first.<br>";
}

// 4. Test notification creation
echo "<h3>4. Testing notification system...</h3>";
$test_result = $conn->query("SELECT id FROM users WHERE is_admin = 1 LIMIT 1");
if ($test_result && $test_result->num_rows > 0) {
    $admin_user = $test_result->fetch_assoc();
    $admin_id = $admin_user['id'];
    
    $test_sql = "INSERT INTO notifications (user_id, type, title, message) VALUES (?, 'test', 'Test Notification', 'This is a test notification')";
    $stmt = $conn->prepare($test_sql);
    $stmt->bind_param("i", $admin_id);
    
    if ($stmt->execute()) {
        echo "✓ Test notification created successfully<br>";
        
        // Clean up the test notification
        $conn->query("DELETE FROM notifications WHERE type = 'test'");
        echo "✓ Test notification cleaned up<br>";
    } else {
        echo "✗ Error creating test notification: " . $stmt->error . "<br>";
    }
} else {
    echo "✗ No admin users found to test with<br>";
}

echo "<h3>Setup Complete!</h3>";
echo "<p>You can now try uploading a manga to test the notification system.</p>";

$conn->close();
?>