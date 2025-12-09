<?php
    require_once 'session.php';

    $sql = "
        SELECT 
            m.id, 
            m.title, 
            m.image_url, 
            m.description,
            m.author,
            m.type,
            m.genre,
            m.created_at,
            m.submitted_by,
            u.username as submitter_name
        FROM manga m
        LEFT JOIN users u ON m.submitted_by = u.id
        WHERE m.approved = 0
        ORDER BY m.created_at DESC
    ";
    $result = $conn->query($sql);
?>