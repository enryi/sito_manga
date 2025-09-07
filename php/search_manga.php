<?php
    require_once 'db_connection.php';

    if (isset($_POST['search'])) {
        $search = $_POST['search'];
        
        $sql = "SELECT id, title, image_url FROM manga WHERE title LIKE ? AND approved = 1";
        $stmt = $conn->prepare($sql);
        $searchTerm = "%" . $search . "%";
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $searchResults = array();
        while ($row = $result->fetch_assoc()) {
            // Get the current script's directory level to determine the correct path
            $currentPath = $_SERVER['REQUEST_URI'];
            $isInSubfolder = strpos($currentPath, '/series/') !== false;
            
            // Adjust paths based on current location
            $imageUrl = $isInSubfolder ? '../' . $row['image_url'] : $row['image_url'];
            $mangaPath = $isInSubfolder ? 
                strtolower(str_replace(' ', '_', $row['title'])) . '.php' : 
                'series/' . strtolower(str_replace(' ', '_', $row['title'])) . '.php';
            
            $searchResults[] = array(
                'id' => $row['id'],
                'title' => $row['title'],
                'image_url' => $imageUrl,
                'manga_path' => $mangaPath
            );
        }
        
        header('Content-Type: application/json');
        echo json_encode($searchResults);
        exit();
    }
?>