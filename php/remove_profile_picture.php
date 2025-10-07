<?php
    require_once 'session.php';
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit();
    }

    $user_id = $_SESSION['user_id'];

    try {
        $conn->begin_transaction();
        
        $stmt = $conn->prepare("SELECT pfp FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('User not found');
        }
        
        $row = $result->fetch_assoc();
        $current_pfp = $row['pfp'];
        $stmt->close();
        
        if (empty($current_pfp)) {
            echo json_encode(['success' => true, 'message' => 'No profile picture to remove']);
            $conn->close();
            exit();
        }
        
        $file_path = '../' . $current_pfp;
        if (!file_exists($file_path)) {
            $update_stmt = $conn->prepare("UPDATE users SET pfp = NULL WHERE id = ?");
            $update_stmt->bind_param("i", $user_id);
            
            if (!$update_stmt->execute()) {
                throw new Exception('Failed to update database');
            }
            
            $update_stmt->close();
            $conn->commit();
            
            echo json_encode(['success' => true, 'message' => 'Profile picture reference removed (file was already missing)']);
            $conn->close();
            exit();
        }
        
        if (!unlink($file_path)) {
            throw new Exception('Failed to delete profile picture file');
        }
        
        $update_stmt = $conn->prepare("UPDATE users SET pfp = NULL WHERE id = ?");
        $update_stmt->bind_param("i", $user_id);
        
        if (!$update_stmt->execute()) {
            throw new Exception('Failed to update database after file deletion');
        }
        
        $update_stmt->close();
        
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Profile picture removed successfully']);
        
    } catch (Exception $e) {
        $conn->rollback();
        
        error_log("Profile picture removal error for user $user_id: " . $e->getMessage());
        
        echo json_encode(['success' => false, 'message' => 'Failed to remove profile picture: ' . $e->getMessage()]);
    }

    $conn->close();
?>