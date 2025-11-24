<?php
include __DIR__ . '/../db.php';
header('Content-Type: application/json');

$userId = $_GET['user_id'] ?? 0;

$sql = "SELECT utg.id_usuario_tarea, tg.descripcion, tg.puntos, utg.completada
        FROM usuario_tareas_grupo utg
        JOIN tareas_grupo tg ON utg.id_tarea_grupo = tg.id_tarea_grupo
        WHERE utg.id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();
echo json_encode($result->fetch_all(MYSQLI_ASSOC));
?>
