<?php
include __DIR__ . '/../db.php';
header('Content-Type: application/json');

// Consulta ordenada por puntos (máximo a mínimo)
$sql = "SELECT id_usuario, NOMBRE, Username, puntos, foto 
        FROM usuario 
        ORDER BY puntos DESC 
        LIMIT 10"; // Puedes cambiar el límite

$result = $conn->query($sql);

$ranking = [];
while ($row = $result->fetch_assoc()) {
    $ranking[] = $row;
}

echo json_encode($ranking);
?>
