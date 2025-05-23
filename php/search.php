<?php
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['query'])) {
        $query = $_GET['query'];
        $servername = "localhost";
        $db_username = "root";
        $db_password = "";
        $dbname = "manga";
        $conn = new mysqli($servername, $db_username, $db_password, $dbname);
        if ($conn->connect_error) {
            echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
            exit();
        }
        $stmt = $conn->prepare("SELECT title FROM manga WHERE approved = 1 AND title LIKE CONCAT('%', ?, '%') LIMIT 5");
        $stmt->bind_param("s", $query);
        $stmt->execute();
        $result = $stmt->get_result();
        $titles = [];
        while ($row = $result->fetch_assoc()) {
            $titles[] = $row['title'];
        }
        echo json_encode(['success' => true, 'data' => $titles]);
        $stmt->close();
        $conn->close();
        exit();
    }
?>