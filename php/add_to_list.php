<?php
session_start();
header('Content-Type: application/json');
require_once '../php/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Utente non autenticato']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$manga_id = $data['manga_Id'];
$user_id = $_SESSION['user_id'];

$query = "SELECT id FROM manga WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $manga_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Il manga non esiste nel database']);
    exit;
}

// Inserisci il manga nella lista dell'utente
$query = "INSERT INTO lista_utente (user_id, manga_id, status, chapters) VALUES (?, ?, 'plan_to_read', 0)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $manga_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
?>