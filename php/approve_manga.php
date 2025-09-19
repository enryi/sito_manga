<?php
session_start();
require_once 'notification_functions.php';
require_once 'db_connection.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "manga";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mangaId = intval($_POST['manga_id']);
    $description = $conn->real_escape_string($_POST['description']);

    $mangaQuery = "SELECT title, image_url, description, author, type, genre, submitted_by FROM manga WHERE id = ?";
    $mangaStmt = $conn->prepare($mangaQuery);
    $mangaStmt->bind_param("i", $mangaId);
    $mangaStmt->execute();
    $mangaResult = $mangaStmt->get_result();
    
    if ($mangaResult && $mangaResult->num_rows > 0) {
        $manga = $mangaResult->fetch_assoc();
        $submitted_by = $manga['submitted_by'];
        $manga_title = $manga['title'];

        // Aggiorna semplicemente il database, niente più creazione di file
        $query = "UPDATE manga SET description = ?, approved = 1 WHERE id = ?";
        $updateStmt = $conn->prepare($query);
        $updateStmt->bind_param("si", $description, $mangaId);
        
        if ($updateStmt->execute()) {
            error_log("Manga approved successfully: $manga_title (ID: $mangaId)");
            
            // Invia notifica all'utente che ha sottomesso il manga
            if ($submitted_by) {
                $approval_message = "Great news! Your manga '$manga_title' has been approved and is now live on the site.";
                if (notifyUserAboutMangaStatus($conn, $submitted_by, 'manga_approved', $manga_title, $approval_message, $mangaId)) {
                    error_log("Approval notification sent to user ID: $submitted_by");
                } else {
                    error_log("Failed to send approval notification to user ID: $submitted_by");
                }
            }
            
            // Elimina le notifiche pending per questo manga
            $deleteNotifQuery = "DELETE FROM notifications WHERE manga_id = ? AND type = 'manga_pending'";
            $deleteStmt = $conn->prepare($deleteNotifQuery);
            $deleteStmt->bind_param("i", $mangaId);
            $deleteStmt->execute();
            
        } else {
            error_log("Error approving manga: " . $conn->error);
        }
    } else {
        error_log("Error fetching manga details for ID: $mangaId");
    }
}

// Controlla se ci sono ancora manga da approvare
$queryCheck = "SELECT COUNT(*) AS pending_count FROM manga WHERE approved = 0";
$resultCheck = $conn->query($queryCheck);
$rowCheck = $resultCheck->fetch_assoc();
$conn->close();

if ($rowCheck['pending_count'] > 0) {
    header("Location: ../pending");
} else {
    header("Location: redirect.php");
}
exit;
?>