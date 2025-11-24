<?php
include '../conexion.php';
header('Content-Type: application/json');

$id_usuario_tarea = $_POST['id_usuario_tarea'];

$conn->query("UPDATE usuario_tareas_grupo 
              SET completada = 1, fecha_completada = NOW()
              WHERE id_usuario_tarea = $id_usuario_tarea");

echo json_encode(["success" => true]);
?>
