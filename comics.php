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
        <script src="JS/settings.js"></script>
        <script src="JS/auth-notifications.js"></script>
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
                        // Check if user has a profile picture
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
                        
                        // Check if user has a custom profile picture
                        $has_custom_pfp = $user_pfp && file_exists($user_pfp);
                        $is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
                    ?>
                    
                    <div class="user-profile-container">
                        <?php if ($has_custom_pfp): ?>
                            <!-- Custom profile picture -->
                            <img src="<?php echo htmlspecialchars($user_pfp); ?>" 
                                alt="Profile Picture" 
                                class="user-icon user-pfp <?php echo $is_admin ? 'admin' : ''; ?>" 
                                onclick="toggleUserMenu()" />
                        <?php else: ?>
                            <!-- Default SVG profile picture -->
                            <div class="user-icon user-pfp default-avatar <?php echo $is_admin ? 'admin' : ''; ?>" 
                                onclick="toggleUserMenu()">
                                <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                                    <!-- Background circle with gradient -->
                                    <defs>
                                        <linearGradient id="avatarGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" style="stop-color:#5a5a5a;stop-opacity:1" />
                                            <stop offset="100%" style="stop-color:#3a3a3a;stop-opacity:1" />
                                        </linearGradient>
                                    </defs>
                                    <circle cx="50" cy="50" r="50" fill="url(#avatarGradient)"/>
                                    
                                    <!-- Person icon -->
                                    <g fill="#e0e0e0">
                                        <!-- Head -->
                                        <circle cx="50" cy="35" r="15"/>
                                        
                                        <!-- Body/Shoulders -->
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
                    
                    <!-- Settings Popup -->
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
                                <button class="settings-tab" onclick="showSettingsTab('preferences')">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    Preferences
                                </button>
                            </div>
                            
                            <!-- Profile Tab -->
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
                            
                            <!-- Security Tab -->
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
                            </div>
                            
                            <!-- Preferences Tab -->
                            <div id="preferences-tab" class="settings-tab-content">
                                <div class="settings-section">
                                    <h6>Reading Preferences</h6>
                                    <div class="preference-group">
                                        <label class="preference-item">
                                            <input type="checkbox" name="auto_bookmark" checked>
                                            <span class="checkmark"></span>
                                            Auto-bookmark manga when reading
                                        </label>
                                        <label class="preference-item">
                                            <input type="checkbox" name="reading_progress" checked>
                                            <span class="checkmark"></span>
                                            Save reading progress
                                        </label>
                                        <label class="preference-item">
                                            <input type="checkbox" name="mature_content">
                                            <span class="checkmark"></span>
                                            Show mature content
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="settings-section">
                                    <h6>Notifications</h6>
                                    <div class="preference-group">
                                        <label class="preference-item">
                                            <input type="checkbox" name="new_chapters" checked>
                                            <span class="checkmark"></span>
                                            New chapter notifications
                                        </label>
                                        <label class="preference-item">
                                            <input type="checkbox" name="manga_updates" checked>
                                            <span class="checkmark"></span>
                                            Manga status updates
                                        </label>
                                        <label class="preference-item">
                                            <input type="checkbox" name="system_notifications" checked>
                                            <span class="checkmark"></span>
                                            System notifications
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="settings-section">
                                    <h6>Display Settings</h6>
                                    <div class="preference-group">
                                        <div class="preference-item">
                                            <label>Items per page:</label>
                                            <select name="items_per_page" class="form-control small-select">
                                                <option value="12">12</option>
                                                <option value="24" selected>24</option>
                                                <option value="36">36</option>
                                                <option value="48">48</option>
                                            </select>
                                        </div>
                                        <div class="preference-item">
                                            <label>Default sort:</label>
                                            <select name="default_sort" class="form-control small-select">
                                                <option value="newest" selected>Newest First</option>
                                                <option value="oldest">Oldest First</option>
                                                <option value="popular">Most Popular</option>
                                                <option value="rating">Highest Rated</option>
                                            </select>
                                        </div>
                                    </div>
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
                    
                    <!-- Profile Picture Upload Modal -->
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
        <div class="manga">
            <div class="manga-container">
                <div class="left-column">
                    <div class="popular-manga-container">
                    <div class="series-header">
                        <h3 class="manga-title">SERIES LIST</h3>
                        <div class="filter-container">
                            <button class="filter-button" onclick="toggleFilterDropdown()">
                                <!-- Icona filtro (imbuto) creata in stile coerente con il sito -->
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
                            require_once 'php/comics.php';
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