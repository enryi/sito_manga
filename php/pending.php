<?php
    require_once 'session.php';

    $sql = "
        SELECT 
            m.id, 
            m.title, 
            m.chapter, 
            m.website_link, 
            m.image_url, 
            mp.created_at
        FROM manga_pending mp
        INNER JOIN manga m ON mp.manga_id = m.id
        ORDER BY mp.created_at DESC
    ";
    $result = $conn->query($sql);
?>