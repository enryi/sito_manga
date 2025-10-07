<?php
    require_once 'php/session.php';
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
        <link rel="stylesheet" href="CSS/search.css">
        <link rel="stylesheet" href="CSS/pending.css">
        <link rel="stylesheet" href="CSS/navbar.css">
        <link rel="stylesheet" href="CSS/notifications.css">
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
        <script src="JS/user.js"></script>
        <script src="JS/search.js"></script>
        <script src="JS/notifications.js"></script>
        <script src="JS/upload-notifications.js"></script>
        <script src="JS/settings.js"></script>
        <script src="JS/auth-notifications.js"></script>
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
                        $user_pfp = null;
                        if (isset($_SESSION['user_id'])) {
                            $stmt = $conn->prepare("SELECT pfp FROM users WHERE id = ?");
                            $stmt->bind_param("i", $_SESSION['user_id']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($row = $result->fetch_assoc()) {
                                $user_pfp = $row['pfp'];
                            }
                            $stmt->close();
                        }
                        
                        $has_custom_pfp = $user_pfp && file_exists($user_pfp);
                        $is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
                    ?>
                    
                    <div class="user-profile-container">
                        <?php if ($has_custom_pfp): ?>
                            <img src="<?php echo htmlspecialchars($user_pfp); ?>" 
                                alt="Profile Picture" 
                                class="user-icon user-pfp <?php echo $is_admin ? 'admin' : ''; ?>" 
                                onclick="toggleUserMenu()" />
                        <?php else: ?>
                            <div class="user-icon user-pfp default-avatar <?php echo $is_admin ? 'admin' : ''; ?>" 
                                onclick="toggleUserMenu()">
                                <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                                    <defs>
                                        <linearGradient id="avatarGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" style="stop-color:#5a5a5a;stop-opacity:1" />
                                            <stop offset="100%" style="stop-color:#3a3a3a;stop-opacity:1" />
                                        </linearGradient>
                                    </defs>
                                    <circle cx="50" cy="50" r="50" fill="url(#avatarGradient)"/>
                                    
                                    <g fill="#e0e0e0">
                                        <circle cx="50" cy="35" r="15"/>
                                        
                                        <path d="M20 85 C20 68, 32 58, 50 58 C68 58, 80 68, 80 85 L80 100 L20 100 Z"/>
                                    </g>
                                </svg>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($is_admin): ?>
                            <div class="admin-gear-badge">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.22,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.22,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.68 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div id="user-dropdown" class="user-dropdown">
                        <?php if ($is_admin): ?>
                            <a href="pending" class="pending-manga">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="approval-icon">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                Approvazione
                            </a>
                        <?php endif; ?>
                        <a href="javascript:void(0);" onclick="openSettingsPopup(event)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="settings-icon">
                                <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            Settings
                        </a>
                        <a href="#" onclick="logout(); return false;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="logout-icon">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" x2="9" y1="12" y2="12"></line>
                            </svg>
                            Log Out
                        </a>
                    </div>
                    
                    <div id="settings-popup" class="popup">
                        <div class="popup-content settings-popup-content">
                            <span class="close-btn" onclick="closeSettingsPopup()">&times;</span>
                            <h5>USER SETTINGS</h5>
                            
                            <div class="settings-tabs">
                                <button class="settings-tab active" onclick="showSettingsTab('profile')">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    Profile
                                </button>
                                <button class="settings-tab" onclick="showSettingsTab('security')">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                    </svg>
                                    Security
                                </button>
                            </div>
                            
                            <div id="profile-tab" class="settings-tab-content active">
                                <div class="settings-section">
                                    <h6>Profile Picture</h6>
                                    <div class="current-pfp-display">
                                        <?php if ($has_custom_pfp): ?>
                                            <img src="<?php echo htmlspecialchars($user_pfp); ?>" alt="Current Profile" class="current-pfp-img" />
                                        <?php else: ?>
                                            <div class="current-pfp-default">
                                                <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                                                    <defs>
                                                        <linearGradient id="settingsAvatarGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                                            <stop offset="0%" style="stop-color:#5a5a5a;stop-opacity:1" />
                                                            <stop offset="100%" style="stop-color:#3a3a3a;stop-opacity:1" />
                                                        </linearGradient>
                                                    </defs>
                                                    <circle cx="50" cy="50" r="50" fill="url(#settingsAvatarGradient)"/>
                                                    <g fill="#e0e0e0">
                                                        <circle cx="50" cy="35" r="15"/>
                                                        <path d="M20 85 C20 68, 32 58, 50 58 C68 58, 80 68, 80 85 L80 100 L20 100 Z"/>
                                                    </g>
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                        <div class="pfp-actions">
                                            <button type="button" class="btn-secondary small" onclick="changePfp()">Change</button>
                                            <?php if ($has_custom_pfp): ?>
                                                <button type="button" class="btn-danger small" onclick="removePfp()">Remove</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="settings-section">
                                    <h6>Username</h6>
                                    <form id="username-form" onsubmit="updateUsername(event)">
                                        <div class="input-group">
                                            <input type="text" name="new_username" class="form-control" 
                                                value="<?php echo htmlspecialchars($_SESSION['username']); ?>" 
                                                placeholder="New username" required>
                                            <button type="submit" class="btn-primary small">Update</button>
                                        </div>
                                        <small class="form-text">Username must be 3-20 characters, letters and numbers only.</small>
                                    </form>
                                </div>
                                
                                <div class="settings-section">
                                    <h6>Account Information</h6>
                                    <div class="account-info">
                                        <div class="info-item">
                                            <span class="info-label">Member since:</span>
                                            <span class="info-value" id="member-since">Loading...</span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Total manga uploads:</span>
                                            <span class="info-value" id="manga-count">Loading...</span>
                                        </div>
                                        <?php if ($is_admin): ?>
                                        <div class="info-item admin-badge">
                                            <span class="info-label">Account type:</span>
                                            <span class="info-value admin-text">Administrator</span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="security-tab" class="settings-tab-content">
                                <div class="settings-section">
                                    <h6>Change Password</h6>
                                    <form id="password-form" onsubmit="updatePassword(event)">
                                        <div class="password-field">
                                            <label>Current Password</label>
                                            <input type="password" name="current_password" class="form-control" required>
                                        </div>
                                        <div class="password-field">
                                            <label>New Password</label>
                                            <input type="password" name="new_password" id="new-password" class="form-control" required>
                                            <div id="password-strength-indicator" class="password-strength"></div>
                                        </div>
                                        <div class="password-field">
                                            <label>Confirm New Password</label>
                                            <input type="password" name="confirm_password" class="form-control" required>
                                        </div>
                                        <button type="submit" class="btn-primary">Update Password</button>
                                    </form>
                                </div>
                                
                                <div class="settings-section">
                                    <h6>Login Sessions</h6>
                                    <div class="session-info">
                                        <div class="current-session">
                                            <div class="session-details">
                                                <span class="session-device">Current Session</span>
                                                <span class="session-time">Active now</span>
                                            </div>
                                            <span class="session-badge current">Current</span>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-danger small" onclick="logoutAllSessions()">Logout All Other Sessions</button>
                                </div>
                                <div class="settings-section danger-zone">
                                    <h6>Danger Zone</h6>
                                    <div class="danger-actions">
                                        <button type="button" class="btn-danger" onclick="clearAllData()">Clear All Reading Data</button>
                                        <button type="button" class="btn-danger" onclick="deleteAccount()">Delete Account</button>
                                    </div>
                                    <small class="form-text danger-text">These actions cannot be undone.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="pfp-upload-modal" class="popup" style="display: none;">
                        <div class="popup-content pfp-modal-content">
                            <span class="close-btn" onclick="closePfpModal()">&times;</span>
                            <h5>Update Profile Picture</h5>
                            <div class="pfp-upload-area" onclick="document.getElementById('pfpFileInput').click()">
                                <input type="file" id="pfpFileInput" accept="image/*" style="display: none;" onchange="previewPfp(event)">
                                <div class="pfp-preview-area">
                                    <img id="pfpPreviewImage" style="display: none;">
                                    <div id="pfpPlaceholder" class="pfp-placeholder">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="7,10 12,15 17,10"></polyline>
                                            <line x1="12" x2="12" y1="15" y2="3"></line>
                                        </svg>
                                        <p>Click to select an image</p>
                                    </div>
                                </div>
                                <div class="crop-controls" id="cropControls" style="display: none;">
                                    <label>Zoom:</label>
                                    <input type="range" id="cropZoom" min="1" max="3" step="0.1" value="1" oninput="updateCrop()">
                                </div>
                            </div>
                            <div class="pfp-modal-actions">
                                <button type="button" class="btn-secondary" onclick="closePfpModal()">Cancel</button>
                                <button type="button" class="btn-primary" onclick="savePfp()" disabled id="savePfpBtn">Save</button>
                            </div>
                        </div>
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