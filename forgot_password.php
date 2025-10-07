<?php
    require_once 'session.php';
    $password_reset_error = null;
    $password_reset_success = null;
    
    if (isset($_SESSION['password_reset_error'])) {
        $password_reset_error = $_SESSION['password_reset_error'];
        unset($_SESSION['password_reset_error']);
    }
    
    if (isset($_SESSION['password_reset_success'])) {
        $password_reset_success = $_SESSION['password_reset_success'];
        unset($_SESSION['password_reset_success']);
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Password Reset - Mangas</title>
        <link rel="icon" href="images/icon.png" type="image/x-icon">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="CSS/navbar.css">
        <link rel="stylesheet" href="CSS/auth.css">
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="JS/auth-notifications.js"></script>
        <script src="JS/enhanced-password.js"></script>
        <style>
            .toggle-password {
                position: absolute;
                right: 12px;
                top: 50%;
                transform: translateY(-50%);
                cursor: pointer;
                width: 20px;
                height: 20px;
                fill: #666;
                transition: fill 0.2s ease;
                z-index: 10;
            }
            .toggle-password:hover {
                fill: #6F2598;
            }
            .password-inputs {
                position: relative;
                display: flex;
                align-items: center;
            }
            .password-inputs input[type="password"],
            .password-inputs input[type="text"] {
                padding-right: 45px !important;
            }
            .step-indicator {
                text-align: center;
                margin-bottom: 1.5rem;
            }
            .step-indicator .step {
                display: inline-block;
                width: 30px;
                height: 30px;
                line-height: 30px;
                border-radius: 50%;
                background-color: #333;
                color: #888;
                margin: 0 10px;
                font-size: 14px;
                font-weight: bold;
                transition: all 0.3s ease;
            }
            .step-indicator .step.active {
                background-color: #6F2598;
                color: white;
            }
            .step-indicator .step.completed {
                background-color: #28a745;
                color: white;
            }
            .step-indicator .step-line {
                display: inline-block;
                width: 40px;
                height: 2px;
                background-color: #333;
                vertical-align: middle;
                transition: background-color 0.3s ease;
            }
            .step-indicator .step-line.completed {
                background-color: #28a745;
            }
        </style>
    </head>
    <body style="background-color: #181A1B; color: #fff; font-family: 'Noto Sans JP', 'Arial', sans-serif; min-height: 100vh;">
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
                    <a href="login" class="login-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                            <polyline points="10 17 15 12 10 7"></polyline>
                            <line x1="15" x2="3" y1="12" y2="12"></line>
                        </svg>
                        Login
                    </a>
                </div>
            </div>
        </div>

        <div class="auth-wrapper">
            <div class="auth-container">
                <div class="auth-card">
                    <div class="auth-logo">
                        <img src="images/icon.png" alt="Mangas Logo" />
                    </div>
                    <h1 class="auth-title">Password Reset</h1>
                    
                    <div class="step-indicator">
                        <span id="step1" class="step active">1</span>
                        <span class="step-line"></span>
                        <span id="step2" class="step">2</span>
                    </div>
                    
                    <div id="username-section">
                        <p class="auth-subtitle">Enter your username to reset your password</p>
                        
                        <div id="message-box" style="display: none;"></div>
                        <?php if ($password_reset_error): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($password_reset_error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($password_reset_success): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($password_reset_success); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form id="username-form">
                            <div class="form-group">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                                </svg>
                                <input type="text" 
                                    id="username" 
                                    name="username" 
                                    class="form-control" 
                                    placeholder="Enter your username" 
                                    autocomplete="username"
                                    required>
                            </div>
                            
                            <button type="submit" class="btn-primary">Verify Username</button>
                        </form>
                        
                        <div class="auth-footer">
                            Remember your password? <a href="login">Back to Login</a>
                        </div>
                    </div>

                    <div id="password-section" style="display: none;">
                        <p class="auth-subtitle">Enter your new password</p>
                        
                        <form id="password-form" action="php/reset_password.php" method="POST">
                            <input type="hidden" id="hidden-username" name="username">
                            
                            <div class="form-group">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd"/>
                                </svg>
                                <div class="password-inputs">
                                    <input type="password" 
                                        name="password" 
                                        id="password" 
                                        class="form-control" 
                                        placeholder="New Password" 
                                        autocomplete="new-password"
                                        required>
                                    <svg id="togglePassword" class="toggle-password" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M2.99902 3L20.999 21M9.8433 9.91364C9.32066 10.4536 8.99902 11.1892 8.99902 12C8.99902 13.6569 10.3422 15 11.999 15C12.8215 15 13.5667 14.669 14.1086 14.133M6.49902 6.64715C4.59972 7.90034 3.15305 9.78394 2.45703 12C3.73128 16.0571 7.52159 19 11.9992 19C13.9881 19 15.8414 18.4194 17.3988 17.4184M10.999 5.04939C11.328 5.01673 11.6617 5 11.9992 5C16.4769 5 20.2672 7.94291 21.5414 12C21.2607 12.894 20.8577 13.7338 20.3522 14.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <svg id="showPasswordIcon" class="toggle-password" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                                        <path d="M15.0007 12C15.0007 13.6569 13.6576 15 12.0007 15C10.3439 15 9.00073 13.6569 9.00073 12C9.00073 10.3431 10.3439 9 12.0007 9C13.6576 9 15.0007 10.3431 15.0007 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12.0012 5C7.52354 5 3.73326 7.94288 2.45898 12C3.73324 16.0571 7.52354 19 12.0012 19C16.4788 19 20.2691 16.0571 21.5434 12C20.2691 7.94291 16.4788 5 12.0012 5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                            
                            <div id="password-strength-bar">
                                <div id="password-strength-bar-inner"></div>
                            </div>
                            <div id="password-strength"></div>
                            
                            <button type="submit" id="password-button" class="btn-primary" disabled>Reset Password</button>
                        </form>
                        
                        <div class="auth-footer">
                            Remember your password? <a href="login">Back to Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.form-control').forEach(input => {
                    input.addEventListener('focus', function() {
                        const svg = this.parentElement.querySelector('svg:not(.toggle-password)');
                        if (svg) {
                            svg.style.color = '#6F2598';
                        }
                    });
                    
                    input.addEventListener('blur', function() {
                        const svg = this.parentElement.querySelector('svg:not(.toggle-password)');
                        if (svg) {
                            svg.style.color = '#666';
                        }
                    });
                });
                
                const passwordInput = document.getElementById('password');
                const hideIcon = document.getElementById('togglePassword');
                const showIcon = document.getElementById('showPasswordIcon');
                
                function togglePasswordVisibility() {
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        hideIcon.style.display = 'none';
                        showIcon.style.display = 'block';
                    } else {
                        passwordInput.type = 'password';
                        hideIcon.style.display = 'block';
                        showIcon.style.display = 'none';
                    }
                }
                
                hideIcon.addEventListener('click', togglePasswordVisibility);
                showIcon.addEventListener('click', togglePasswordVisibility);

                document.getElementById('username-form').addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const username = document.getElementById('username').value.trim();
                    
                    if (!username) {
                        showAuthNotification('error', 'Validation Error', 'Username is required.');
                        return;
                    }
                    
                    try {
                        const response = await fetch('php/check_username.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ username: username })
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            document.getElementById('username-section').style.display = 'none';
                            document.getElementById('password-section').style.display = 'block';
                            document.getElementById('hidden-username').value = username;
                            
                            document.getElementById('step1').classList.remove('active');
                            document.getElementById('step1').classList.add('completed');
                            document.getElementById('step2').classList.add('active');
                            document.querySelector('.step-line').classList.add('completed');
                            
                            showAuthNotification('success', 'Username Verified', 'Username found! Please enter your new password.');
                        } else {
                            showAuthNotification('error', 'Username Not Found', 'Username not found. Please check your username and try again.');
                        }
                    } catch (error) {
                        console.error('Username verification error:', error);
                        showAuthNotification('error', 'Connection Error', 'An error occurred. Please check your connection and try again.');
                    }
                });

                document.getElementById('password-form').addEventListener('submit', function(e) {
                    const passwordButton = document.getElementById('password-button');
                    if (passwordButton.disabled) {
                        e.preventDefault();
                        showAuthNotification('warning', 'Invalid Password', 'Please enter a strong password to continue.');
                        return false;
                    }
                    
                    showAuthNotification('info', 'Updating Password', 'Please wait while we update your password...', 0);
                });
            });
        </script>
    </body>
</html>