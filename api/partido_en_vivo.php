<?php
header('Content-Type: application/json');
include '../db.php';

// Ejemplo: buscar el partido que tenga estado = 'EN_CURSO'
$sql = "SELECT * FROM partidos WHERE estado = 'EN_CURSO' LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'match' => [
            'local' => $row['equipo_local'],
            'visitante' => $row['equipo_visitante'],
            'marcador_local' => $row['marcador_local'],
            'marcador_visitante' => $row['marcador_visitante'],
            'minuto' => $row['minuto_actual']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'msg' => 'No hay partido en vivo']);
}

$conn->close();
?>
