<?php
include __DIR__ . '/../db.php';
header('Content-Type: application/json');

$sql = "SELECT id_recompensa, nombre, imagen_url, costo FROM recompensas";
$result = $conn->query($sql);

$recompensas = [];
while ($row = $result->fetch_assoc()) {
    $recompensas[] = $row;
}

echo json_encode($recompensas);
?>
