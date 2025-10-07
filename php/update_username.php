<?php
    header('Content-Type: application/json');

    require_once 'session.php';

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $new_username = trim($_POST['new_username'] ?? '');

    if (empty($new_username)) {
        echo json_encode(['success' => false, 'message' => 'Username cannot be empty']);
        exit();
    }

    if (strlen($new_username) < 3 || strlen($new_username) > 20) {
        echo json_encode(['success' => false, 'message' => 'Username must be between 3-20 characters']);
        exit();
    }

    if (!preg_match('/^[a-zA-Z0-9]+$/', $new_username)) {
        echo json_encode(['success' => false, 'message' => 'Username can only contain letters and numbers']);
        exit();
    }

    $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    if (!$check_stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit();
    }

    $check_stmt->bind_param("si", $new_username, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        $check_stmt->close();
        exit();
    }
    $check_stmt->close();

    $update_stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
    if (!$update_stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit();
    }

    $update_stmt->bind_param("si", $new_username, $user_id);

    if ($update_stmt->execute()) {
        $_SESSION['username'] = $new_username;
        echo json_encode(['success' => true, 'message' => 'Username updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update username']);
    }

    $update_stmt->close();
?>