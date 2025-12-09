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
    $password_confirmation = $_POST['password'] ?? '';

    if (empty($password_confirmation)) {
        echo json_encode(['success' => false, 'message' => 'Password confirmation required']);
        exit();
    }

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
        $conn->autocommit(false);
        
        if ($user_data['pfp'] && file_exists('../' . $user_data['pfp'])) {
            unlink('../' . $user_data['pfp']);
        }
        
        $tables = ['bookmarks', 'notifications'];
        foreach ($tables as $table) {
            $table_check = $conn->query("SHOW TABLES LIKE '$table'");
            if ($table_check->num_rows > 0) {
                $stmt = $conn->prepare("DELETE FROM `$table` WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $stmt->close();
            }
        }
        
        $manga_check = $conn->query("SHOW TABLES LIKE 'manga'");
        if ($manga_check->num_rows > 0) {
            $stmt = $conn->prepare("DELETE FROM manga WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
        }
        
        $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $delete_stmt->bind_param("i", $user_id);
        $delete_stmt->execute();
        $delete_stmt->close();
        
        $conn->commit();
        
        session_destroy();
        
        echo json_encode(['success' => true, 'message' => 'Account deleted successfully']);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to delete account: ' . $e->getMessage()]);
    }
?>