<?php
header('Content-Type: application/json');
include '../db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT u.ID_USUARIO, u.Username, u.puntos
        FROM amigos a
        JOIN usuario u ON u.ID_USUARIO = a.ID_USUARIO2
        WHERE a.ID_USUARIO1 = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

$stmt->execute();
$result = $stmt->get_result();

$amigos = [];
while ($row = $result->fetch_assoc()) {
    $amigos[] = $row;
}

echo json_encode($amigos);
?>
