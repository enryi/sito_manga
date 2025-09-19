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
            // Aggiungi l'URL dinamico per ogni manga - URL pulito
            $mangaTitleSlug = strtolower(str_replace(' ', '_', $row['title']));
            $row['manga_url'] = "series/" . $mangaTitleSlug;
            $bookmarks[] = $row;
        }
        
        $stmt->close();
        return $bookmarks;
    }

    // Funzione helper per generare l'URL del manga
    function getMangaUrl($mangaTitle) {
        $mangaTitleSlug = strtolower(str_replace(' ', '_', $mangaTitle));
        return "series/" . $mangaTitleSlug;
    }

    // Funzione per mostrare i bookmarks in HTML (se necessaria)
    function displayBookmarks($bookmarks) {
        if (empty($bookmarks) || isset($bookmarks['error'])) {
            echo '<p class="no-data">No bookmarks found.</p>';
            return;
        }
        
        foreach ($bookmarks as $bookmark) {
            $mangaUrl = getMangaUrl($bookmark['title']);
            echo '<div class="bookmark-item" onclick="window.location.href=\'' . htmlspecialchars($mangaUrl) . '\'">';
            echo '<img src="' . htmlspecialchars($bookmark['image_url']) . '" alt="' . htmlspecialchars($bookmark['title']) . '">';
            echo '<div class="bookmark-info">';
            echo '<h3 class="bookmark-title">' . htmlspecialchars($bookmark['title']) . '</h3>';
            echo '<p class="bookmark-status">Status: ' . htmlspecialchars($bookmark['status']) . '</p>';
            if ($bookmark['rating']) {
                echo '<p class="bookmark-rating">Rating: ' . htmlspecialchars($bookmark['rating']) . '/10</p>';
            }
            echo '<p class="bookmark-chapters">Chapters: ' . htmlspecialchars($bookmark['chapters']) . '</p>';
            echo '</div>';
            echo '</div>';
        }
    }
?>