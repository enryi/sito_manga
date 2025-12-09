<?php
    require_once __DIR__ . '/../php/session.php';

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];

    if ($host === 'localhost') {
        $base_path = '/enryi';
    } else {
        $base_path = '';
    }

    $base_url = $protocol . '://' . $host . $base_path;
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>503 - Service Unavailable | Mangas</title>
        <link rel="icon" href="<?php echo $base_url; ?>/images/icon.png" type="image/x-icon">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php echo $base_url; ?>/CSS/navbar.css">
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="<?php echo $base_url; ?>/JS/user.js"></script>
        <script src="<?php echo $base_url; ?>/JS/search.js"></script>
        <script src="<?php echo $base_url; ?>/JS/notifications.js"></script>
        <script src="<?php echo $base_url; ?>/JS/settings.js"></script>
        <style>
            body {
                background-color: #181A1B;
                color: #fff;
                font-family: 'Noto Sans JP', 'Arial', sans-serif;
                min-height: 100vh;
                margin: 0;
                overflow-x: hidden;
            }

            .error-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: calc(100vh - 80px);
                padding: 40px 20px;
                text-align: center;
                position: relative;
            }

            .error-animation {
                position: relative;
                margin-bottom: 40px;
            }

            .error-code {
                font-size: 180px;
                font-weight: 900;
                background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                line-height: 1;
                margin: 0;
                animation: glitch 3s infinite;
                text-shadow: 0 0 80px rgba(23, 162, 184, 0.5);
            }

            @keyframes glitch {
                0%, 100% {
                    transform: translate(0);
                }
                20% {
                    transform: translate(-2px, 2px);
                }
                40% {
                    transform: translate(-2px, -2px);
                }
                60% {
                    transform: translate(2px, 2px);
                }
                80% {
                    transform: translate(2px, -2px);
                }
            }

            .floating-manga {
                position: absolute;
                width: 60px;
                height: 80px;
                background: rgba(23, 162, 184, 0.2);
                border: 2px solid rgba(23, 162, 184, 0.4);
                border-radius: 4px;
                animation: float 6s ease-in-out infinite;
            }

            .floating-manga:nth-child(1) {
                top: 10%;
                left: 10%;
                animation-delay: 0s;
            }

            .floating-manga:nth-child(2) {
                top: 20%;
                right: 15%;
                animation-delay: 1s;
            }

            .floating-manga:nth-child(3) {
                bottom: 20%;
                left: 15%;
                animation-delay: 2s;
            }

            .floating-manga:nth-child(4) {
                bottom: 15%;
                right: 10%;
                animation-delay: 3s;
            }

            @keyframes float {
                0%, 100% {
                    transform: translateY(0px) rotate(0deg);
                }
                50% {
                    transform: translateY(-20px) rotate(5deg);
                }
            }

            .error-content {
                position: relative;
                z-index: 1;
                max-width: 600px;
            }

            .error-title {
                font-size: 36px;
                font-weight: 700;
                margin-bottom: 20px;
                color: #fff;
            }

            .error-message {
                font-size: 18px;
                color: #b0b0b0;
                margin-bottom: 40px;
                line-height: 1.6;
            }

            .error-actions {
                display: flex;
                gap: 20px;
                justify-content: center;
                flex-wrap: wrap;
            }

            .error-btn {
                padding: 14px 32px;
                font-size: 16px;
                font-weight: 600;
                border-radius: 8px;
                text-decoration: none;
                transition: all 0.3s ease;
                display: inline-flex;
                align-items: center;
                gap: 10px;
                border: none;
                cursor: pointer;
            }

            .error-btn-primary {
                background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
                color: #fff;
                box-shadow: 0 4px 15px rgba(23, 162, 184, 0.4);
            }

            .error-btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(23, 162, 184, 0.6);
                color: #fff;
            }

            .error-btn-secondary {
                background: rgba(255, 255, 255, 0.1);
                color: #fff;
                border: 2px solid rgba(255, 255, 255, 0.2);
            }

            .error-btn-secondary:hover {
                background: rgba(255, 255, 255, 0.15);
                border-color: rgba(255, 255, 255, 0.3);
                transform: translateY(-2px);
                color: #fff;
            }

            .error-suggestions {
                margin-top: 60px;
                padding: 30px;
                background: rgba(255, 255, 255, 0.05);
                border-radius: 12px;
                border: 1px solid rgba(255, 255, 255, 0.1);
            }

            .suggestions-title {
                font-size: 20px;
                font-weight: 600;
                margin-bottom: 20px;
                color: #17a2b8;
            }

            .suggestions-list {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
                text-align: left;
            }

            .suggestion-item {
                display: flex;
                align-items: center;
                gap: 10px;
                color: #b0b0b0;
                font-size: 14px;
            }

            .suggestion-icon {
                width: 20px;
                height: 20px;
                color: #17a2b8;
                flex-shrink: 0;
            }

            @media (max-width: 768px) {
                .error-code {
                    font-size: 120px;
                }

                .error-title {
                    font-size: 28px;
                }

                .error-message {
                    font-size: 16px;
                }

                .error-actions {
                    flex-direction: column;
                }

                .error-btn {
                    width: 100%;
                    justify-content: center;
                }

                .floating-manga {
                    width: 40px;
                    height: 60px;
                }

                .error-suggestions {
                    padding: 20px;
                }

                .suggestions-list {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>
    <body>
        <div class="navbar">
            <div class="navbar-container">
                <div class="logo-container">
                    <a href="<?php echo $base_url; ?>/php/redirect.php">
                        <img src="<?php echo $base_url; ?>/images/icon.png" alt="Logo" class="logo" />
                    </a>
                    <div class="nav-links">
                        <a href="<?php echo $base_url; ?>/php/redirect.php" class="nav-link">Home</a>
                        <a href="<?php echo $base_url; ?>/bookmark" class="nav-link">Bookmarks</a>
                        <a href="<?php echo $base_url; ?>/comics" class="nav-link">Comics</a>
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
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="notification">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" 
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" 
                                stroke-linejoin="round" class="notification-icon">
                                <path d="M10.268 21a2 2 0 0 0 3.464 0"></path>
                                <path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673
                                        C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8
                                        c0 4.499-1.411 5.956-2.738 7.326"></path>
                            </svg>
                        </div>
                    <?php endif; ?>
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
                        
                        $has_custom_pfp = $user_pfp && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $user_pfp);
                        $is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
                    ?>
                    
                    <div class="user-profile-container">
                        <?php if ($has_custom_pfp): ?>
                            <img src="<?php echo $base_url . '/' . htmlspecialchars($user_pfp); ?>" 
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
                            <a href="<?php echo $base_url; ?>/pending" class="pending-manga">
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
                            
                <?php else: ?>
                    <a href="<?php echo $base_url; ?>/login" class="login-button">
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

        <div class="error-container">
            <div class="floating-manga"></div>
            <div class="floating-manga"></div>
            <div class="floating-manga"></div>
            <div class="floating-manga"></div>

            <div class="error-content">
                <div class="error-animation">
                    <h1 class="error-code">503</h1>
                </div>

                <h2 class="error-title">Service Temporarily Unavailable</h2>
                <p class="error-message">
                    We're currently performing maintenance or experiencing high traffic. 
                    The service should be back online shortly. Thank you for your patience!
                </p>

                <div class="error-actions">
                    <a href="javascript:location.reload()" class="error-btn error-btn-primary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 2v6h-6"></path>
                            <path d="M3 12a9 9 0 0 1 15-6.7L21 8"></path>
                            <path d="M3 22v-6h6"></path>
                            <path d="M21 12a9 9 0 0 1-15 6.7L3 16"></path>
                        </svg>
                        Retry
                    </a>
                    <a href="<?php echo $base_url; ?>/php/redirect.php" class="error-btn error-btn-secondary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                        Back to Home
                    </a>
                </div>

                <div class="error-suggestions">
                    <h3 class="suggestions-title">What's happening?</h3>
                    <div class="suggestions-list">
                        <div class="suggestion-item">
                            <svg class="suggestion-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                            <span>Scheduled maintenance</span>
                        </div>
                        <div class="suggestion-item">
                            <svg class="suggestion-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect width="20" height="14" x="2" y="7" rx="2" ry="2"></rect>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                            </svg>
                            <span>Server upgrades in progress</span>
                        </div>
                        <div class="suggestion-item">
                            <svg class="suggestion-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <span>Should be back soon</span>
                        </div>
                        <div class="suggestion-item">
                            <svg class="suggestion-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="23 4 23 10 17 10"></polyline>
                                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                            </svg>
                            <span>Try refreshing in a moment</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>