<?php
    session_start();
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "manga";
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }
    $user = $_POST['username'];
    $pass = $_POST['password'];
    $_SESSION['registration_username'] = $user;
    function is_password_pwned($password) {
        $hashed_password = sha1($password);
        $prefix = substr($hashed_password, 0, 5);
        $suffix = substr($hashed_password, 5);
        $url = "https://api.pwnedpasswords.com/range/" . $prefix;
        $response = file_get_contents($url);

        if ($response === false) {
            return false;
        }
        $lines = explode("\n", $response);
        foreach ($lines as $line) {
            list($hash_suffix, $count) = explode(":", $line);
            if (strcasecmp($hash_suffix, $suffix) === 0) {
                return true;
            }
        }
        return false;
    }
    if (is_password_pwned($pass)) {
        $_SESSION['registration_error'] = "La password inserita è stata trovata in un elenco di password rubate. Scegli una password diversa.";
        header("Location: ../register");
        exit();
    }
    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $user, $hashed_password);
    if ($stmt->execute()) {
        $_SESSION['registration_success'] = true;
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $user;
        unset($_SESSION['registration_username']);
        header("Location: ../login?registered=1");
        $stmt->close();
        $conn->close();
        exit();
    } else {
        if (strpos($stmt->error, "Duplicate entry") !== false && strpos($stmt->error, "username") !== false) {
            $_SESSION['registration_error'] = "L'username esiste già.";
        } else {
            $_SESSION['registration_error'] = "Errore durante la registrazione: " . $stmt->error;
        }
        header("Location: ../register");
        $stmt->close();
        $conn->close();
        exit();
    }
?>