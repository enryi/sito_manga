<?php
    require_once 'php/username.php';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="CSS/login.css">
        <link rel="stylesheet" href="CSS/user.css">
        <link rel="stylesheet" href="CSS/body.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link rel="icon" href="images/icon.png" type="image/x-icon">
    </head>
    <body>
        <div class="overlay"></div>
        <div class="login-container">
            <div class="login-box">
                <h1>Login</h1>
                <?php if ($login_error): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($login_error); ?>
                    </div>
                <?php endif; ?>
                <form id="loginForm" action="php/process_login.php" method="POST">
                    <input type="text" name="username" placeholder="Username" value="<?php echo $username; ?>" autocomplete="username" required>
                    <input type="password" name="password" placeholder="Password" autocomplete="current-password" required>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="rememberUsername" name="remember_username" <?php echo isset($_COOKIE['remembered_username']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="rememberUsername">Remember your username</label>
                    </div>
                    <button id="loginButton" type="submit">Login</button>
                </form>
                <a href="register">You don't have an account? Register</a>
                <a href="forgot_password">Forgot Password?</a>
            </div>
        </div>
    </body>
</html>