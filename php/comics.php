<?php
    require_once 'session.php';

    $mangaPerPage = 16;
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $currentPage = max(1, $currentPage);
    $sortParam = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
    $orderClause = '';
    
    switch ($sortParam) {
        case 'oldest':
            $orderClause = 'ORDER BY m.created_at ASC';
            break;
        case 'rating_high':
            $orderClause = 'ORDER BY rating DESC, m.created_at DESC';
            break;
        case 'rating_low':
            $orderClause = 'ORDER BY rating ASC, m.created_at DESC';
            break;
        case 'title_az':
            $orderClause = 'ORDER BY m.title ASC';
            break;
        case 'title_za':
            $orderClause = 'ORDER BY m.title DESC';
            break;
        case 'newest':
        default:
            $orderClause = 'ORDER BY m.created_at DESC';
            break;
    }
    
    $offset = ($currentPage - 1) * $mangaPerPage;

    $sql = "SELECT m.id, m.title, m.image_url, m.created_at,
        COALESCE(FLOOR(AVG(lu.rating) * 10) / 10, 0) AS rating
        FROM manga m
        LEFT JOIN user_list lu ON m.id = lu.manga_id
        WHERE m.approved = 1
        GROUP BY m.id, m.title, m.image_url, m.created_at
        $orderClause
        LIMIT " . ($mangaPerPage + 1) . " OFFSET $offset";
    
    $result = $conn->query($sql);
    $mangaItems = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $mangaItems[] = $row;
        }
    }

    $hasNextPage = count($mangaItems) > $mangaPerPage;
    
    if ($hasNextPage) {
        array_pop($mangaItems);
    }

    $columns = 4;
    $currentPageMangaCount = count($mangaItems);

    if ($currentPageMangaCount > 0) {
        $emptyDivCount = $columns - ($currentPageMangaCount % $columns);
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
        
        if ($emptyDivCount < $columns && $emptyDivCount > 0) {
            for ($i = 0; $i < $emptyDivCount; $i++) {
                echo '<div class="manga-item-fake"></div>';
            }
        }
    } else {
        echo '<p class="no-data">No manga found.</p>';
    }

    if ($currentPage > 1 || $hasNextPage) {
        echo '<div class="simple-pagination">';
        
        $baseUrl = '?';
        if ($sortParam !== 'newest') {
            $baseUrl .= 'sort=' . urlencode($sortParam) . '&';
        }
        
        if ($currentPage > 1) {
            echo '<a href="' . $baseUrl . 'page=' . ($currentPage - 1) . '" class="nav-btn prev-btn">';
            echo '<svg width="16" height="16" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.81809 4.18179C8.99383 4.35753 8.99383 4.64245 8.81809 4.81819L6.13629 7.49999L8.81809 10.1818C8.99383 10.3575 8.99383 10.6424 8.81809 10.8182C8.64236 10.9939 8.35743 10.9939 8.1817 10.8182L5.1817 7.81819C5.09731 7.73379 5.0499 7.61933 5.0499 7.49999C5.0499 7.38064 5.09731 7.26618 5.1817 7.18179L8.1817 4.18179C8.35743 4.00605 8.64236 4.00605 8.81809 4.18179Z" fill="currentColor" fill-rule="evenodd" clip-rule="evenodd" stroke="currentColor" stroke-width="1"></path></svg>';
            echo 'Previous</a>';
        } else {
            echo '<span class="nav-btn prev-btn disabled">';
            echo '<svg width="16" height="16" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.81809 4.18179C8.99383 4.35753 8.99383 4.64245 8.81809 4.81819L6.13629 7.49999L8.81809 10.1818C8.99383 10.3575 8.99383 10.6424 8.81809 10.8182C8.64236 10.9939 8.35743 10.9939 8.1817 10.8182L5.1817 7.81819C5.09731 7.73379 5.0499 7.61933 5.0499 7.49999C5.0499 7.38064 5.09731 7.26618 5.1817 7.18179L8.1817 4.18179C8.35743 4.00605 8.64236 4.00605 8.81809 4.18179Z" fill="currentColor" fill-rule="evenodd" clip-rule="evenodd" stroke="currentColor" stroke-width="1"></path></svg>';
            echo 'Previous</span>';
        }
        
        if ($hasNextPage) {
            echo '<a href="' . $baseUrl . 'page=' . ($currentPage + 1) . '" class="nav-btn next-btn">';
            echo 'Next<svg width="16" height="16" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.18194 4.18185C6.35767 4.00611 6.6426 4.00611 6.81833 4.18185L9.81833 7.18185C9.90272 7.26624 9.95013 7.3807 9.95013 7.50005C9.95013 7.6194 9.90272 7.73386 9.81833 7.81825L6.81833 10.8182C6.6426 10.994 6.35767 10.994 6.18194 10.8182C6.0062 10.6425 6.0062 10.3576 6.18194 10.1819L8.86374 7.50005L6.18194 4.81825C6.0062 4.64251 6.0062 4.35759 6.18194 4.18185Z" fill="currentColor" fill-rule="evenodd" clip-rule="evenodd" stroke="currentColor" stroke-width="1"></path></svg>';
            echo '</a>';
        } else {
            echo '<span class="nav-btn next-btn disabled">';
            echo 'Next<svg width="16" height="16" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.18194 4.18185C6.35767 4.00611 6.6426 4.00611 6.81833 4.18185L9.81833 7.18185C9.90272 7.26624 9.95013 7.3807 9.95013 7.50005C9.95013 7.6194 9.90272 7.73386 9.81833 7.81825L6.81833 10.8182C6.6426 10.994 6.35767 10.994 6.18194 10.8182C6.0062 10.6425 6.0062 10.3576 6.18194 10.1819L8.86374 7.50005L6.18194 4.81825C6.0062 4.64251 6.0062 4.35759 6.18194 4.18185Z" fill="currentColor" fill-rule="evenodd" clip-rule="evenodd" stroke="currentColor" stroke-width="1"></path></svg>';
            echo '</span>';
        }
        
        echo '</div>';
    }
?>