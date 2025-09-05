<?php
    require_once '../php/index.php';
    $_SESSION['current_path'] = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');
    
    $user_status = '';
    $user_score = 0;
    $user_chapters = 0;
    
    // Check if user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $manga_id = 9;
        
        // Prima verifichiamo se la connessione è valida
        if (!$conn) {
            error_log("Database connection not available in solo_leveling.php");
            // Continuiamo con i valori predefiniti
        } else {
            $query = "SELECT status, rating, chapters FROM lista_utente WHERE user_id = ? AND manga_id = ?";
            $stmt = $conn->prepare($query);
            
            if ($stmt === false) {
                error_log("Query preparation failed in solo_leveling.php: " . $conn->error);
                // Continuiamo con i valori predefiniti
            } else {
                if (!$stmt->bind_param("ii", $user_id, $manga_id)) {
                    error_log("Parameter binding failed in solo_leveling.php: " . $stmt->error);
                } else {
                    if ($stmt->execute()) {
                        $result = $stmt->get_result();
                        if ($result && $result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $user_status = $row['status'];
                            $user_score = $row['rating'];  // Changed from 'score' to 'rating'
                            $user_chapters = $row['chapters'];
                        }
                    } else {
                        error_log("Query execution failed in solo_leveling.php: " . $stmt->error);
                    }
                }
                $stmt->close();
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Solo leveling</title>
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
        <style>
            body {
                background-color: #181A1B;
                color: #fff;
                font-family: 'Roboto', sans-serif;
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 1200px;
                margin: 20px auto;
                padding: 20px;
                background-color: #222;
                border-radius: 8px;
            }
            .anime-header {
                display: flex;
                gap: 20px;
            }
            .anime-header img {
                width: 200px;
                border-radius: 8px;
            }
            .anime-details {
                flex: 1;
            }
            .anime-details h1 {
                font-size: 24px;
                margin-bottom: 10px;
            }
            .anime-details .score {
                font-size: 18px;
                margin-bottom: 10px;
            }
            .anime-details .info {
                font-size: 14px;
                margin-bottom: 20px;
            }
            .anime-details .synopsis {
                font-size: 16px;
                line-height: 1.6;
            }
            .user-interaction {
                margin-top: 20px;
                padding: 15px;
                background-color: #333;
                border-radius: 8px;
                display: flex;
                gap: 20px;
                align-items: center;
            }
            .user-interaction label {
                font-size: 14px;
                margin-right: 10px;
            }
            .user-interaction select, .user-interaction input {
                padding: 8px;
                border: none;
                border-radius: 4px;
                background-color: #444;
                color: #fff;
            }
        </style>
        <script>
            function addMangaToList(mangaId) {
                fetch('../php/add_to_list.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ manga_Id: mangaId })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Risposta dal server:', data);
                    if (data.success) {
                        alert('Manga aggiunto alla lista!');
                        location.reload();
                    } else {
                        alert('Errore: ' + data.error);
                    }
                })
                .catch(error => console.error('Errore:', error));
            }
            function updateStatusOrScore() {
                const status = document.getElementById('status').value;
                const score = document.getElementById('score').value;
                const chaptersRead = document.getElementById('chapters-read').value;
                console.log(`Status: ${status}, Score: ${score}, Chapters Read: ${chaptersRead}`);
            }

            function incrementChapters() {
                const chaptersInput = document.getElementById('chapters-read');
                const totalChapters = parseInt(document.getElementById('totalChapters').textContent.split('/')[1]);
                let currentValue = parseInt(chaptersInput.value);
                if (currentValue < totalChapters) {
                    chaptersInput.value = currentValue + 1;
                    updateStatusOrScore();
                }
            }
        </script>
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
        <div class="manga">
            <div class="manga-container">
                <div class="left-column">
                    <div class="anime-header" style="display: flex; gap: 20px; align-items: flex-start; background-color: #222; border: 1px solid #444; border-radius: 8px; padding: 15px;">
                        <div class="anime-image" style="flex: 1; max-width: 250px;">
                            <img src="../uploads/manga/01J3BAXFBTABT3VNAV3RPNZK7S-optimized.webp" alt="Ore dake Level Up na Ken" style="width: 100%; border-radius: 8px; object-fit: cover;">
                        </div>
                        <div class="anime-details" style="flex: 2; margin-left: 20px;">
                            <h1 style="font-size: 24px; margin-bottom: 10px;">Solo leveling</h1>
                            <div class="score" style="font-size: 18px; margin-bottom: 10px;">Score: 8.27</div>
                            <div class="manga-user-interaction" style="display: flex; align-items: center; gap: 20px; margin-top: 20px; background-color: #333; padding: 15px; border-radius: 8px;">
                                <!-- Select per lo status -->
                                <div class="manga-status-container">
                                    <label for="manga-status" style="font-size: 14px; margin-right: 10px;">Status:</label>
                                    <select name="manga-status" id="manga-status" class="manga-form-user-status" onchange="updateMangaStatusOrScore()" style="background-color: #444; color: #fff; border: none; padding: 5px; border-radius: 4px; font-size: 12px; width: 150px;">
                                        <option value="" disabled>Status</option>
                                        <option value="Reading" <?php echo $user_status === 'Reading' ? 'selected' : ''; ?>>Reading</option>
                                        <option value="Completed" <?php echo $user_status === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="Dropped" <?php echo $user_status === 'Dropped' ? 'selected' : ''; ?>>Dropped</option>
                                        <option value="Plan to Read" <?php echo $user_status === 'Plan to Read' ? 'selected' : ''; ?>>Plan to Read</option>
                                    </select>
                                </div>

                                <!-- Select per il punteggio -->
                                <div class="manga-score-container">
                                    <label for="manga-score" style="font-size: 14px; margin-right: 10px;">Score:</label>
                                    <select name="manga-score" id="manga-score" class="manga-form-user-score" onchange="updateMangaStatusOrScore()" style="background-color: #444; color: #fff; border: none; padding: 5px; border-radius: 4px; font-size: 12px; width: 150px;">
                                        <option value="0" <?php echo $user_score == 0 ? 'selected' : ''; ?>>Select</option>
                                        <option value="10" <?php echo $user_score == 10 ? 'selected' : ''; ?>>(10) Masterpiece</option>
                                        <option value="9" <?php echo $user_score == 9 ? 'selected' : ''; ?>>(9) Great</option>
                                        <option value="8" <?php echo $user_score == 8 ? 'selected' : ''; ?>>(8) Very Good</option>
                                        <option value="7" <?php echo $user_score == 7 ? 'selected' : ''; ?>>(7) Good</option>
                                        <option value="6" <?php echo $user_score == 6 ? 'selected' : ''; ?>>(6) Fine</option>
                                        <option value="5" <?php echo $user_score == 5 ? 'selected' : ''; ?>>(5) Average</option>
                                        <option value="4" <?php echo $user_score == 4 ? 'selected' : ''; ?>>(4) Bad</option>
                                        <option value="3" <?php echo $user_score == 3 ? 'selected' : ''; ?>>(3) Very Bad</option>
                                        <option value="2" <?php echo $user_score == 2 ? 'selected' : ''; ?>>(2) Horrible</option>
                                        <option value="1" <?php echo $user_score == 1 ? 'selected' : ''; ?>>(1) Appalling</option>
                                    </select>
                                </div>

                                <!-- Input per i capitoli -->
                                <div class="manga-chapters-container" style="display: flex; align-items: center; gap: 10px;">
                                    <label for="manga-myinfo-watchedeps" style="font-size: 14px;">Chapters:</label>
                                    <input type="text" id="manga-myinfo-watchedeps" name="manga-myinfo-watchedeps" size="3" class="manga-inputtext js-manga-user-episode-seen" value="<?php echo $user_chapters; ?>" data-eps="12" style="width: 50px; background-color: #333; color: #fff; border: none; padding: 5px; border-radius: 4px; font-size: 12px; text-align: center;" onchange="updateMangaStatusOrScore()">
                                    <span id="manga-curEps" data-num="12" style="color: #aaa; font-size: 12px;">/ 200</span>
                                    <a href="javascript:void(0);" class="js-manga-btn-count manga-increase ml4" onclick="incrementChapters()" style="color: #fff; text-decoration: none; font-size: 14px;">
                                        <i class="fa-solid fa-circle-plus"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="synopsis" style="margin-top: 20px; font-size: 14px; line-height: 1.6;">
                                Humanity was caught at a precipice a decade ago when the first gates—portals linked with other dimensions that harbor monsters immune to conventional weaponry—emerged around the world...
                            </div>
                        </div>
                    </div>
                </div>
                <div class="top-manga-container">
                    <h3 class="manga-title">TOP MANGA</h3>
                    <div class="divider"></div>
                    <div class="manga-top-list">
                        <?php
                            require_once 'manga_top.php';
                        ?>
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