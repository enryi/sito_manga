<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "manga";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'is_admin'");
    if ($result->num_rows == 0) {
        $sql = "ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0";
        if ($conn->query($sql) === TRUE) {
            echo "is_admin's column added successfully\n";
        } else {
            echo "Error adding the column: " . $conn->error . "\n";
        }
    } else {
        echo "is_admin's column already exist\n";
    }

    $sql = "UPDATE users u 
            INNER JOIN admin a ON u.id = a.user_id 
            SET u.is_admin = 1";
    if ($conn->query($sql) === TRUE) {
        echo "Admin's permission updated successfully\n";
    } else {
        echo "Error during the permission's update: " . $conn->error . "\n";
    }

    $conn->close();
    echo "Migration completed\n";
?>
