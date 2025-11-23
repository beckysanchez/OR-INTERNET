<?php
include '../db.php';
header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);

$id_usuario = $data['usuario_id'] ?? null;
$id_partido = $data['partido_id'] ?? null;
$pred_local = $data['marcador_local'] ?? null;
$pred_visitante = $data['marcador_visitante'] ?? null;

if (!$id_usuario || !$id_partido || $pred_local === null || $pred_visitante === null) {
    echo json_encode(['success' => false, 'msg' => 'Datos incompletos']);
    exit;
}

$sql = "INSERT INTO predicciones (id_usuario, id_partido, pred_local, pred_visitante)
        VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $id_usuario, $id_partido, $pred_local, $pred_visitante);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'msg' => 'Predicción guardada']);
} else {
    echo json_encode(['success' => false, 'msg' => 'Error al guardar']);
}
?>
