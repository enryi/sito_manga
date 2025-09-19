<?php
// php/url_helpers.php - Funzioni helper per la gestione degli URL

/**
 * Crea uno slug URL-friendly da un titolo
 */
function createUrlSlug($title) {
    $slug = strtolower(trim($title));
    // Sostituisci spazi e caratteri speciali con underscore
    $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
    // Rimuovi underscore multipli e quelli all'inizio/fine
    $slug = preg_replace('/_+/', '_', $slug);
    $slug = trim($slug, '_');
    return $slug;
}

/**
 * Converte slug URL in pattern di ricerca per il database
 */
function slugToSearchPattern($slug) {
    // Decodifica URL
    $slug = urldecode($slug);
    // Converte underscore in spazi per la ricerca base
    $pattern = str_replace('_', ' ', $slug);
    return $pattern;
}

/**
 * Genera URL completo per una pagina manga
 */
function getMangaUrl($title, $baseUrl = '/enryi/series/') {
    return $baseUrl . createUrlSlug($title);
}

/**
 * Cerca manga nel database usando diversi pattern
 */
function findMangaBySlug($conn, $slug) {
    $searchPatterns = [];
    
    // Pattern base: sostituisci underscore con spazi
    $searchPatterns[] = slugToSearchPattern($slug);
    
    // Pattern alternativo: mantieni underscore
    $searchPatterns[] = $slug;
    
    // Pattern per titoli con virgole, trattini etc
    $searchPatterns[] = str_replace(['_', '-'], [', ', '-'], $slug);
    $searchPatterns[] = str_replace('_', ' ', str_replace(',_', ', ', $slug));
    
    foreach ($searchPatterns as $pattern) {
        // Prova ricerca esatta
        $stmt = $conn->prepare("SELECT * FROM manga WHERE LOWER(title) = LOWER(?) AND approved = 1");
        $stmt->bind_param("s", $pattern);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        // Prova ricerca con LIKE
        $likePattern = '%' . $pattern . '%';
        $stmt = $conn->prepare("SELECT * FROM manga WHERE LOWER(title) LIKE LOWER(?) AND approved = 1 LIMIT 1");
        $stmt->bind_param("s", $likePattern);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    }
    
    // Ricerca fallback per parole chiave
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

/**
 * Genera breadcrumb per la pagina manga
 */
function getMangaBreadcrumb($mangaTitle) {
    return [
        ['url' => '/enryi/', 'title' => 'Home'],
        ['url' => '#', 'title' => $mangaTitle, 'active' => true]
    ];
}

/**
 * Valida e pulisce slug URL
 */
function validateSlug($slug) {
    if (empty($slug)) {
        return false;
    }
    
    // Rimuovi caratteri pericolosi
    $slug = preg_replace('/[<>"\']/', '', $slug);
    
    // Decodifica URL
    $slug = urldecode($slug);
    
    return $slug;
}
?>