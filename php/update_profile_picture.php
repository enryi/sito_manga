<?php
    require_once 'session.php';
    require_once 'secure_image_upload.php'; // <-- NUOVO
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit();
    }

    $user_id = $_SESSION['user_id'];

    if (!isset($_FILES['profile_picture'])) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded']);
        exit();
    }

    // ============================================================
    // UPLOAD SICURO CON SANITIZZAZIONE IMMAGINE
    // ============================================================
    $uploadResult = secureImageUpload(
        $_FILES['profile_picture'],
        '../uploads/profiles/',
        5 * 1024 * 1024  // 5MB
    );
    
    if (!$uploadResult['success']) {
        error_log("SECURITY: Profile picture upload blocked for user $user_id - " . $uploadResult['error']);
        echo json_encode(['success' => false, 'message' => $uploadResult['error']]);
        exit();
    }
    
    // VERIFICA DIMENSIONI RAGIONEVOLI PER FOTO PROFILO
    if ($uploadResult['width'] > 2000 || $uploadResult['height'] > 2000) {
        @unlink($uploadResult['path']);
        echo json_encode(['success' => false, 'message' => 'Image dimensions too large. Maximum 2000x2000 pixels.']);
        exit();
    }

    // ============================================================
    // RECUPERO FOTO PROFILO ATTUALE
    // ============================================================
    $stmt = $conn->prepare("SELECT pfp FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_pfp = null;
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $current_pfp = $row['pfp'];
    }
    $stmt->close();

    // ============================================================
    // UPDATE DATABASE
    // ============================================================
    $pfp_path = 'uploads/profiles/' . $uploadResult['filename'];
    $update_stmt = $conn->prepare("UPDATE users SET pfp = ? WHERE id = ?");
    $update_stmt->bind_param("si", $pfp_path, $user_id);
    
    if ($update_stmt->execute()) {
        // ELIMINA VECCHIA FOTO SE ESISTE
        if ($current_pfp && file_exists('../' . $current_pfp)) {
            @unlink('../' . $current_pfp);
            error_log("Old profile picture deleted: " . $current_pfp);
        }
        
        error_log("SUCCESS: Profile picture updated for user $user_id. New file: " . $uploadResult['filename']);
        echo json_encode([
            'success' => true, 
            'message' => 'Profile picture updated successfully',
            'pfp_path' => $pfp_path
        ]);
    } else {
        // ROLLBACK: elimina file appena caricato se update fallisce
        @unlink($uploadResult['path']);
        error_log("Failed to update profile picture in database for user $user_id");
        echo json_encode(['success' => false, 'message' => 'Failed to update database']);
    }
    
    $update_stmt->close();
    $conn->close();
?>