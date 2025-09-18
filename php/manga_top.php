<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "manga";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $query = "
        SELECT 
            m.id, 
            m.title, 
            m.image_url, 
            CASE 
                WHEN CHAR_LENGTH(m.genre) <= 36 THEN m.genre
                ELSE CONCAT(SUBSTRING(m.genre, 1, 33), '...')
            END AS genre,
            COALESCE(FLOOR(AVG(lu.rating) * 10) / 10, 0) AS rating
        FROM manga m
        LEFT JOIN user_list lu ON m.id = lu.manga_id
        WHERE m.approved = 1
        GROUP BY m.id
        HAVING rating > 0
        ORDER BY rating DESC, m.id DESC
        LIMIT 5
    ";

    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $rank = 1;
        while ($row = $result->fetch_assoc()) {
            $mangaTitleSlug = strtolower(str_replace(' ', '_', $row['title']));
            $mangaPageUrl = "series/" . $mangaTitleSlug . ".php";
            $rating = floatval($row['rating']);
            
            echo '<div class="top-manga-item" onclick="window.location.href=\'' . htmlspecialchars($mangaPageUrl) . '\'">';
            echo '<div class="rank">' . $rank . '</div>';
            echo '<div class="image-container">';
            echo '<img src="' . htmlspecialchars($row['image_url'], ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') . '">';
            echo '</div>';
            echo '<div class="info">';
            echo '<p class="manga-title" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1em; display: block; vertical-align: middle;">' . htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') . '</p>';
            $genres = explode(',', $row['genre']);
            $limitedGenres = array_slice($genres, 0, 4);
            if (!empty($limitedGenres)) {
                echo '<p class="manga-genre">' . htmlspecialchars(implode(', ', $limitedGenres), ENT_QUOTES, 'UTF-8');
                if (count($genres) > 4) {
                    echo '...';
                }
                echo '</p>';
            }
            echo '<div class="manga-rating">';
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
            echo '<span class="manga-rating">' . htmlspecialchars($rating, ENT_QUOTES, 'UTF-8') . '</span>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            $rank++;
        }
    } else {
        echo '<p class="no-data">No manga found.</p>';
    }

    $conn->close();
?>