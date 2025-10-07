<?php
    function createUrlSlug($title) {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
        $slug = preg_replace('/_+/', '_', $slug);
        $slug = trim($slug, '_');
        return $slug;
    }

    function slugToSearchPattern($slug) {
        $slug = urldecode($slug);
        $pattern = str_replace('_', ' ', $slug);
        return $pattern;
    }

    function getMangaUrl($title, $baseUrl = '/enryi/series/') {
        return $baseUrl . createUrlSlug($title);
    }

    function findMangaBySlug($conn, $slug) {
        $searchPatterns = [];
        
        $searchPatterns[] = slugToSearchPattern($slug);
        
        $searchPatterns[] = $slug;
        
        $searchPatterns[] = str_replace(['_', '-'], [', ', '-'], $slug);
        $searchPatterns[] = str_replace('_', ' ', str_replace(',_', ', ', $slug));
        
        foreach ($searchPatterns as $pattern) {
            $stmt = $conn->prepare("SELECT * FROM manga WHERE LOWER(title) = LOWER(?) AND approved = 1");
            $stmt->bind_param("s", $pattern);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            
            $likePattern = '%' . $pattern . '%';
            $stmt = $conn->prepare("SELECT * FROM manga WHERE LOWER(title) LIKE LOWER(?) AND approved = 1 LIMIT 1");
            $stmt->bind_param("s", $likePattern);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
        }
        
        $cleanPattern = preg_replace('/[^a-zA-Z0-9\s]/', '', slugToSearchPattern($slug));
        $words = array_filter(explode(' ', $cleanPattern), function($word) { 
            return strlen($word) > 2; 
        });
        
        if (!empty($words)) {
            $conditions = [];
            foreach ($words as $word) {
                $conditions[] = "LOWER(title) LIKE LOWER('%" . $conn->real_escape_string($word) . "%')";
            }
            
            $query = "SELECT * FROM manga WHERE approved = 1 AND (" . implode(' AND ', $conditions) . ") LIMIT 1";
            $result = $conn->query($query);
            
            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc();
            }
        }
        
        return null;
    }

    function getMangaBreadcrumb($mangaTitle) {
        return [
            ['url' => '/enryi/', 'title' => 'Home'],
            ['url' => '#', 'title' => $mangaTitle, 'active' => true]
        ];
    }

    function validateSlug($slug) {
        if (empty($slug)) {
            return false;
        }
        
        $slug = preg_replace('/[<>"\']/', '', $slug);
        
        $slug = urldecode($slug);
        
        return $slug;
    }
?>