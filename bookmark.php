<?php
    require_once 'php/index.php';
    $_SESSION['current_path'] = $_SERVER['PHP_SELF'];
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mangas</title>
        <link rel="icon" href="images/icon.png" type="image/x-icon">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto|Varela+Round">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
        <link rel="stylesheet" href="CSS/manga.css">
        <link rel="stylesheet" href="CSS/navbar.css">
        <link rel="stylesheet" href="CSS/notifications.css">
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
        <script src="JS/user.js"></script>
        <script src="JS/search.js"></script>
        <script src="JS/notifications.js"></script>
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
                                Approval
                            </a>
                        <?php endif; ?>
                        <a href="php/redirect.php" onclick="logout()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="logout-icon">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" x2="9" y1="12" y2="12"></line>
                            </svg>
                            Log Out
                        </a>
                    </div>
                </div>
                </div>
                <div class="hamburger" onclick="toggleMobileMenu()">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
        <div id="mobileMenu" class="mobile-menu">
            <div class="mobile-search-container">
                <input type="text" id="mobile-search-input" placeholder="Search manga..." onkeyup="searchManga()" autocomplete="off" />
                <svg class="search-icon" viewBox="0 0 24 24">
                    <path d="M10 6.5C10 8.433 8.433 10 6.5 10C4.567 10 3 8.433 3 6.5C3 4.567 4.567 3 6.5 3C8.433 3 10 4.567 10 6.5ZM9.30884 10.0159C8.53901 10.6318 7.56251 11 6.5 11C4.01472 11 2 8.98528 2 6.5C2 4.01472 4.01472 2 6.5 2C8.98528 2 11 4.01472 11 6.5C11 7.56251 10.6318 8.53901 10.0159 9.30884L12.8536 12.1464C13.0488 12.3417 13.0488 12.6583 12.8536 12.8536C12.6583 13.0488 12.3417 13.0488 12.1464 12.8536L9.30884 10.0159Z"></path>
                </svg>
            </div>

            <a href="" class="nav-link" onclick="closeMobileMenu()">Home</a>
            <a href="bookmark" class="nav-link" onclick="closeMobileMenu()">Bookmarks</a>
            <a href="comics" class="nav-link" onclick="closeMobileMenu()">Comics</a>
            
            <div class="mobile-user-controls">
                <a href="#" class="nav-link" onclick="closeMobileMenu()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; margin-right: 8px;">
                        <path d="M10.268 21a2 2 0 0 0 3.464 0"></path>
                        <path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326"></path>
                    </svg>
                    Notifications
                </a>
                
                <?php if (isset($_SESSION['logged_in']) && isset($_SESSION['username'])): ?>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                        <a href="pending" class="nav-link" onclick="closeMobileMenu()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; margin-right: 8px;">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            Pending Manga
                        </a>
                    <?php endif; ?>
                    
                    <a href="#" class="nav-link" onclick="logout(); closeMobileMenu();">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; margin-right: 8px;">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" x2="9" y1="12" y2="12"></line>
                        </svg>
                        Log Out
                    </a>
                <?php else: ?>
                    <a href="login" class="nav-link" onclick="closeMobileMenu()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; margin-right: 8px;">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                            <polyline points="10 17 15 12 10 7"></polyline>
                            <line x1="15" x2="3" y1="12" y2="12"></line>
                        </svg>
                        Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div id="notification-container" class="notification-container"></div>
        <div class="manga">
            <div class="manga-container">
                <div class="left-column">
                    <div class="popular-manga-container">
                        <h3 class="manga-title">BOOKMARK</h3>
                        <div class="divider"></div>
                        <div class="manga-popular-list">
                            <?php if (isset($bookmarks['error'])): ?>
                                <div class="error-message">
                                    <?php echo htmlspecialchars($bookmarks['error']); ?>
                                </div>
                            <?php elseif (empty($bookmarks)): ?>
                                <div class="manga-item-fake">
                                    <p>You haven't added any manga to your list yet!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($bookmarks as $manga): ?>
                                    <div class="manga-item" onclick="window.location.href='series/<?php echo strtolower(str_replace(' ', '_', $manga['title'])); ?>.php'">
                                        <img src="<?php echo htmlspecialchars($manga['image_url']); ?>" alt="<?php echo htmlspecialchars($manga['title']); ?>">
                                        <h3><?php echo htmlspecialchars($manga['title']); ?></h3>
                                        <div class="manga-details">
                                            <?php if ($manga['rating']): ?>
                                                <span class="rating">★ <?php echo number_format($manga['rating'], 1); ?></span>
                                            <?php endif; ?>
                                            <span class="status"><?php 
                                                switch($manga['status']) {
                                                    case 'reading':
                                                        echo 'Reading';
                                                        break;
                                                    case 'completed':
                                                        echo 'Completed';
                                                        break;
                                                    case 'plan_to_read':
                                                        echo 'Plan to read';
                                                        break;
                                                    case 'dropped':
                                                        echo 'Dropped';
                                                        break;
                                                }
                                            ?></span>
                                            <?php if ($manga['chapters']): ?>
                                                <span class="chapters">Ch: <?php echo $manga['chapters']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
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
        <div id="add-manga-popup" class="popup">
            <div class="popup-content">
                <span class="close-btn" onclick="closeAddMangaPopup()">&times;</span>
                <h5>ADD NEW MANGA</h5>
                <form id="add-manga-form" method="post" action="php/add_manga.php" enctype="multipart/form-data" autocomplete="off">
                    <label for="manga-title">TITLE:</label>
                    <input type="text" id="manga-title" name="manga-title" placeholder="Title" required>
                    
                    <label for="manga-image">UPLOAD IMAGE:</label>
                    <input type="file" id="manga-image" name="manga-image" accept="image/*" required>
                    
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
            function toggleMobileMenu() {
                const mobileMenu = document.getElementById('mobileMenu');
                const hamburger = document.querySelector('.hamburger');
                
                mobileMenu.classList.toggle('active');
                hamburger.classList.toggle('active');
                
                if (mobileMenu.classList.contains('active')) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = 'auto';
                }
            }

            function closeMobileMenu() {
                const mobileMenu = document.getElementById('mobileMenu');
                const hamburger = document.querySelector('.hamburger');
                
                mobileMenu.classList.remove('active');
                hamburger.classList.remove('active');
                document.body.style.overflow = 'auto';
            }

            document.addEventListener('click', function(event) {
                const mobileMenu = document.getElementById('mobileMenu');
                const hamburger = document.querySelector('.hamburger');
                
                if (mobileMenu.classList.contains('active') && 
                    !mobileMenu.contains(event.target) && 
                    !hamburger.contains(event.target)) {
                    closeMobileMenu();
                }
            });

            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    closeMobileMenu();
                }
            });

            document.getElementById('mobile-search-input').addEventListener('input', function() {
                const desktopSearch = document.getElementById('search-input');
                desktopSearch.value = this.value;
                searchManga();
            });
        </script>
    </body>
</html>