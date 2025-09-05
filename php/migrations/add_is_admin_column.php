<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "manga";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Controlla se la colonna is_admin esiste già
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'is_admin'");
    if ($result->num_rows == 0) {
        // La colonna non esiste, la aggiungiamo
        $sql = "ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0";
        if ($conn->query($sql) === TRUE) {
            echo "Colonna is_admin aggiunta con successo\n";
        } else {
            echo "Errore nell'aggiunta della colonna: " . $conn->error . "\n";
        }
    } else {
        echo "La colonna is_admin esiste già\n";
    }

    // Aggiorna gli admin dalla tabella admin
    $sql = "UPDATE users u 
            INNER JOIN admin a ON u.id = a.user_id 
            SET u.is_admin = 1";
    if ($conn->query($sql) === TRUE) {
        echo "Permessi admin aggiornati con successo\n";
    } else {
        echo "Errore nell'aggiornamento dei permessi: " . $conn->error . "\n";
    }

    $conn->close();
    echo "Migrazione completata\n";
?>
