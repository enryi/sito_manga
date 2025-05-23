<?php
    session_start();
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
        $query = "UPDATE manga SET description = '$description', approved = 1 WHERE id = $mangaId";
        if ($conn->query($query) === TRUE) {
            $mangaQuery = "SELECT title, image_url, description FROM manga WHERE id = $mangaId";
            $mangaResult = $conn->query($mangaQuery);
            if ($mangaResult && $mangaResult->num_rows > 0) {
                $manga = $mangaResult->fetch_assoc();
                $title = htmlspecialchars($manga['title'], ENT_QUOTES, 'UTF-8');
                $imageUrl = htmlspecialchars($manga['image_url'], ENT_QUOTES, 'UTF-8');
                $description = htmlspecialchars($manga['description'], ENT_QUOTES, 'UTF-8');
                $filename = strtolower(str_replace(' ', '_', $title)) . '.php';
                $filePath = "../series/$filename";
                if (!is_dir("../series")) {
                    mkdir("../series", 0777, true);
                }
                $pageContent = <<<'PHP'
                <?php
                    require_once '../php/index.php';
                    $_SESSION['current_path'] = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');
                ?>
                <!DOCTYPE html>
                <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Mangas</title>
                        <link rel="icon" href="../images/icon.png" type="image/x-icon">
                        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto|Varela+Round">
                        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
                        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
                        <link rel="stylesheet" href="../CSS/manga.css">
                        <link rel="stylesheet" href="../CSS/navbar.css">
                        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
                        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
                        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
                        <script src="../JS/user.js"></script>
                        <script src="../JS/search.js"></script>
                    </head>
                    <body style="background-color: #181A1B; color: #fff; font-family: 'Roboto', sans-serif;">
                        <div class="navbar">
                            <div class="navbar-container">
                                <div class="logo-container">
                                    <a href="https://enryi.23hosts.com">
                                        <img src="../images/icon.png" alt="Logo" class="logo" />
                                    </a>
                                    <div class="nav-links">
                                        <a href="https://enryi.23hosts.com" class="nav-link">Home</a>
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
                                        $user_icon = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] ? "../images/admin.png" : "../images/user.svg";
                                    ?>
                                    <img src="<?php echo $user_icon; ?>" alt="User Icon" class="user-icon" onclick="toggleUserMenu()" />
                                    <div id="user-dropdown" class="user-dropdown">
                                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                                            <a href="../pending" class="pending-manga">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="approval-icon">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                                Approvazione
                                            </a>
                                        <?php endif; ?>
                                        <a href="https://enryi.23hosts.com" onclick="logout()">
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
                        </div>
                        <div id="add-manga-popup" class="popup">
                            <div class="popup-content">
                                <span class="close-btn" onclick="closeAddMangaPopup()">&times;</span>
                                <h5>ADD NEW MANGA</h5>
                                <form id="add-manga-form" method="post" action="../php/add_manga.php" enctype="multipart/form-data" autocomplete="off">
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
                    </body>
                </html>
                PHP;
                if (file_put_contents($filePath, $pageContent) !== false) {
                    echo "Manga approved and dedicated page created successfully: <a href='$filePath'>$title</a>";
                } else {
                    echo "Error creating the dedicated page.";
                }
            } else {
                echo "Error fetching manga details.";
            }
        } else {
            echo "Error: " . $conn->error;
        }
    }
    $queryCheck = "SELECT COUNT(*) AS pending_count FROM manga WHERE approved = 0";
    $resultCheck = $conn->query($queryCheck);
    if (!$resultCheck) {
        die("Error executing check query: " . $conn->error);
    }
    $rowCheck = $resultCheck->fetch_assoc();
    $conn->close();

    if ($rowCheck['pending_count'] > 0) {
        header("Location: ../pending");
    } else {
        header("Location: https://enryi.23hosts.com");
    }
    exit;
?>