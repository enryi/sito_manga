<?php
session_start();

$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "manga";

try {
    $conn = new mysqli($servername, $username_db, $password_db, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Check if required POST data exists
    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        $_SESSION['password_reset_error'] = "Missing required fields";
        header("Location: ../forgot_password");
        exit();
    }

    $username = trim($_POST['username']);
    $new_password = trim($_POST['password']);

    // Validate inputs
    if (empty($username)) {
        $_SESSION['password_reset_error'] = "Username is required";
        header("Location: ../forgot_password");
        exit();
    }

    if (empty($new_password)) {
        $_SESSION['password_reset_error'] = "Password is required";
        header("Location: ../forgot_password");
        exit();
    }

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update ONLY the password for the given username
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
    $stmt->bind_param("ss", $hashed_password, $username);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['password_changed'] = true;
            header("Location: ../login?password_reset=1");
            exit();
        } else {
            $_SESSION['password_reset_error'] = "Username not found or no changes made";
            header("Location: ../forgot_password");
            exit();
        }
    } else {
        $_SESSION['password_reset_error'] = "Error updating password: " . $stmt->error;
        header("Location: ../forgot_password");
        exit();
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    $_SESSION['password_reset_error'] = "Database error occurred";
    error_log("Reset password error: " . $e->getMessage());
    header("Location: ../forgot_password");
    exit();
}
?>