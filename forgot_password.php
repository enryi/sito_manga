<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="images/icon.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ripristino Password</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
    <script src="JS/password.js"></script>
    <link rel="stylesheet" href="CSS/password.css">
    <link rel="stylesheet" href="CSS/body.css">
</head>
<body>
    <div class="overlay"></div>
    <div class="register-container">
        <div class="register-box">
            <h3>Ripristino Password</h3>
            <div id="message-box" style="display: none;"></div>
            <form id="username-form" onsubmit="checkUsername(event)">
                <input type="text" id="username" name="username" placeholder="Username" required>
                <button type="submit">Verifica Username</button>
                <a href="login.php">Torna all'area di login!</a>
            </form>
            <form id="password-form" action="php/reset_password.php" method="POST" style="display: none;" onsubmit="validatePassword(event)">
                <input type="hidden" id="hidden-username" name="username">
                <div class="password-inputs">
                    <input type="password" name="password" id="password" placeholder="Password" required>
                    <img id="togglePassword" src="images/hide.png" alt="Mostra/Nascondi Password">
                </div>
                <div id="password-strength-bar">
                    <div id="password-strength-bar-inner"></div>
                </div>
                <div id="password-strength"></div>
                <button id="password-button" type="submit" disabled>Imposta nuova password</button>
            </form>
        </div>
    </div>
</body>
</html>