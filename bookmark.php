<?php
    require_once 'php/index.php';
    $_SESSION['current_path'] = $_SERVER['PHP_SELF'];
    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        header('Location: php/redirect.php');
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Bookmarks</title>
        <link rel="icon" href="images/icon.png" type="image/x-icon">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto|Varela+Round">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
        <link rel="stylesheet" href="CSS/manga.css">
        <link rel="stylesheet" href="CSS/navbar.css">
        <link rel="stylesheet" href="CSS/search.css">
        <link rel="stylesheet" href="CSS/notifications.css">
        <link rel="stylesheet" href="CSS/bookmark.css">
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
        <script src="JS/user.js"></script>
        <script src="JS/search.js"></script>
        <script src="JS/notifications.js"></script>
        <script src="JS/bookmark-filters.js"></script>
        <script src="JS/upload-notifications.js"></script>   
        <style>
            .empty-state {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-align: center;
                padding: 4rem 2rem;
                min-height: 400px;
                background: rgba(255, 255, 255, 0.02);
                border-radius: 12px;
                border: 1px solid rgba(255, 255, 255, 0.05);
                margin: 2rem 0;
            }

            .empty-state-icon {
                margin-bottom: 1.5rem;
                opacity: 0.6;
                color: #666;
            }

            .empty-state-icon svg {
                width: 64px;
                height: 64px;
            }

            .empty-state-title {
                font-size: 1.5rem;
                font-weight: 600;
                color: #fff;
                margin-bottom: 1rem;
                text-align: center;
            }

            .empty-state-message {
                font-size: 1rem;
                color: #aaa;
                margin-bottom: 2rem;
                max-width: 400px;
                line-height: 1.6;
                text-align: center;
            }

            .empty-state-action {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.75rem 1.5rem;
                background: linear-gradient(135deg, #007bff, #0056b3);
                color: white;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 500;
                transition: all 0.3s ease;
                border: none;
                cursor: pointer;
                font-size: 0.95rem;
            }

            .empty-state-action:hover {
                background: linear-gradient(135deg, #0056b3, #004085);
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
                text-decoration: none;
                color: white;
            }

            .empty-state-action:focus {
                outline: none;
                box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.4);
            }

            /* Responsive adjustments */
            @media (max-width: 768px) {
                .empty-state {
                    padding: 3rem 1.5rem;
                    min-height: 300px;
                }
                
                .empty-state-icon svg {
                    width: 48px;
                    height: 48px;
                }
                
                .empty-state-title {
                    font-size: 1.25rem;
                }
                
                .empty-state-message {
                    font-size: 0.9rem;
                }
                
                .empty-state-action {
                    padding: 0.6rem 1.2rem;
                    font-size: 0.9rem;
                }
            }

            /* Dark theme adjustments */
            @media (prefers-color-scheme: dark) {
                .empty-state {
                    background: rgba(255, 255, 255, 0.03);
                    border-color: rgba(255, 255, 255, 0.08);
                }
                
                .empty-state-icon {
                    color: #777;
                }
            }

            /* Animation for when empty state appears */
            .empty-state {
                animation: fadeInUp 0.5s ease-out;
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            /* Stili per il lazy loading */
            .loading-indicator {
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 2rem;
                margin-top: 1rem;
            }

            .loading-spinner {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 0.5rem;
                color: #fff;
            }

            .spinner {
                width: 32px;
                height: 32px;
                border: 3px solid rgba(255, 255, 255, 0.1);
                border-top: 3px solid #007bff;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            .loading-spinner span {
                font-size: 0.9rem;
                color: rgba(255, 255, 255, 0.7);
            }

            /* Ottimizzazioni per le performance */
            .manga-list-item {
                will-change: transform;
                transform: translateZ(0);
                opacity: 0;
                animation: fadeInUp 0.3s ease forwards;
            }

            .manga-list-item img {
                will-change: transform;
                transform: translateZ(0);
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .scroll-sentinel {
                height: 1px !important;
                visibility: hidden !important;
                pointer-events: none !important;
            }

            @media (max-width: 768px) {
                .loading-indicator {
                    padding: 1rem;
                }
                
                .spinner {
                    width: 24px;
                    height: 24px;
                    border-width: 2px;
                }
            }
        </style>
    </head>
    <body style="background-color: #181A1B; color: #fff; font-family: 'Roboto', sans-serif;">
        <?php
            require_once 'php/get_bookmarks.php';
            if (!isset($_SESSION['user_id'])) {
                header('Location: php/redirect.php');
                exit();
            }
            $bookmarks = getUserBookmarks($_SESSION['user_id']);
        ?>
        <div class="navbar">
            <div class="navbar-container">
                <div class="logo-container">
                    <a href="php/redirect.php">
                        <img src="images/icon.png" alt="Logo" class="logo" />
                    </a>
                    <div class="nav-links">
                        <a href="php/redirect.php" class="nav-link">Home</a>
                        <a href="bookmark" class="nav-link">Bookmarks</a>
                        <a href="comics" class="nav-link">Comics</a>
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
                                <a href="pending" class="pending-manga">
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
                        <a href="login" class="login-button">
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
        
        <div class="bookmark-page">
            <div class="bookmark-container">
                <h1 class="bookmark-title">MY MANGA LIST</h1>
                
                <!-- Filters Section -->
                <div class="filters-section">
                    <div class="status-filters">
                        <button class="filter-btn active" data-status="all">All</button>
                        <button class="filter-btn" data-status="reading">Reading</button>
                        <button class="filter-btn" data-status="completed">Completed</button>
                        <button class="filter-btn" data-status="plan_to_read">Plan to Read</button>
                        <button class="filter-btn" data-status="on_hold">On Hold</button>
                        <button class="filter-btn" data-status="dropped">Dropped</button>
                    </div>
                    
                    <div class="sort-filters">
                        <select id="sort-select" class="sort-dropdown">
                            <option value="title_asc">Title (A-Z)</option>
                            <option value="title_desc">Title (Z-A)</option>
                            <option value="score_desc">Score (High to Low)</option>
                            <option value="score_asc">Score (Low to High)</option>
                            <option value="chapters_desc">Chapters (High to Low)</option>
                            <option value="chapters_asc">Chapters (Low to High)</option>
                        </select>
                    </div>
                </div>

                <!-- Stats Section -->
                <div class="stats-section">
                    <div class="stat-item">
                        <span class="stat-number" id="total-manga">0</span>
                        <span class="stat-label">Total Entries</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="total-chapters">0</span>
                        <span class="stat-label">Chapters Read</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="avg-score">0.0</span>
                        <span class="stat-label">Average Score</span>
                    </div>
                </div>

                <!-- Manga List -->
                <div class="manga-list-section">
                    <?php if (isset($bookmarks['error'])): ?>
                        <div class="error-message">
                            <?php echo htmlspecialchars($bookmarks['error']); ?>
                        </div>
                    <?php elseif (empty($bookmarks)): ?>
                        <div class="no-bookmarks">
                            <p>You haven't added any manga to your list yet!</p>
                            <a href="comics" class="add-manga-btn">Browse Manga</a>
                        </div>
                    <?php else: ?>
                        <!-- Il container sarà popolato via JavaScript per il lazy loading -->
                        <div class="manga-list" id="manga-list">
                            <!-- I manga verranno caricati dinamicamente qui -->
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div id="add-manga-popup" class="popup">
            <div class="popup-content">
                <span class="close-btn" onclick="closeAddMangaPopup()">&times;</span>
                <h5>ADD NEW MANGA</h5>
                <form id="add-manga-form" method="post" action="php/add_manga.php" enctype="multipart/form-data" autocomplete="off">
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
            // Pass PHP data to JavaScript
            <?php if (!isset($bookmarks['error']) && !empty($bookmarks)): ?>
                window.mangaData = <?php echo json_encode($bookmarks); ?>;
            <?php else: ?>
                window.mangaData = [];
            <?php endif; ?>
        </script>
    </body>
</html>