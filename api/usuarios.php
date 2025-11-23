<?php
header('Content-Type: application/json');
include '../db.php';

$q = $_GET['q'] ?? '';

$sql = "SELECT ID_USUARIO, Username, puntos FROM usuario WHERE Username LIKE ?";
$stmt = $conn->prepare($sql);
$searchTerm = '%' . $q . '%';
$stmt->bind_param("s", $searchTerm);

$stmt->execute();
$result = $stmt->get_result();

$usuarios = [];
while ($row = $result->fetch_assoc()) {
    $usuarios[] = $row;
}

echo json_encode($usuarios);
?>
