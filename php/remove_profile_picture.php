<?php
// remove_profile_picture.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "manga";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Get current pfp
    $stmt = $conn->prepare("SELECT pfp FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('User not found');
    }
    
    $row = $result->fetch_assoc();
    $current_pfp = $row['pfp'];
    $stmt->close();
    
    // Se non c'è un'immagine da rimuovere
    if (empty($current_pfp)) {
        echo json_encode(['success' => true, 'message' => 'No profile picture to remove']);
        $conn->close();
        exit();
    }
    
    // Verifica che il file esista prima di procedere
    $file_path = '../' . $current_pfp;
    if (!file_exists($file_path)) {
        // File non esiste, aggiorna solo il database
        $update_stmt = $conn->prepare("UPDATE users SET pfp = NULL WHERE id = ?");
        $update_stmt->bind_param("i", $user_id);
        
        if (!$update_stmt->execute()) {
            throw new Exception('Failed to update database');
        }
        
        $update_stmt->close();
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Profile picture reference removed (file was already missing)']);
        $conn->close();
        exit();
    }
    
    // Prima cancella il file fisico
    if (!unlink($file_path)) {
        throw new Exception('Failed to delete profile picture file');
    }
    
    // Poi aggiorna il database
    $update_stmt = $conn->prepare("UPDATE users SET pfp = NULL WHERE id = ?");
    $update_stmt->bind_param("i", $user_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception('Failed to update database after file deletion');
    }
    
    $update_stmt->close();
    
    // Commit della transazione
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Profile picture removed successfully']);
    
} catch (Exception $e) {
    // Rollback in caso di errore
    $conn->rollback();
    
    // Se il file è stato cancellato ma il database non è stato aggiornato,
    // non possiamo ripristinare il file, quindi loggiamo l'errore
    error_log("Profile picture removal error for user $user_id: " . $e->getMessage());
    
    echo json_encode(['success' => false, 'message' => 'Failed to remove profile picture: ' . $e->getMessage()]);
}

$conn->close();
?>