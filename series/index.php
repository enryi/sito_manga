<?php
    require_once '../php/index.php';
    $_SESSION['current_path'] = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');

    function createUrlSlug($title) {
        $slug = strtolower($title);
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

    $requestUri = $_SERVER['REQUEST_URI'];
    $basePath = '/enryi/series/';

    $mangaSlug = '';
    if (strpos($requestUri, $basePath) === 0) {
        $mangaSlug = substr($requestUri, strlen($basePath));
        $mangaSlug = trim($mangaSlug, '/');
        
        if (strpos($mangaSlug, '?') !== false) {
            $mangaSlug = substr($mangaSlug, 0, strpos($mangaSlug, '?'));
        }
        
        $mangaSlug = urldecode($mangaSlug);
    }

    if (empty($mangaSlug)) {
        $mangaSlug = isset($_GET['manga']) ? $_GET['manga'] : '';
    }

    $mangaId = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if (empty($mangaSlug) && $mangaId == 0) {
        header("Location: ../php/redirect.php");
        exit();
    }

    if ($mangaId > 0) {
        $mangaQuery = "SELECT * FROM manga WHERE id = ? AND approved = 1";
        $stmt = $conn->prepare($mangaQuery);
        $stmt->bind_param("i", $mangaId);
    } else {
        $searchPatterns = [];
        
        $searchPatterns[] = slugToSearchPattern($mangaSlug);
        
        $searchPatterns[] = $mangaSlug;
        
        $searchPatterns[] = str_replace(['_', '-'], [', ', '-'], $mangaSlug);
        $searchPatterns[] = str_replace('_', ' ', str_replace(',_', ', ', $mangaSlug));
        
        $currentManga = null;
        
        foreach ($searchPatterns as $pattern) {
            $mangaQuery = "SELECT * FROM manga WHERE LOWER(title) = LOWER(?) AND approved = 1";
            $stmt = $conn->prepare($mangaQuery);
            $stmt->bind_param("s", $pattern);
            $stmt->execute();
            $mangaResult = $stmt->get_result();
            
            if ($mangaResult->num_rows > 0) {
                $currentManga = $mangaResult->fetch_assoc();
                break;
            }
            
            $likePattern = '%' . $pattern . '%';
            $mangaQuery = "SELECT * FROM manga WHERE LOWER(title) LIKE LOWER(?) AND approved = 1 LIMIT 1";
            $stmt = $conn->prepare($mangaQuery);
            $stmt->bind_param("s", $likePattern);
            $stmt->execute();
            $mangaResult = $stmt->get_result();
            
            if ($mangaResult->num_rows > 0) {
                $currentManga = $mangaResult->fetch_assoc();
                break;
            }
        }
        
        if (!$currentManga) {
            $cleanPattern = preg_replace('/[^a-zA-Z0-9\s]/', '', slugToSearchPattern($mangaSlug));
            $words = explode(' ', $cleanPattern);
            $words = array_filter($words, function($word) { return strlen($word) > 2; });
            
            if (!empty($words)) {
                $conditions = [];
                foreach ($words as $word) {
                    $conditions[] = "LOWER(title) LIKE LOWER('%" . $conn->real_escape_string($word) . "%')";
                }
                
                $mangaQuery = "SELECT * FROM manga WHERE approved = 1 AND (" . implode(' AND ', $conditions) . ") LIMIT 1";
                $mangaResult = $conn->query($mangaQuery);
                
                if ($mangaResult && $mangaResult->num_rows > 0) {
                    $currentManga = $mangaResult->fetch_assoc();
                }
            }
        }
    }

    if (!isset($currentManga) && $mangaId > 0) {
        $stmt->execute();
        $mangaResult = $stmt->get_result();
        if ($mangaResult->num_rows > 0) {
            $currentManga = $mangaResult->fetch_assoc();
        }
    }

    if (!isset($currentManga) || !$currentManga) {
        http_response_code(404);
        echo "<h1>Manga non trovato</h1>";
        echo "<p>Il manga richiesto non esiste o non è stato ancora approvato.</p>";
        echo "<p>Slug ricercato: " . htmlspecialchars($mangaSlug) . "</p>";
        echo "<a href='../'>Torna alla home</a>";
        exit();
    }

    $mangaId = $currentManga['id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        
        if (isset($_POST['update_status'])) {
            $status = $_POST['status'];
            $chapters = floatval($_POST['chapters']);
            $rating = $_POST['rating'] ? floatval($_POST['rating']) : null;
            $linkSite = !empty($_POST['link_site']) ? $_POST['link_site'] : null;
            
            $checkQuery = "SELECT id FROM user_list WHERE user_id = ? AND manga_id = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("ii", $userId, $mangaId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                $updateQuery = "UPDATE user_list SET status = ?, chapters = ?, rating = ?, link_site = ? WHERE user_id = ? AND manga_id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param("sidsii", $status, $chapters, $rating, $linkSite, $userId, $mangaId);
                $updateStmt->execute();
            } else {
                $insertQuery = "INSERT INTO user_list (user_id, manga_id, status, chapters, rating, link_site) VALUES (?, ?, ?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertQuery);
                $insertStmt->bind_param("iisdds", $userId, $mangaId, $status, $chapters, $rating, $linkSite);
                $insertStmt->execute();
            }
            
            $redirectUrl = "/enryi/series/" . createUrlSlug($currentManga['title']);
            header("Location: " . $redirectUrl);
            exit();
        }
    }

    $userStatus = null;
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $statusQuery = "SELECT status, chapters, rating, link_site FROM user_list WHERE user_id = ? AND manga_id = ?";
        $statusStmt = $conn->prepare($statusQuery);
        $statusStmt->bind_param("ii", $userId, $mangaId);
        $statusStmt->execute();
        $statusResult = $statusStmt->get_result();
        if ($statusResult->num_rows > 0) {
            $userStatus = $statusResult->fetch_assoc();
        }
    }

    $genres = explode(',', $currentManga['genre']);
    $conditions = [];
    foreach ($genres as $genre) {
        $genre = trim($genre);
        $conditions[] = "genre LIKE '%" . $conn->real_escape_string($genre) . "%'";
    }

    $recommendedQuery = "SELECT id, title, image_url FROM manga 
                        WHERE approved = 1 AND id != ? 
                        AND (" . implode(' OR ', $conditions) . ")
                        ORDER BY RAND() LIMIT 5";
    $recStmt = $conn->prepare($recommendedQuery);
    $recStmt->bind_param("i", $mangaId);
    $recStmt->execute();
    $recommendedResult = $recStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($currentManga['title']); ?> - Mangas</title>
        <link rel="icon" href="../images/icon.png" type="image/x-icon">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto|Varela+Round">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
        <link rel="stylesheet" href="../CSS/manga.css">
        <link rel="stylesheet" href="../CSS/navbar.css">
        <link rel="stylesheet" href="../CSS/series.css">
        <link rel="stylesheet" href="../CSS/notifications.css">
        <link rel="stylesheet" href="../CSS/search.css">
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
        <script src="../JS/user_manga.js"></script>
        <script src="../JS/search_manga.js"></script>
        <script src="../JS/manga_notifications.js"></script>
        <script src="../JS/upload-notifications.js"></script>
        <script src="../JS/settings_manga.js"></script>
        <script src="../JS/auth-notifications.js"></script>
    </head>
    <body style="background-color: #181A1B; color: #fff; font-family: 'Roboto', sans-serif;">
        <?php include 'navbar_manga.php'; ?>

        <div class="manga-page-container">
            <div class="manga-main-content">
                <div class="manga-image-section">
                    <?php 
                    $hasUserLink = $userStatus && !empty($userStatus['link_site']);
                    $imageClass = $hasUserLink ? 'clickable-image' : '';
                    $imageOnClick = $hasUserLink ? "onclick=\"window.open('" . htmlspecialchars($userStatus['link_site']) . "', '_blank')\"" : '';
                    ?>
                    <img src="../<?php echo $currentManga['image_url']; ?>" 
                        alt="<?php echo htmlspecialchars($currentManga['title']); ?>" 
                        class="manga-main-image <?php echo $imageClass; ?>" 
                        <?php echo $imageOnClick; ?>>
                </div>
                
                <div class="manga-info-section">
                    <h1 class="manga-main-title"><?php echo htmlspecialchars($currentManga['title']); ?></h1>
                    
                    <div class="synopsis-section">
                        <div class="manga-description">
                            <p><?php echo htmlspecialchars($currentManga['description']); ?></p>
                        </div>
                    </div>

                    <div class="manga-details-grid">
                        <div class="detail-item">
                            <span class="detail-label">Author</span>
                            <span class="detail-value"><?php echo htmlspecialchars($currentManga['author']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Artist</span>
                            <span class="detail-value"><?php echo htmlspecialchars($currentManga['author']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Type</span>
                            <span class="detail-value"><?php echo htmlspecialchars($currentManga['type']); ?></span>
                        </div>
                    </div>

                    <div class="genres-section">
                        <h3 class="genres-title">Genres</h3>
                        <div class="genres-container">
                            <?php 
                            $genres = explode(',', $currentManga['genre']);
                            foreach ($genres as $genre): 
                                $trimmedGenre = trim($genre);
                            ?>
                                <button class="genre-tag" onclick="window.location.href='../search_genre.php?genre=<?php echo urlencode($trimmedGenre); ?>'"><?php echo htmlspecialchars($trimmedGenre); ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div id="notification-container" class="notification-container"></div>
            
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="user-controls <?php echo $userStatus ? 'status-' . $userStatus['status'] : ''; ?>">
                <form method="post" id="manga-status-form">
                    <input type="hidden" name="update_status" value="1">
                    <div class="control-row">
                        <div class="control-group">
                            <label for="status">Status:</label>
                            <select name="status" id="status" onchange="autoSave()">
                                <option value="plan_to_read" <?php echo ($userStatus && $userStatus['status'] == 'plan_to_read') ? 'selected' : ''; ?>>Plan to read</option>
                                <option value="reading" <?php echo ($userStatus && $userStatus['status'] == 'reading') ? 'selected' : ''; ?>>Reading</option>
                                <option value="completed" <?php echo ($userStatus && $userStatus['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                <option value="dropped" <?php echo ($userStatus && $userStatus['status'] == 'dropped') ? 'selected' : ''; ?>>Dropped</option>
                            </select>
                        </div>
                    
                        <div class="control-group">
                            <label for="chapters">Chapter Read:</label>
                            <input type="number" name="chapters" id="chapters" min="0" step="0.1"
                                value="<?php echo $userStatus ? $userStatus['chapters'] : 0; ?>"
                                onchange="autoSave()">
                        </div>
                        
                        <div class="control-group">
                            <label for="rating">Rating (1-10):</label>
                            <input type="number" name="rating" id="rating" min="1" max="10" step="0.1"
                                value="<?php echo $userStatus && $userStatus['rating'] ? $userStatus['rating'] : ''; ?>"
                                onchange="autoSave()">
                        </div>
                        
                        <div class="control-group">
                            <label for="link_site">Website Link:</label>
                            <input type="url" name="link_site" id="link_site" 
                                placeholder="https://example.com"
                                value="<?php echo $userStatus && $userStatus['link_site'] ? htmlspecialchars($userStatus['link_site']) : ''; ?>"
                                onchange="autoSave()">
                        </div>
                        
                        <button type="submit" class="save-btn">Save</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <div class="recommendations-section">
                <h2 class="recommendations-title">Raccomandation</h2>
                <div class="recommendations-grid">
                    <?php if ($recommendedResult && $recommendedResult->num_rows > 0): ?>
                        <?php while ($recommended = $recommendedResult->fetch_assoc()): ?>
                            <?php 
                                $recTitleSlug = createUrlSlug($recommended['title']); 
                                $recPageUrl = "/enryi/series/" . $recTitleSlug;
                            ?>
                            <div class="recommendation-item" onclick="window.location.href='<?php echo $recPageUrl; ?>'">
                                <img src="../<?php echo $recommended['image_url']; ?>" alt="<?php echo htmlspecialchars($recommended['title']); ?>">
                                <div class="recommendation-title"><?php echo htmlspecialchars($recommended['title']); ?></div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-recommendations">No manga is available now.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div id="add-manga-popup" class="popup">
            <div class="popup-content">
                <span class="close-btn" onclick="closeAddMangaPopup()">&times;</span>
                <h5>ADD NEW MANGA</h5>
                <form id="add-manga-form" method="post" action="../php/add_manga.php" enctype="multipart/form-data" autocomplete="off">
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

        <script>
            function autoSave() {
                const form = document.getElementById('manga-status-form');
                const saveBtn = document.querySelector('.save-btn');
                
                if (!form) return;
                
                saveBtn.textContent = 'Saving...';
                saveBtn.style.backgroundColor = '#ffc107';
                saveBtn.disabled = true;
                
                const formData = {
                    manga_id: <?php echo $mangaId; ?>,
                    status: document.getElementById('status').value,
                    chapters: parseFloat(document.getElementById('chapters').value) || 0,
                    rating: document.getElementById('rating').value ? parseFloat(document.getElementById('rating').value) : null,
                    link_site: document.getElementById('link_site').value || null
                };
                
                if (formData.chapters < 0) {
                    showNotification('Chapter count cannot be negative', 'error');
                    resetSaveButton();
                    return;
                }
                

                
                fetch('../php/update_manga_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showNotification('Progress saved successfully! Chapters: ' + data.data.chapters, 'success');
                        
                        const userControls = document.querySelector('.user-controls');
                        if (userControls) {
                            userControls.className = userControls.className.replace(/status-\w+/, '');
                            userControls.classList.add('status-' + data.data.status);
                        }
                        
                        updateImageClickability(formData.link_site);
                        
                    } else {
                        showNotification('Error: ' + (data.error || 'Unknown error'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Connection error. Please try again.', 'error');
                })
                .finally(() => {
                    resetSaveButton();
                });
            }

            function resetSaveButton() {
                const saveBtn = document.querySelector('.save-btn');
                if (saveBtn) {
                    saveBtn.textContent = 'Save';
                    saveBtn.style.backgroundColor = '#28a745';
                    saveBtn.disabled = false;
                }
            }

            function showNotification(message, type) {
                let title = '';
                switch(type) {
                    case 'success':
                        title = 'Saved Successfully';
                        break;
                    case 'error':
                        title = 'Error';
                        break;
                    case 'warning':
                        title = 'Warning';
                        break;
                    default:
                        title = 'Notification';
                        type = 'info';
                }
                
                let container = document.getElementById('upload-notifications-container');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'upload-notifications-container';
                    container.className = 'upload-notifications-container';
                    document.body.appendChild(container);
                }
                
                container.style.left = 'auto';
                container.style.right = '20px';
                
                showUploadNotifications(type, title, message, 3000);
            }

            function updateImageClickability(linkSite) {
                const mangaImage = document.querySelector('.manga-main-image');
                if (mangaImage) {
                    if (linkSite) {
                        mangaImage.classList.add('clickable-image');
                        mangaImage.style.cursor = 'pointer';
                        mangaImage.onclick = () => window.open(linkSite, '_blank');
                    } else {
                        mangaImage.classList.remove('clickable-image');
                        mangaImage.style.cursor = 'default';
                        mangaImage.onclick = null;
                    }
                }
            }

            document.getElementById('manga-status-form')?.addEventListener('submit', function(e) {
                e.preventDefault();
                autoSave();
            });

            document.getElementById('chapters')?.addEventListener('blur', function(e) {
                let value = parseFloat(e.target.value);
                if (!isNaN(value) && value >= 0) {
                    e.target.value = Math.round(value * 10) / 10;
                }
            });
        </script>
    </body>
</html>