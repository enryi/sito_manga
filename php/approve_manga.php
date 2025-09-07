
<?php
    session_start();
    require_once 'notification_functions.php';
    
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "manga";
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $mangaId = intval($_POST['manga_id']);
        $description = $conn->real_escape_string($_POST['description']);

        $mangaQuery = "SELECT title, image_url, description, author, type, genre, submitted_by FROM manga WHERE id = ?";
        $mangaStmt = $conn->prepare($mangaQuery);
        $mangaStmt->bind_param("i", $mangaId);
        $mangaStmt->execute();
        $mangaResult = $mangaStmt->get_result();
        
        if ($mangaResult && $mangaResult->num_rows > 0) {
            $manga = $mangaResult->fetch_assoc();
            $submitted_by = $manga['submitted_by'];
            $manga_title = $manga['title'];

            $query = "UPDATE manga SET description = ?, approved = 1 WHERE id = ?";
            $updateStmt = $conn->prepare($query);
            $updateStmt->bind_param("si", $description, $mangaId);
            
            if ($updateStmt->execute()) {
                $title = htmlspecialchars($manga['title'], ENT_QUOTES, 'UTF-8');
                $imageUrl = htmlspecialchars($manga['image_url'], ENT_QUOTES, 'UTF-8');
                $author = htmlspecialchars($manga['author'], ENT_QUOTES, 'UTF-8');
                $type = htmlspecialchars($manga['type'], ENT_QUOTES, 'UTF-8');
                $genre = htmlspecialchars($manga['genre'], ENT_QUOTES, 'UTF-8');
                $filename = strtolower(str_replace(' ', '_', $title)) . '.php';
                $filePath = "../series/$filename";
                
                if (!is_dir("../series")) {
                    mkdir("../series", 0777, true);
                }
                $pageContent = <<<PHP
<?php
    require_once '../php/index.php';
    \$_SESSION['current_path'] = htmlspecialchars(\$_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');
    
    \$servername = "localhost";
    \$username = "root";
    \$password = "";
    \$dbname = "manga";
    \$conn = new mysqli(\$servername, \$username, \$password, \$dbname);
    
    if (\$conn->connect_error) {
        die("Connection failed: " . \$conn->connect_error);
    }
    
    \$mangaQuery = "SELECT * FROM manga WHERE id = $mangaId AND approved = 1";
    \$mangaResult = \$conn->query(\$mangaQuery);
    \$currentManga = \$mangaResult->fetch_assoc();
    \$mangaId = \$currentManga['id'];
    
    if (\$_SERVER['REQUEST_METHOD'] === 'POST' && isset(\$_SESSION['user_id'])) {
        \$userId = \$_SESSION['user_id'];
        
        if (isset(\$_POST['update_status'])) {
            \$status = \$_POST['status'];
            \$chapters = intval(\$_POST['chapters']);
            \$rating = \$_POST['rating'] ? floatval(\$_POST['rating']) : null;
            
            \$checkQuery = "SELECT id FROM user_list WHERE user_id = ? AND manga_id = ?";
            \$checkStmt = \$conn->prepare(\$checkQuery);
            \$checkStmt->bind_param("ii", \$userId, \$mangaId);
            \$checkStmt->execute();
            \$checkResult = \$checkStmt->get_result();
            
            if (\$checkResult->num_rows > 0) {
                \$updateQuery = "UPDATE user_list SET status = ?, chapters = ?, rating = ? WHERE user_id = ? AND manga_id = ?";
                \$updateStmt = \$conn->prepare(\$updateQuery);
                \$updateStmt->bind_param("sidii", \$status, \$chapters, \$rating, \$userId, \$mangaId);
                \$updateStmt->execute();
            } else {
                \$insertQuery = "INSERT INTO user_list (user_id, manga_id, status, chapters, rating) VALUES (?, ?, ?, ?, ?)";
                \$insertStmt = \$conn->prepare(\$insertQuery);
                \$insertStmt->bind_param("iisid", \$userId, \$mangaId, \$status, \$chapters, \$rating);
                \$insertStmt->execute();
            }
        }
    }

    \$userStatus = null;
    if (isset(\$_SESSION['user_id'])) {
        \$userId = \$_SESSION['user_id'];
        \$statusQuery = "SELECT status, chapters, rating FROM user_list WHERE user_id = ? AND manga_id = ?";
        \$statusStmt = \$conn->prepare(\$statusQuery);
        \$statusStmt->bind_param("ii", \$userId, \$mangaId);
        \$statusStmt->execute();
        \$statusResult = \$statusStmt->get_result();
        if (\$statusResult->num_rows > 0) {
            \$userStatus = \$statusResult->fetch_assoc();
        }
    }

    \$genres = explode(',', \$currentManga['genre']);
    \$genreList = "'" . implode("','", array_map('trim', \$genres)) . "'";
    \$recommendedQuery = "SELECT id, title, image_url FROM manga 
                          WHERE approved = 1 AND id != \$mangaId 
                          AND (";
    \$conditions = [];
    foreach (\$genres as \$genre) {
        \$genre = trim(\$genre);
        \$conditions[] = "genre LIKE '%\$genre%'";
    }
    \$recommendedQuery .= implode(' OR ', \$conditions);
    \$recommendedQuery .= ") ORDER BY RAND() LIMIT 5";
    \$recommendedResult = \$conn->query(\$recommendedQuery);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>$title - Mangas</title>
        <link rel="icon" href="../images/icon.png" type="image/x-icon">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto|Varela+Round">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
        <link rel="stylesheet" href="../CSS/manga.css">
        <link rel="stylesheet" href="../CSS/navbar.css">
        <link rel="stylesheet" href="../CSS/series.css">
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
        <script src="../JS/user.js"></script>
        <script src="../JS/search_manga.js"></script>
    </head>
    <body style="background-color: #181A1B; color: #fff; font-family: 'Roboto', sans-serif;">
        <div class="navbar">
            <div class="navbar-container">
                <div class="logo-container">
                    <a href="../php/redirect.php">
                        <img src="../images/icon.png" alt="Logo" class="logo" />
                    </a>
                    <div class="nav-links">
                        <a href="../php/redirect.php" class="nav-link">Home</a>
                        <a href="../bookmark" class="nav-link">Bookmarks</a>
                        <a href="../comics" class="nav-link">Comics</a>
                    </div>
                </div>
                <div class="search-container" autocomplete="off">
                    <input type="text" id="search-input" placeholder="Search" onkeyup="searchManga()" autocomplete="off" />
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
                    <?php if (isset(\$_SESSION['logged_in']) && isset(\$_SESSION['username'])): ?>
                        <?php \$user_icon = isset(\$_SESSION['is_admin']) && \$_SESSION['is_admin'] ? "../images/admin.png" : "../images/user.svg"; ?>
                        <img src="<?php echo \$user_icon; ?>" alt="User Icon" class="user-icon" onclick="toggleUserMenu()" />
                        <div id="user-dropdown" class="user-dropdown">
                            <?php if (isset(\$_SESSION['is_admin']) && \$_SESSION['is_admin']): ?>
                                <a href="../pending" class="pending-manga">
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
                        <a href="../login" class="login-button">
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

        <div class="manga-page-container">
            <div class="manga-main-content">
                <div class="manga-image-section">
                    <img src="../<?php echo \$currentManga['image_url']; ?>" alt="$title" class="manga-main-image">
                </div>
                
                <div class="manga-info-section">
                    <h1 class="manga-main-title">$title</h1>
                    
                    <div class="manga-details">
                        <div class="manga-detail-item">
                            <span class="detail-label">Autore:</span>
                            <span class="detail-value"><?php echo \$currentManga['author']; ?></span>
                        </div>
                        <div class="manga-detail-item">
                            <span class="detail-label">Tipo:</span>
                            <span class="detail-value"><?php echo \$currentManga['type']; ?></span>
                        </div>
                        <div class="manga-detail-item">
                            <span class="detail-label">Generi:</span>
                            <span class="detail-value"><?php echo \$currentManga['genre']; ?></span>
                        </div>
                    </div>
                    
                    <div class="manga-description">
                        <p><?php echo \$currentManga['description']; ?></p>
                    </div>
                </div>
            </div>

            <?php if (isset(\$_SESSION['user_id'])): ?>
                <div class="user-controls <?php echo \$userStatus ? 'status-' . \$userStatus['status'] : ''; ?>">
                    <form method="post" id="manga-status-form">
                        <input type="hidden" name="update_status" value="1">
                        <div class="control-row">
                            <div class="control-group">
                                <label for="status">Stato:</label>
                                <select name="status" id="status" onchange="autoSave()">
                                    <option value="plan_to_read" <?php echo (\$userStatus && \$userStatus['status'] == 'plan_to_read') ? 'selected' : ''; ?>>Plan to read</option>
                                    <option value="reading" <?php echo (\$userStatus && \$userStatus['status'] == 'reading') ? 'selected' : ''; ?>>Reading</option>
                                    <option value="completed" <?php echo (\$userStatus && \$userStatus['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                    <option value="dropped" <?php echo (\$userStatus && \$userStatus['status'] == 'dropped') ? 'selected' : ''; ?>>Dropped</option>
                                </select>
                            </div>
                            
                            <div class="control-group">
                                <label for="chapters">Capitoli letti:</label>
                                <input type="number" name="chapters" id="chapters" min="0" 
                                       value="<?php echo \$userStatus ? \$userStatus['chapters'] : 0; ?>"
                                       onchange="autoSave()">
                            </div>
                            
                            <div class="control-group">
                                <label for="rating">Voto (1-10):</label>
                                <input type="number" name="rating" id="rating" min="1" max="10" step="0.1"
                                       value="<?php echo \$userStatus && \$userStatus['rating'] ? \$userStatus['rating'] : ''; ?>"
                                       onchange="autoSave()">
                            </div>
                            
                            <button type="submit" class="save-btn">Save</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <div class="recommendations-section">
                <h2 class="recommendations-title">Manga Raccomandati</h2>
                <div class="recommendations-grid">
                    <?php if (\$recommendedResult && \$recommendedResult->num_rows > 0): ?>
                        <?php while (\$recommended = \$recommendedResult->fetch_assoc()): ?>
                            <?php 
                                \$recTitleSlug = strtolower(str_replace(' ', '_', \$recommended['title'])); 
                                \$recPageUrl = \$recTitleSlug . ".php";
                            ?>
                            <div class="recommendation-item" onclick="window.location.href='<?php echo \$recPageUrl; ?>'">
                                <img src="../<?php echo \$recommended['image_url']; ?>" alt="<?php echo htmlspecialchars(\$recommended['title']); ?>">
                                <div class="recommendation-title"><?php echo htmlspecialchars(\$recommended['title']); ?></div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-recommendations">Nessun manga raccomandato disponibile al momento.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
            function autoSave() {
                const form = document.getElementById('manga-status-form');
                if (form) {
                    const saveBtn = document.querySelector('.save-btn');
                    saveBtn.textContent = 'Saving...';
                    saveBtn.style.backgroundColor = '#ffc107';
                    
                    form.submit();
                }
            }
            
            document.getElementById('status')?.addEventListener('change', function() {
                const userControls = document.querySelector('.user-controls');
                userControls.className = userControls.className.replace(/status-\\w+/, '');
                userControls.classList.add('status-' + this.value);
            });
            
            function adjustForMobile() {
                if (window.innerWidth <= 768) {
                    const grid = document.querySelector('.recommendations-grid');
                    if (grid) {
                        grid.style.flexDirection = 'column';
                        grid.style.alignItems = 'center';
                    }
                }
            }
            
            window.addEventListener('resize', adjustForMobile);
            adjustForMobile();
        </script>
    </body>
</html>
PHP;
                
                if (file_put_contents($filePath, $pageContent) !== false) {
                    error_log("Manga approved and page created successfully: $title");
                    
                    if ($submitted_by) {
                        $approval_message = "Great news! Your manga '$manga_title' has been approved and is now live on the site.";
                        if (notifyUserAboutMangaStatus($conn, $submitted_by, 'manga_approved', $manga_title, $approval_message, $mangaId)) {
                            error_log("Approval notification sent to user ID: $submitted_by");
                        } else {
                            error_log("Failed to send approval notification to user ID: $submitted_by");
                        }
                    }
                    
                    $deleteNotifQuery = "DELETE FROM notifications WHERE manga_id = ? AND type = 'manga_pending'";
                    $deleteStmt = $conn->prepare($deleteNotifQuery);
                    $deleteStmt->bind_param("i", $mangaId);
                    $deleteStmt->execute();
                    
                } else {
                    error_log("Error creating the dedicated page for: $title");
                }
            } else {
                error_log("Error approving manga: " . $conn->error);
            }
        } else {
            error_log("Error fetching manga details for ID: $mangaId");
        }
    }
    
    $queryCheck = "SELECT COUNT(*) AS pending_count FROM manga WHERE approved = 0";
    $resultCheck = $conn->query($queryCheck);
    $rowCheck = $resultCheck->fetch_assoc();
    $conn->close();

    if ($rowCheck['pending_count'] > 0) {
        header("Location: ../pending");
    } else {
        header("Location: redirect.php");
    }
    exit;
?>