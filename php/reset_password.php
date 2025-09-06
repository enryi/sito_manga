<?php
    session_start();
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "manga";
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $username = $_POST['username'];
    $new_password = $_POST['password'];
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET username = ?, password = ? WHERE username = ?");
    $stmt->bind_param("sss", $username, $hashed_password, $username);
    if ($stmt->execute()) {
        $_SESSION['password_changed'] = true;
        header("Location: redirect.php");
        exit();
    } else {
        echo "Error in the update: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
?>