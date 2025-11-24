<?php
include '../db.php';
header('Content-Type: application/json');

$id_usuario = $_GET['id'] ?? null;

if(!$id_usuario){
    echo json_encode([]);
    exit;
}

$query = $conn->prepare("
    SELECT p.*, par.equipo_local, par.equipo_visitante
    FROM predicciones p
    INNER JOIN partidos par ON par.id_partido = p.id_partido
    WHERE p.id_usuario = ?
    ORDER BY p.id_prediccion DESC
");
$query->bind_param("i", $id_usuario);
$query->execute();
$result = $query->get_result();

$predicciones = [];

while($row = $result->fetch_assoc()){
    $predicciones[] = $row;
}

echo json_encode($predicciones);
?>
