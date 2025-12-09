<?php
header('Content-Type: application/json');

try {
    // Connessione (metti i tuoi dati)
    $conn = new mysqli('localhost', 'root', '', 'manga');

    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }

    // Leggi JSON in input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!isset($data['username']) || empty(trim($data['username']))) {
        echo json_encode(['success' => false, 'message' => 'Username is required']);
        exit;
    }

    $username = trim($data['username']);

    // Prepara query sulla tabella users
    $stmt = $conn->prepare("SELECT username FROM users WHERE username = ? LIMIT 1");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("s", $username);

    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Username found']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Username not found']);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
