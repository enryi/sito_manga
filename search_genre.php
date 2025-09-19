<?php
// search_genre.php - Ricerca manga per genere (moved to root folder)
require_once 'php/index.php';
$_SESSION['current_path'] = $_SERVER['PHP_SELF'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "manga";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ottieni il genere dalla query string
$searchGenre = isset($_GET['genre']) ? trim($_GET['genre']) : '';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Genre: <?php echo htmlspecialchars($searchGenre); ?> - Mangas</title>
        <link rel="icon" href="images/icon.png" type="image/x-icon">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto|Varela+Round">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
        <link rel="stylesheet" href="CSS/manga.css">
        <link rel="stylesheet" href="CSS/navbar.css">
        <link rel="stylesheet" href="CSS/search.css">
        <link rel="stylesheet" href="CSS/notifications.css">
        <link rel="stylesheet" href="CSS/pagination.css">
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
        <script src="JS/user.js"></script>
        <script src="JS/search.js"></script>
        <script src="JS/filter.js"></script>
        <script src="JS/notifications.js"></script>
        <script src="JS/upload-notifications.js"></script>
    </head>
    <body style="background-color: #181A1B; color: #fff; font-family: 'Roboto', sans-serif;">
        <!-- Navbar -->
        <div class="navbar">
            <div class="navbar-container">
                <div class="logo-container">
                    <a href="php/redirect.php">
                        <img src="images/icon.png" alt="Logo" class="logo" />
                    </a>
                    <div class="nav-links">
                        <a href="php/redirect.php" class="nav-link">Home</a>
                        <a href="bookmark/" class="nav-link">Bookmarks</a>
                        <a href="comics/" class="nav-link">Comics</a>
                    </div>
                </div>
                <div class="search-container" autocomplete="off">
                    <input type="text" id="search-input" placeholder="Search" autocomplete="off" />
                    <div id="search-results" class="search-results-container">
                        <h class="search-results"></h>
                        <h class="search-results2"></h>
                    </div>
                    <svg class="search-icon" viewBox="0 0 24 15">
                        <path d="M10 6.5C10 8.433 8.433 10 6.5 10C4.567 10 3 8.433 3 6.5C3 4.567 4.567 3 6.5 3C8.433 3 10 4.567 10 6.5ZM9.30884 10.0159C8.53901 10.6318 7.56251 11 6.5 11C4.01472 11 2 8.98528 2 6.5C2 4.01472 4.01472 2 6.5 2C8.98528 2 11 4.01472 11 6.5C11 7.56251 10.6318 8.53901 10.0159 9.30884L12.8536 12.1464C13.0488 12.3417 13.0488 12.6583 12.8536 12.8536C12.6583 13.0488 12.3417 13.0488 12.1464 12.8536L9.30884 10.0159Z"></path>
                    </svg>
                </div>
                <div class="user-container">
                    <div class="notification">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="notification-icon">
                            <path d="M10.268 21a2 2 0 0 0 3.464 0"></path>
                            <path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326"></path>
                        </svg>
                    </div>
                    <?php if (isset($_SESSION['logged_in']) && isset($_SESSION['username'])): ?>
                        <?php
                            $user_icon = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] ? "images/admin.png" : "images/user.svg";
                        ?>
                        <img src="<?php echo $user_icon; ?>" alt="User Icon" class="user-icon" onclick="toggleUserMenu()" />
                        <div id="user-dropdown" class="user-dropdown">
                            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                                <a href="pending/" class="pending-manga">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="approval-icon">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    Pending
                                </a>
                            <?php endif; ?>
                            <a href="#" onclick="logout(); return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="logout-icon">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" x2="9" y1="12" y2="12"></line>
                                </svg>
                                Log Out
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="login/" class="login-button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                <polyline points="10 17 15 12 10 7"></polyline>
                                <line x1="15" x2="3" y1="12" y2="12"></line>
                            </svg>
                            Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div id="notification-container" class="notification-container"></div>
        
        <!-- Struttura principale -->
        <div class="manga">
            <div class="manga-container">
                <div class="left-column">
                    <div class="popular-manga-container">
                        <div class="series-header">
                            <h3 class="manga-title">GENRE: <?php echo strtoupper(htmlspecialchars($searchGenre)); ?></h3>
                            <div class="filter-container">
                                <button class="filter-button" onclick="toggleFilterDropdown()">
                                    <svg class="filter-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3 4.5C3 4.22386 3.22386 4 3.5 4H20.5C20.7761 4 21 4.22386 21 4.5C21 4.77614 20.7761 5 20.5 5H3.5C3.22386 5 3 4.77614 3 4.5Z" fill="currentColor"/>
                                        <path d="M5 8.5C5 8.22386 5.22386 8 5.5 8H18.5C18.7761 8 19 8.22386 19 8.5C19 8.77614 18.7761 9 18.5 9H5.5C5.22386 9 5 8.77614 5 8.5Z" fill="currentColor"/>
                                        <path d="M7 12.5C7 12.2239 7.22386 12 7.5 12H16.5C16.7761 12 17 12.2239 17 12.5C17 12.7761 16.7761 13 16.5 13H7.5C7.22386 13 7 12.7761 7 12.5Z" fill="currentColor"/>
                                        <path d="M9 16.5C9 16.2239 9.22386 16 9.5 16H14.5C14.7761 16 15 16.2239 15 16.5C15 16.7761 14.7761 17 14.5 17H9.5C9.22386 17 9 16.7761 9 16.5Z" fill="currentColor"/>
                                        <path d="M11 20.5C11 20.2239 11.2239 20 11.5 20H12.5C12.7761 20 13 20.2239 13 20.5C13 20.7761 12.7761 21 12.5 21H11.5C11.2239 21 11 20.7761 11 20.5Z" fill="currentColor"/>
                                    </svg>
                                </button>
                                <div class="filter-dropdown" id="filter-dropdown">
                                    <div class="filter-dropdown-header">Sort By</div>
                                    <button class="filter-option active" data-sort="newest">
                                        Newest First <span class="sort-indicator">↓</span>
                                    </button>
                                    <button class="filter-option" data-sort="oldest">
                                        Oldest First <span class="sort-indicator">↑</span>
                                    </button>
                                    <button class="filter-option" data-sort="rating_high">
                                        Rating High-Low <span class="sort-indicator">↓</span>
                                    </button>
                                    <button class="filter-option" data-sort="rating_low">
                                        Rating Low-High <span class="sort-indicator">↑</span>
                                    </button>
                                    <button class="filter-option" data-sort="title_az">
                                        Title A-Z <span class="sort-indicator">↓</span>
                                    </button>
                                    <button class="filter-option" data-sort="title_za">
                                        Title Z-A <span class="sort-indicator">↑</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="divider"></div>
                        <div class="manga-popular-list">
                            <?php
                                // Includi la logica dei manga con filtro per genere
                                $searchPattern = "%" . $searchGenre . "%";
                                
                                // Configurazione paginazione
                                $mangaPerPage = 16;
                                $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                $currentPage = max(1, $currentPage);
                                
                                // Gestione ordinamento
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
                                
                                // Calcola l'offset per la query
                                $offset = ($currentPage - 1) * $mangaPerPage;

                                // Query con filtro genere
                                $sql = "SELECT m.id, m.title, m.image_url, m.created_at,
                                    COALESCE(FLOOR(AVG(lu.rating) * 10) / 10, 0) AS rating
                                    FROM manga m
                                    LEFT JOIN user_list lu ON m.id = lu.manga_id
                                    WHERE m.approved = 1 AND m.genre LIKE ?
                                    GROUP BY m.id, m.title, m.image_url, m.created_at
                                    $orderClause
                                    LIMIT " . ($mangaPerPage + 1) . " OFFSET $offset";
                                
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("s", $searchPattern);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                $mangaItems = [];
                                if ($result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $mangaItems[] = $row;
                                    }
                                }

                                // Verifica se c'è una pagina successiva
                                $hasNextPage = count($mangaItems) > $mangaPerPage;
                                
                                // Rimuovi l'elemento extra se presente
                                if ($hasNextPage) {
                                    array_pop($mangaItems);
                                }

                                $columns = 4;
                                $currentPageMangaCount = count($mangaItems);

                                // Mostra i manga della pagina corrente
                                if ($currentPageMangaCount > 0) {
                                    $emptyDivCount = $columns - ($currentPageMangaCount % $columns);
                                    foreach ($mangaItems as $manga) {
                                        $mangaTitleSlug = strtolower(str_replace(' ', '_', $manga['title']));
                                        // URL pulito senza parametri GET
                                        $mangaPageUrl = "series/" . $mangaTitleSlug;
                                        $rating = floatval($manga['rating']);
                                        
                                        echo '<div class="manga-item" onclick="window.location.href=\'' . htmlspecialchars($mangaPageUrl) . '\'">';
                                        echo '<img src="' . htmlspecialchars($manga['image_url']) . '" alt="' . htmlspecialchars($manga['title']) . '">';
                                        
                                        // Overlay del rating che appare in hover
                                        echo '<div class="rating-overlay">';
                                        echo '<div class="rating-content">';
                                        
                                        // Titolo dell'overlay su due righe
                                        echo '<div class="overlay-title">' . htmlspecialchars($manga['title']) . '</div>';
                                        
                                        // Container per stelle e voto numerico sulla stessa riga
                                        echo '<div class="rating-row">';
                                        
                                        // Sempre 5 stelle
                                        echo '<div class="stars">';
                                        $ratingOutOfFive = $rating / 2; // Converti da 10 a 5
                                        $fullStars = floor($ratingOutOfFive);
                                        $hasHalfStar = ($ratingOutOfFive - $fullStars) >= 0.5;
                                        $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
                                        
                                        // Stelle piene
                                        for ($i = 0; $i < $fullStars; $i++) {
                                            echo '<svg class="manga-star full" viewBox="0 0 24 24" fill="#ffc107" xmlns="http://www.w3.org/2000/svg">
                                            <polygon points="12,2 15,9 22,9 16,14 18,21 12,17 6,21 8,14 2,9 9,9"/>
                                            </svg>';
                                        }
                                        
                                        // Stella mezza (se presente)
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
                                        
                                        // Stelle vuote
                                        for ($i = 0; $i < $emptyStars; $i++) {
                                            echo '<svg class="manga-star empty" viewBox="0 0 24 24" fill="#444" xmlns="http://www.w3.org/2000/svg">
                                            <polygon points="12,2 15,9 22,9 16,14 18,21 12,17 6,21 8,14 2,9 9,9"/>
                                            </svg>';
                                        }
                                        echo '</div>';
                                        
                                        // Voto numerico accanto alle stelle
                                        if ($rating > 0) {
                                            echo '<div class="rating-number">' . htmlspecialchars($rating, ENT_QUOTES, 'UTF-8') . '</div>';
                                        } else {
                                            echo '<div class="rating-number">N/A</div>';
                                        }
                                        
                                        echo '</div>'; // Chiude rating-row
                                        echo '</div>';
                                        echo '</div>';
                                        
                                        echo '<p class="manga-title">' . htmlspecialchars($manga['title']) . '</p>';
                                        echo '</div>';
                                    }
                                    
                                    // Aggiungi div vuoti per mantenere il layout
                                    if ($emptyDivCount < $columns && $emptyDivCount > 0) {
                                        for ($i = 0; $i < $emptyDivCount; $i++) {
                                            echo '<div class="manga-item-fake"></div>';
                                        }
                                    }
                                } else {
                                    echo '<p class="no-data">Nessun manga trovato per il genere "' . htmlspecialchars($searchGenre) . '".</p>';
                                }

                                // Mostra la paginazione con parametro genere
                                if ($currentPage > 1 || $hasNextPage) {
                                    echo '<div class="simple-pagination">';
                                    
                                    // Costruisci URL base con parametri di ordinamento e genere
                                    $baseUrl = '?genre=' . urlencode($searchGenre) . '&';
                                    if ($sortParam !== 'newest') {
                                        $baseUrl .= 'sort=' . urlencode($sortParam) . '&';
                                    }
                                    
                                    // Bottone Previous
                                    if ($currentPage > 1) {
                                        echo '<a href="' . $baseUrl . 'page=' . ($currentPage - 1) . '" class="nav-btn prev-btn">';
                                        echo '<svg width="16" height="16" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.81809 4.18179C8.99383 4.35753 8.99383 4.64245 8.81809 4.81819L6.13629 7.49999L8.81809 10.1818C8.99383 10.3575 8.99383 10.6424 8.81809 10.8182C8.64236 10.9939 8.35743 10.9939 8.1817 10.8182L5.1817 7.81819C5.09731 7.73379 5.0499 7.61933 5.0499 7.49999C5.0499 7.38064 5.09731 7.26618 5.1817 7.18179L8.1817 4.18179C8.35743 4.00605 8.64236 4.00605 8.81809 4.18179Z" fill="currentColor" fill-rule="evenodd" clip-rule="evenodd" stroke="currentColor" stroke-width="1"></path></svg>';
                                        echo 'Previous</a>';
                                    } else {
                                        echo '<span class="nav-btn prev-btn disabled">';
                                        echo '<svg width="16" height="16" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.81809 4.18179C8.99383 4.35753 8.99383 4.64245 8.81809 4.81819L6.13629 7.49999L8.81809 10.1818C8.99383 10.3575 8.99383 10.6424 8.81809 10.8182C8.64236 10.9939 8.35743 10.9939 8.1817 10.8182L5.1817 7.81819C5.09731 7.73379 5.0499 7.61933 5.0499 7.49999C5.0499 7.38064 5.09731 7.26618 5.1817 7.18179L8.1817 4.18179C8.35743 4.00605 8.64236 4.00605 8.81809 4.18179Z" fill="currentColor" fill-rule="evenodd" clip-rule="evenodd" stroke="currentColor" stroke-width="1"></path></svg>';
                                        echo 'Previous</span>';
                                    }
                                    
                                    // Bottone Next
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

                                $conn->close();
                            ?>
                        </div>
                    </div>
                </div>
                <div class="top-manga-container">
                    <h3 class="manga-title">TOP MANGA</h3>
                    <div class="divider"></div>
                    <div class="manga-top-list">
                        <?php
                            require_once 'php/manga_top.php';
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Add manga popup -->
        <div id="add-manga-popup" class="popup">
            <div class="popup-content">
                <span class="close-btn" onclick="closeAddMangaPopup()">&times;</span>
                <h5>ADD NEW MANGA</h5>
                <form id="add-manga-form" method="post" action="add_manga.php" enctype="multipart/form-data" autocomplete="off">
                    <label for="manga-title">TITLE:</label>
                    <input type="text" id="manga-title" name="manga-title" placeholder="Title" required>
                    
                    <label for="manga-image">UPLOAD IMAGE:</label>
                    <input type="file" id="manga-image" name="manga-image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" required>
                    <small style="color: #888; font-size: 12px;">Accepted formats: JPG, PNG, GIF, WebP (Max: 5MB)</small>
                    
                    <label for="manga-description">DESCRIPTION:</label>
                    <input type="text" id="manga-description" name="manga-description" placeholder="Description" required>
                    
                    <label for="manga-author">AUTHOR:</label>
                    <input type="text" id="manga-author" name="manga-author" placeholder="Author" required>
                    
                    <label for="manga-type">TYPE:</label>
                    <select id="manga-type" name="manga-type" required>
                        <option value="" disabled selected>Type</option>
                        <option value="Manga">Manga</option>
                        <option value="Manwha">Manwha</option>
                        <option value="Manhua">Manhua</option>
                    </select>
                    <label for="manga-genre">GENRE:</label>
                    <input type="text" id="manga-genre" name="manga-genre" placeholder="Genre" required>
                    <button type="submit">ADD MANGA</button>
                </form>
            </div>
        </div>
    </body>
</html>