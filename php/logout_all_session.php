<?php
    header('Content-Type: application/json');
    require_once 'session.php';

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit();
    }

    try {
        $_SESSION = array();
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-3600, '/');
        }
        
        session_destroy();
        
        echo json_encode(['success' => true, 'message' => 'All sessions logged out successfully']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to logout sessions: ' . $e->getMessage()]);
    }
?>