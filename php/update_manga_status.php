<?php
    session_start();
    header('Content-Type: application/json');
    require_once 'db_connection.php';

    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'User not authenticated']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }

    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $_POST;
        }
        
        $userId = $_SESSION['user_id'];
        $mangaId = intval($input['manga_id']);
        $status = $input['status'];
        $chapters = intval($input['chapters']);
        $rating = !empty($input['rating']) ? floatval($input['rating']) : null;
        
        $validStatuses = ['reading', 'completed', 'dropped', 'plan_to_read'];
        if (!in_array($status, $validStatuses)) {
            throw new Exception('Status not valid');
        }
        
        if ($chapters < 0) {
            throw new Exception('The number of chapters cannot be negative');
        }
        
        if ($rating !== null && ($rating < 0 || $rating > 10)) {
            throw new Exception('The rating has to be between 0 and 10');
        }
        
        if ($mangaId <= 0) {
            throw new Exception('ID manga not valid');
        }
        
        $mangaCheck = $conn->prepare("SELECT id FROM manga WHERE id = ? AND approved = 1");
        $mangaCheck->bind_param("i", $mangaId);
        $mangaCheck->execute();
        $mangaResult = $mangaCheck->get_result();
        
        if ($mangaResult->num_rows === 0) {
            throw new Exception('Manga non trovato');
        }
        
        $checkQuery = "SELECT id FROM user_list WHERE user_id = ? AND manga_id = ?";
        $checkStmt = $conn->prepare($checkQuery);
        
        if (!$checkStmt) {
            throw new Exception('Errore nella preparazione della query: ' . $conn->error);
        }
        
        $checkStmt->bind_param("ii", $userId, $mangaId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $updateQuery = "UPDATE user_list SET status = ?, chapters = ?, rating = ?, updated_at = NOW() WHERE user_id = ? AND manga_id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            
            if (!$updateStmt) {
                throw new Exception('Error preparing the update: ' . $conn->error);
            }
            
            $updateStmt->bind_param("sidii", $status, $chapters, $rating, $userId, $mangaId);
            
            if (!$updateStmt->execute()) {
                throw new Exception('Error executing the update: ' . $updateStmt->error);
            }
            
            $affectedRows = $updateStmt->affected_rows;
            $action = 'updated';
            
        } else {
            $insertQuery = "INSERT INTO user_list (user_id, manga_id, status, chapters, rating, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
            $insertStmt = $conn->prepare($insertQuery);
            
            if (!$insertStmt) {
                throw new Exception('Error preparing the insert: ' . $conn->error);
            }
            
            $insertStmt->bind_param("iisid", $userId, $mangaId, $status, $chapters, $rating);
            
            if (!$insertStmt->execute()) {
                throw new Exception('Error executing the insert: ' . $insertStmt->error);
            }
            
            $affectedRows = $insertStmt->affected_rows;
            $action = 'inserted';
        }
        
        if ($affectedRows > 0) {
            $statsQuery = "SELECT 
                            COUNT(*) as total_manga,
                            SUM(CASE WHEN status = 'reading' THEN 1 ELSE 0 END) as reading_count,
                            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
                            SUM(CASE WHEN status = 'dropped' THEN 1 ELSE 0 END) as dropped_count,
                            SUM(CASE WHEN status = 'plan_to_read' THEN 1 ELSE 0 END) as plan_to_read_count,
                            AVG(CASE WHEN rating IS NOT NULL THEN rating ELSE NULL END) as avg_rating,
                            SUM(chapters) as total_chapters
                        FROM user_list WHERE user_id = ?";
            
            $statsStmt = $conn->prepare($statsQuery);
            $statsStmt->bind_param("i", $userId);
            $statsStmt->execute();
            $statsResult = $statsStmt->get_result();
            $userStats = $statsResult->fetch_assoc();
            
            $mangaRatingQuery = "SELECT AVG(rating) as avg_rating, COUNT(*) as rating_count 
                                FROM user_list 
                                WHERE manga_id = ? AND rating IS NOT NULL";
            $mangaRatingStmt = $conn->prepare($mangaRatingQuery);
            $mangaRatingStmt->bind_param("i", $mangaId);
            $mangaRatingStmt->execute();
            $mangaRatingResult = $mangaRatingStmt->get_result();
            $mangaRating = $mangaRatingResult->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'action' => $action,
                'message' => 'Progress saved successfully',
                'data' => [
                    'manga_id' => $mangaId,
                    'status' => $status,
                    'chapters' => $chapters,
                    'rating' => $rating,
                    'user_stats' => $userStats,
                    'manga_avg_rating' => round(floatval($mangaRating['avg_rating']), 1),
                    'manga_rating_count' => intval($mangaRating['rating_count'])
                ],
                'timestamp' => time()
            ]);
            
        } else {
            throw new Exception('Nothing was modified');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'timestamp' => time()
        ]);
        
        error_log("Error in update_manga_status.php: " . $e->getMessage() . " - User ID: " . ($_SESSION['user_id'] ?? 'N/A'));
        
    } catch (Error $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Interlal Server Error',
            'timestamp' => time()
        ]);
        
        error_log("Critical Error in update_manga_status.php: " . $e->getMessage());
    }

    if (isset($conn)) {
        $conn->close();
    }
?>