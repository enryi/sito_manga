<?php

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "manga";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    echo "<h2>Database Diagnostic</h2>";

    echo "<h3>1. Users Table Structure:</h3>";
    $result = $conn->query("DESCRIBE users");
    if ($result) {
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "Error: " . $conn->error;
    }

    echo "<h3>2. Current Users:</h3>";
    $result = $conn->query("SELECT id, username, is_admin FROM users");
    if ($result) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Username</th><th>Is Admin</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['username'] . "</td>";
            echo "<td>" . ($row['is_admin'] ?? 'Column not exists') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "Error checking users: " . $conn->error . "<br>";
        
        $result = $conn->query("SELECT id, username FROM users");
        if ($result) {
            echo "<p><strong>Note: is_admin column doesn't exist. Current users:</strong></p>";
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Username</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['username'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }

    echo "<h3>3. Admin Table:</h3>";
    $result = $conn->query("SELECT * FROM admin");
    if ($result) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>User ID</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['user_id'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "Error: " . $conn->error;
    }

    echo "<h3>4. Notifications Table Structure:</h3>";
    $result = $conn->query("DESCRIBE notifications");
    if ($result) {
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "Error: " . $conn->error . "<br>";
        echo "Notifications table might not exist.";
    }

    echo "<h3>5. Current Notifications:</h3>";
    $result = $conn->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 10");
    if ($result) {
        if ($result->num_rows > 0) {
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>User ID</th><th>Manga ID</th><th>Type</th><th>Title</th><th>Message</th><th>Read</th><th>Created</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['user_id'] . "</td>";
                echo "<td>" . $row['manga_id'] . "</td>";
                echo "<td>" . $row['type'] . "</td>";
                echo "<td>" . ($row['title'] ?? 'N/A') . "</td>";
                echo "<td>" . $row['message'] . "</td>";
                echo "<td>" . $row['is_read'] . "</td>";
                echo "<td>" . $row['created_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No notifications found.";
        }
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
?>