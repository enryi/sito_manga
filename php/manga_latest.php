<?php
    require_once 'session.php';
    
    $sql = "SELECT m.id, m.title, m.image_url,
        COALESCE(FLOOR(AVG(lu.rating) * 10) / 10, 0) AS rating
        FROM manga m
        LEFT JOIN user_list lu ON m.id = lu.manga_id
        WHERE m.approved = 1
        GROUP BY m.id, m.title, m.image_url
        ORDER BY m.created_at DESC
        LIMIT 16";
    
    $result = $conn->query($sql);
    $mangaItems = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $mangaItems[] = $row;
        }
    }

    $columns = 4;
    $totalManga = count($mangaItems);

    if ($totalManga > 0) {
        $emptyDivCount = $columns - ($totalManga % $columns);
        foreach ($mangaItems as $manga) {
            $mangaTitleSlug = strtolower(str_replace(' ', '_', $manga['title']));
            $mangaPageUrl = "series/" . $mangaTitleSlug;
            $rating = floatval($manga['rating']);
            
            echo '<div class="manga-item" onclick="window.location.href=\'' . htmlspecialchars($mangaPageUrl) . '\'">';
            echo '<img src="' . htmlspecialchars($manga['image_url']) . '" alt="' . htmlspecialchars($manga['title']) . '">';
            
            echo '<div class="rating-overlay">';
            echo '<div class="rating-content">';
            
            echo '<div class="overlay-title">' . htmlspecialchars($manga['title']) . '</div>';
            
            echo '<div class="rating-row">';
            
            echo '<div class="stars">';
            $ratingOutOfFive = $rating / 2;
            $fullStars = floor($ratingOutOfFive);
            $hasHalfStar = ($ratingOutOfFive - $fullStars) >= 0.5;
            $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
            
            for ($i = 0; $i < $fullStars; $i++) {
                echo '<svg class="manga-star full" viewBox="0 0 24 24" fill="#ffc107" xmlns="http://www.w3.org/2000/svg">
                <polygon points="12,2 15,9 22,9 16,14 18,21 12,17 6,21 8,14 2,9 9,9"/>
                </svg>';
            }
            
            if ($hasHalfStar) {
                echo '<svg class="manga-star half" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <defs>
                <linearGradient id="halfGradient' . $manga['id'] . '" x1="0" y1="0" x2="1" y2="0">
                <stop offset="50%" stop-color="#ffc107"/>
                <stop offset="50%" stop-color="#444"/>
                </linearGradient>
                </defs>
                <polygon points="12,2 15,9 22,9 16,14 18,21 12,17 6,21 8,14 2,9 9,9" fill="url(#halfGradient' . $manga['id'] . ')"/>
                </svg>';
            }
            
            for ($i = 0; $i < $emptyStars; $i++) {
                echo '<svg class="manga-star empty" viewBox="0 0 24 24" fill="#444" xmlns="http://www.w3.org/2000/svg">
                <polygon points="12,2 15,9 22,9 16,14 18,21 12,17 6,21 8,14 2,9 9,9"/>
                </svg>';
            }
            echo '</div>';
            
            if ($rating > 0) {
                echo '<div class="rating-number">' . htmlspecialchars($rating, ENT_QUOTES, 'UTF-8') . '</div>';
            } else {
                echo '<div class="rating-number">N/A</div>';
            }
            
            echo '</div>';
            echo '</div>';
            echo '</div>';
            
            echo '<p class="manga-title">' . htmlspecialchars($manga['title']) . '</p>';
            echo '</div>';
        }
        if ($emptyDivCount < $columns) {
            for ($i = 0; $i < $emptyDivCount; $i++) {
                echo '<div class="manga-item-fake"></div>';
            }
        }
    } else {
        echo '<p class="no-data">No manga found.</p>';
    }
?>