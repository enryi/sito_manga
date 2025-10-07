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

    try {
        $conn->autocommit(false);
        
        $stmt1 = $conn->prepare("DELETE FROM bookmarks WHERE user_id = ?");
        $stmt1->bind_param("i", $user_id);
        $stmt1->execute();
        $stmt1->close();
        
        $stmt2 = $conn->prepare("DELETE FROM reading_history WHERE user_id = ?");
        $stmt2->bind_param("i", $user_id);
        $stmt2->execute();
        $stmt2->close();
        
        $stmt3 = $conn->prepare("DELETE FROM notifications WHERE user_id = ?");
        $stmt3->bind_param("i", $user_id);
        $stmt3->execute();
        $stmt3->close();
        
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'All reading data cleared successfully']);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to clear data: ' . $e->getMessage()]);
    }

    $conn->close();
?>