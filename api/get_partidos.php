<?php
include '../db.php';
header('Content-Type: application/json');

$sql = "SELECT id_partido, equipo_local, equipo_visitante, fecha 
        FROM partidos";
$result = $conn->query($sql);

$partidos = [];
while ($row = $result->fetch_assoc()) {
    $partidos[] = $row;
}

echo json_encode($partidos);
$conn->close();
?>
