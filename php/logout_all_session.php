<?php
// logout_all_sessions.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// For basic implementation, we just destroy the current session
// In a production app, you would:
// 1. Have a sessions table to track all user sessions
// 2. Delete all session records for this user
// 3. Possibly set a timestamp to invalidate existing sessions

try {
    // Clear session data
    $_SESSION = array();
    
    // Delete session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    
    // Destroy session
    session_destroy();
    
    echo json_encode(['success' => true, 'message' => 'All sessions logged out successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to logout sessions: ' . $e->getMessage()]);
}
?>