<?php
// php/search_manga.php - Script backend per la ricerca
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// Configurazione database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "manga";

// Connessione al database
$conn = new mysqli($servername, $username, $password, $dbname);

// Controllo connessione
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Imposta il charset UTF-8
$conn->set_charset("utf8mb4");

// Verifica che la richiesta sia POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Ottieni il termine di ricerca
$query = isset($_POST['query']) ? trim($_POST['query']) : '';

// Validazione input
if (empty($query)) {
    echo json_encode([]);
    exit();
}

// Validazione lunghezza minima
if (strlen($query) < 1) {
    echo json_encode([]);
    exit();
}

try {
    // Usa prepared statements per sicurezza
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
        $searchTerm, $searchTerm, $searchTerm, $searchTerm,  // WHERE conditions
        $exactMatch, $exactMatch,                            // ORDER priority (exact matches)
        $searchTerm, $searchTerm, $searchTerm               // ORDER priority (partial matches)
    );
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $mangas = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Gestione URL immagine
            $imageUrl = $row['image_url'];
            
            // Assicurati che l'URL dell'immagine sia nel formato corretto
            if (!empty($imageUrl)) {
                // Se l'URL non inizia già con uploads/, aggiungilo
                if (!str_starts_with($imageUrl, 'uploads/')) {
                    $imageUrl = 'uploads/manga/' . $imageUrl;
                }
            } else {
                // Immagine placeholder se non presente
                $imageUrl = 'images/placeholder.png';
            }
            
            // Tronca descrizione se troppo lunga
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
    
    // Restituisci i risultati in formato JSON
    echo json_encode($mangas, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Search error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Search failed: ' . $e->getMessage()]);
} finally {
    // Chiudi la connessione
    $conn->close();
}
?>