<?php
require_once 'db_connection.php';

function getUserBookmarks($userId) {
    global $conn;
    
    $sql = "SELECT m.*, lu.status, lu.rating, lu.chapters 
            FROM user_list lu 
            JOIN manga m ON lu.manga_id = m.id 
            WHERE lu.user_id = ? AND m.approved = 1 
            ORDER BY lu.status, m.title";
            
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return ['error' => 'Prepare failed: ' . $conn->error];
    }
    
    if (!$stmt->bind_param("i", $userId)) {
        return ['error' => 'Binding parameters failed: ' . $stmt->error];
    }
    
    if (!$stmt->execute()) {
        return ['error' => 'Execute failed: ' . $stmt->error];
    }
    
    $result = $stmt->get_result();
    $bookmarks = [];
    
    while ($row = $result->fetch_assoc()) {
        $bookmarks[] = $row;
    }
    
    $stmt->close();
    return $bookmarks;
}
?>
