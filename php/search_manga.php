<?php
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    require_once 'session.php';

    $conn->set_charset("utf8mb4");

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit();
    }

    $query = isset($_POST['query']) ? trim($_POST['query']) : '';

    if (empty($query)) {
        echo json_encode([]);
        exit();
    }

    if (strlen($query) < 1) {
        echo json_encode([]);
        exit();
    }

    try {
        $searchTerm = "%$query%";
        $exactMatch = "$query%";
        
        $sql = "SELECT id, title, author, type, genre, image_url, description, created_at 
                FROM manga 
                WHERE approved = 1 AND (
                    title LIKE ? OR 
                    author LIKE ? OR 
                    genre LIKE ? OR
                    type LIKE ?
                ) 
                ORDER BY 
                    CASE 
                        WHEN title LIKE ? THEN 1
                        WHEN author LIKE ? THEN 2
                        WHEN title LIKE ? THEN 3
                        WHEN author LIKE ? THEN 4
                        WHEN genre LIKE ? THEN 5
                        ELSE 6
                    END,
                    title ASC
                LIMIT 15";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("sssssssss", 
            $searchTerm, $searchTerm, $searchTerm, $searchTerm,
            $exactMatch, $exactMatch,
            $searchTerm, $searchTerm, $searchTerm
        );
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $mangas = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $imageUrl = $row['image_url'];
                
                if (!empty($imageUrl)) {
                    if (!str_starts_with($imageUrl, 'uploads/')) {
                        $imageUrl = 'uploads/manga/' . $imageUrl;
                    }
                } else {
                    $imageUrl = 'images/placeholder.png';
                }
                
                $description = $row['description'];
                if (strlen($description) > 120) {
                    $description = substr($description, 0, 120) . '...';
                }
                
                $mangas[] = [
                    'id' => (int)$row['id'],
                    'title' => htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'),
                    'author' => htmlspecialchars($row['author'], ENT_QUOTES, 'UTF-8'),
                    'type' => htmlspecialchars($row['type'], ENT_QUOTES, 'UTF-8'),
                    'genre' => htmlspecialchars($row['genre'], ENT_QUOTES, 'UTF-8'),
                    'image_url' => htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'),
                    'description' => htmlspecialchars($description, ENT_QUOTES, 'UTF-8'),
                    'created_at' => $row['created_at']
                ];
            }
        }
        
        $stmt->close();
        
        echo json_encode($mangas, JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        error_log("Search error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Search failed: ' . $e->getMessage()]);
    } finally {
        $conn->close();
    }
?>