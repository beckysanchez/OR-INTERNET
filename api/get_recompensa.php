<?php
header('Content-Type: application/json; charset=utf-8');
include __DIR__ . '/../db.php';

$id_usuario = isset($_GET['id_usuario']) ? (int)$_GET['id_usuario'] : 0;

// Si no hay usuario válido, regresamos arreglo vacío
if ($id_usuario <= 0) {
    echo json_encode([]);
    exit;
}

$sql = "
    SELECT 
        r.id_recompensa,
        r.nombre,
        r.imagen_url,
        r.costo,
        IF(ur.seleccionada = 1, 1, 0) AS seleccionada
    FROM recompensas r
    LEFT JOIN usuario_recompensas ur
        ON r.id_recompensa = ur.id_recompensa
       AND ur.id_usuario   = ?
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode([]);
    exit;
}

$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

$recompensas = [];
while ($row = $result->fetch_assoc()) {
    $recompensas[] = $row;
}

echo json_encode($recompensas);
