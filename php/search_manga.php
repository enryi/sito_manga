<?php
require_once 'db_connection.php';

if (isset($_POST['search'])) {
    $search = $_POST['search'];
    
    // Prepare the SQL query with LIKE for partial matches
    $sql = "SELECT id, title, image_url FROM manga WHERE title LIKE ? AND approved = 1";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $searchResults = array();
    while ($row = $result->fetch_assoc()) {
        $searchResults[] = array(
            'id' => $row['id'],
            'title' => $row['title'],
            'image_url' => $row['image_url']
        );
    }
    
    // Return results as JSON
    header('Content-Type: application/json');
    echo json_encode($searchResults);
    exit();
}
?>
