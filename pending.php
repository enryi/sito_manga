<?php
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
    $queryCheck = "SELECT COUNT(*) AS pending_count FROM manga WHERE approved = 0";
    $resultCheck = $conn->query($queryCheck);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Pending Manga</title>
        <link rel="icon" href="images/icon.png" type="image/x-icon">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto|Varela+Round">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
        <link rel="stylesheet" href="CSS/user.css">
        <link rel="stylesheet" href="CSS/pending.css">
        <link rel="stylesheet" href="CSS/navbar.css">
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
        <script src="JS/user.js"></script>
        <script src="JS/search.js"></script>
        <script src="JS/notifications.js"></script>
        <style>
            .reason-modal {
                display: none;
                position: fixed;
                z-index: 1000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgba(0,0,0,0.4);
            }
            .reason-modal-content {
                background-color: #2a2a2a;
                margin: 15% auto;
                padding: 20px;
                border: 1px solid #444;
                border-radius: 8px;
                width: 50%;
                max-width: 500px;
                color: #fff;
            }
            .reason-close {
                color: #aaa;
                float: right;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
            }
            .reason-close:hover,
            .reason-close:focus {
                color: #fff;
                text-decoration: none;
            }
            .reason-textarea {
                width: 100%;
                min-height: 100px;
                background-color: #333;
                border: 1px solid #555;
                border-radius: 4px;
                color: #fff;
                padding: 10px;
                margin: 10px 0;
                resize: vertical;
            }
            .reason-buttons {
                text-align: right;
                margin-top: 15px;
            }
            .reason-buttons button {
                margin-left: 10px;
            }
        </style>
        <script>
            function toggleDescription(button) {
                const mangaItem = button.closest('.manga-item');
                const description = mangaItem.querySelector('.manga-description');
                if (description.classList.contains('full')) {
                    description.classList.remove('full');
                    button.textContent = 'Read More';
                } else {
                    description.classList.add('full');
                    button.textContent = 'Read Less';
                }
            }
            function openMangaPopup(mangaId) {
                document.getElementById('manga-popup-' + mangaId).style.display = 'block';
            }
            function closeMangaPopup(mangaId) {
                document.getElementById('manga-popup-' + mangaId).style.display = 'none';
            }

            function openReasonModal(mangaId, mangaTitle) {
                document.getElementById('reasonModal').style.display = 'block';
                document.getElementById('reasonMangaId').value = mangaId;
                document.getElementById('reasonMangaTitle').textContent = mangaTitle;
                document.getElementById('reasonText').value = '';
            }
            
            function closeReasonModal() {
                document.getElementById('reasonModal').style.display = 'none';
            }
            
            function submitDisapproval() {
                const mangaId = document.getElementById('reasonMangaId').value;
                const reason = document.getElementById('reasonText').value.trim();
                
                if (!reason) {
                    alert('Please provide a reason for disapproval.');
                    return;
                }
                
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'php/disapprove_manga.php';
                
                const mangaIdInput = document.createElement('input');
                mangaIdInput.type = 'hidden';
                mangaIdInput.name = 'manga_id';
                mangaIdInput.value = mangaId;
                
                const reasonInput = document.createElement('input');
                reasonInput.type = 'hidden';
                reasonInput.name = 'reason';
                reasonInput.value = reason;
                
                form.appendChild(mangaIdInput);
                form.appendChild(reasonInput);
                document.body.appendChild(form);
                form.submit();
            }

            window.onclick = function(event) {
                const modal = document.getElementById('reasonModal');
                if (event.target == modal) {
                    closeReasonModal();
                }
            }
        </script>
    </head>
    <body style="background-color: #181A1B; color: #fff; font-family: 'Roboto', sans-serif;">
        <div class="navbar">
            <div class="navbar-container">
                <div class="logo-container">
                    <a href="php/redirect.php">
                        <img src="images/icon.png" alt="Logo" class="logo" />
                    </a>
                    <div class="nav-links">
                        <a href="php/redirect.php" class="nav-link">Home</a>
                        <a href="bookmarks" class="nav-link">Bookmarks</a>
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
        </div>
        <div class="container">
            <h1 style="margin-top: 10px;">Pending Manga</h1>
                <?php 
                    $query = "SELECT m.id, m.title, m.image_url, m.description, m.author, m.type, m.genre, m.submitted_by, u.username as submitter_name
                    FROM manga m
                    LEFT JOIN users u ON m.submitted_by = u.id
                    WHERE m.approved = 0 
                    ORDER BY m.created_at DESC;";
                    $result = $conn->query($query);
                    if ($result && $result->num_rows > 0) {
                        echo '<div class="manga-list">';
                        while ($row = $result->fetch_assoc()) {
                            $mangaId = htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8');
                            $mangaTitle = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');
                            $mangaImage = htmlspecialchars($row['image_url'], ENT_QUOTES, 'UTF-8');
                            $mangaDescription = htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8');
                            $mangaAuthor = htmlspecialchars($row['author'], ENT_QUOTES, 'UTF-8');
                            $mangaType = htmlspecialchars($row['type'], ENT_QUOTES, 'UTF-8');
                            $mangaGenre = htmlspecialchars($row['genre'], ENT_QUOTES, 'UTF-8');
                            $submitterName = htmlspecialchars($row['submitter_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8');
                            
                            echo '<div class="manga-item" onclick="openMangaPopup(' . $mangaId . ')">';
                            echo '<img src="' . $mangaImage . '" alt="' . $mangaTitle . '">';
                            echo '<div class="manga-title">' . $mangaTitle . '</div>';
                            echo '<div class="submitter-info">Submitted by: ' . $submitterName . '</div>';
                            echo '</div>';
                            
                            echo '<div id="manga-popup-' . $mangaId . '" class="popup" style="display: none;">';
                            echo '<div class="popup-content">';
                            echo '<span class="close-btn" onclick="closeMangaPopup(' . $mangaId . ')">&times;</span>';
                            echo '<div style="display: flex; align-items: center;">';
                            echo '<img src="' . $mangaImage . '" alt="' . $mangaTitle . '" style="width: 10%; border-radius: 8px; margin-right: 15px;">';
                            echo '<div>';
                            echo '<h4 style="margin-bottom: 10px; font-size: 1.5rem; background: none; border: none; outline: none;" contenteditable="true" spellcheck="false">' . $mangaTitle . '</h4>';
                            echo '<div style="font-size: 0.9em; color: #888; margin-bottom: 10px;">Submitted by: ' . $submitterName . '</div>';
                            echo '<hr style="border: 1px solid #ccc; margin: 10px 0;">';
                            echo '<div style="display: flex; align-items: center; margin-bottom: 3px;">';
                            echo '<strong style="min-width: 45px;">Author:</strong>';
                            echo '<div contenteditable="true" spellcheck="false" style="flex: 1; color: #fff; background: none; border: none; white-space: pre-wrap; word-wrap: break-word; outline: none; margin-left: 5px;">' . $mangaAuthor . '</div>';
                            echo '</div>';
                            echo '<div style="display: flex; align-items: center; margin-bottom: 3px;">';
                            echo '<strong style="min-width: 45px;">Genre:</strong>';
                            echo '<div contenteditable="true" spellcheck="false" style="flex: 1; color: #fff; background: none; border: none; white-space: pre-wrap; word-wrap: break-word; outline: none; margin-left: 5px;">' . $mangaGenre . '</div>';
                            echo '</div>';
                            echo '<div style="display: flex; align-items: center; margin-bottom: 3px;">';
                            echo '<strong style="min-width: 45px; margin-right: 0px;">Type:</strong>';
                            echo '<div contenteditable="true" spellcheck="false" style="color: #fff; background: none; border: none; white-space: pre-wrap; word-wrap: break-word; outline: none;">' . $mangaType . '</div>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                            echo '<p style="margin-top:15px; margin-bottom:5px"><strong>Description:</strong></p>';
                            echo '<div contenteditable="true" spellcheck="false" style="width: 100%; color: #fff; background: none; border: none; white-space: pre-wrap; word-wrap: break-word; outline: none;">' . $mangaDescription . '</div>';
                            echo '<div class="approval-buttons">';
                            echo '<form method="post" action="php/approve_manga.php" style="display: inline;">';
                            echo '<input type="hidden" name="manga_id" value="' . $mangaId . '">';
                            echo '<input type="hidden" name="description" id="description-' . $mangaId . '" value="' . $mangaDescription . '">';
                            echo '<input type="hidden" name="author" id="author-' . $mangaId . '" value="' . $mangaAuthor . '">';
                            echo '<input type="hidden" name="type" id="type-' . $mangaId . '" value="' . $mangaType . '">';
                            echo '<input type="hidden" name="genre" id="genre-' . $mangaId . '" value="' . $mangaGenre . '">';
                            echo '<input type="hidden" name="redirect" value="redirect.php">';
                            echo '<button type="submit" class="btn btn-success">Approve</button>';
                            echo '</form>';
                            echo '<button type="button" class="btn btn-danger" style="margin-left: 10px;" onclick="openReasonModal(' . $mangaId . ', \'' . addslashes($mangaTitle) . '\')">Disapprove</button>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        echo '</div>';
                    } else {
                        echo '<p>No pending manga found.</p>';
                    }
                    $conn->close();
                ?>
        </div>

        <div id="reasonModal" class="reason-modal">
            <div class="reason-modal-content">
                <span class="reason-close" onclick="closeReasonModal()">&times;</span>
                <h3>Disapprove Manga</h3>
                <p>You are about to disapprove: <strong id="reasonMangaTitle"></strong></p>
                <p>Please provide a reason for disapproval:</p>
                <textarea id="reasonText" class="reason-textarea" placeholder="Enter the reason for disapproval here..." maxlength="500"></textarea>
                <div class="reason-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeReasonModal()">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="submitDisapproval()">Disapprove</button>
                </div>
                <input type="hidden" id="reasonMangaId" value="">
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
    </body>
</html>