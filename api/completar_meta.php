<?php
include __DIR__ . '/../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$id_usuario_tarea = $data['id_usuario_tarea'] ?? 0;

$sql = "UPDATE usuario_tareas_grupo 
        SET completada = 1, fecha_completada = NOW() 
        WHERE id_usuario_tarea = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario_tarea);
$stmt->execute();

echo json_encode(["success" => true]);
?>
