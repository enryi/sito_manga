<?php
    require_once 'php/register.php';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registrazione</title>
        <link rel="icon" href="images/icon.png" type="image/x-icon">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="CSS/password.css">
        <link rel="stylesheet" href="CSS/body.css">
        <script src="JS/password.js"></script>
    </head>
    <body>
        <div class="overlay"></div>
        <div class="register-container">
            <div class="register-box">
                <h1>Registrazione</h1>
                <div id="error-box" style="display: none;"></div>
                <?php if ($registration_error): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($registration_error); ?>
                    </div>
                <?php endif; ?>
                <?php if ($password_changed): ?>
                    <div class="alert alert-success">
                        La tua password è stata aggiornata con successo.
                    </div>
                <?php endif; ?>
                <?php if ($registration_success): ?>
                    <div class="alert alert-success">
                        Registrazione completata con successo. Ora puoi accedere.
                    </div>
                <?php endif; ?>
                <form action="php/process_register.php" method="POST">
                    <input type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($registration_username); ?>" required>
                    <div class="password-inputs">
                        <input type="password" name="password" id="password" placeholder="Password" required>
                        <img id="togglePassword" src="images/hide.png" alt="Mostra/Nascondi Password">
                    </div>
                    <div id="password-strength-bar">
                        <div id="password-strength-bar-inner"></div>
                    </div>
                    <div id="password-strength"></div>
                    <button type="submit" id="password-button" disabled>Registrati</button>
                </form>
                <a href="https://enryi.23hosts.com">Hai già un account? Accedi</a>
            </div>
        </div>
    </body>
</html>