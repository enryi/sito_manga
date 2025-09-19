<?php
    require_once 'db_connection.php';

    $query = "
        SELECT m.id, m.title, m.image_url,
        COUNT(lu.user_id) AS user_votes,
        COALESCE(FLOOR(AVG(lu.rating) * 10) / 10, 0) AS rating
        FROM manga m
        LEFT JOIN user_list lu ON m.id = lu.manga_id
        WHERE m.approved = 1
        GROUP BY m.id
        HAVING user_votes > 0
        ORDER BY user_votes DESC, rating DESC
        LIMIT 4;
    ";

    $result = $conn->query($query);
    $mangaItems = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $mangaItems[] = $row;
        }
    }
    $conn->close();

    $columns = 4;
    $totalManga = count($mangaItems);

    if ($totalManga > 0) {
        $emptyDivCount = $columns - ($totalManga % $columns);
        foreach ($mangaItems as $manga) {
            $mangaTitleSlug = strtolower(str_replace(' ', '_', $manga['title']));
            // URL pulito senza parametri GET
            $mangaPageUrl = "series/" . $mangaTitleSlug;
            $rating = floatval($manga['rating']);
            
            echo '<div class="manga-item" onclick="window.location.href=\'' . htmlspecialchars($mangaPageUrl) . '\'">';
            echo '<img class="manga-image" src="' . htmlspecialchars($manga['image_url'], ENT_QUOTES, 'UTF-8') . '" alt="Manga Image">';
            echo '<p class="manga-title">' . htmlspecialchars($manga['title'], ENT_QUOTES, 'UTF-8') . '</p>';
            echo '<div class="manga-rating-container">';
            echo '<div class="stars">';
            $fullStars = floor($rating / 2);
            $hasHalfStar = ($rating / 2) - $fullStars;
            for ($i = 0; $i < $fullStars; $i++) {
                echo '<svg class="manga-star" viewBox="0 0 24.99 24.1" fill="gold" xmlns="http://www.w3.org/2000/svg">
                <polygon points="12,2 15,9 22,9 16,14 18,21 12,17 6,21 8,14 2,9 9,9"/>
                </svg>';
            }
            if ($hasHalfStar > 0.45) {
                echo '<svg class="manga-star" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <defs>
                <linearGradient id="halfGradient" x1="0" y1="0" x2="1" y2="0">
                <stop offset="50%" stop-color="gold"/>
                <stop offset="50%" stop-color="lightgray"/>
                </linearGradient>
                </defs>
                <polygon points="12,2 15,9 22,9 16,14 18,21 12,17 6,21 8,14 2,9 9,9" fill="url(#halfGradient)"/>
                </svg>';
            }
            echo '</div>';
            echo '<p class="manga-rating">' . htmlspecialchars($rating, ENT_QUOTES, 'UTF-8') . '</p>';
            echo '</div>';
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