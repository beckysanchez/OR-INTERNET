<?php
include '../conexion.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$id_grupo = $data['id_grupo'];
$creador_id = $data['creador_id'];
$descripcion = $data['descripcion'];
$puntos = $data['puntos'] ?? 10;

// 1️⃣ Insertar tarea en tareas_grupo
$conn->query("INSERT INTO tareas_grupo (id_grupo, creador_id, descripcion, puntos) 
              VALUES ('$id_grupo', '$creador_id', '$descripcion', '$puntos')");

// Obtener el ID generado
$id_tarea_grupo = $conn->insert_id;

// 2️⃣ Asignar la tarea a TODOS los miembros del grupo
$conn->query("INSERT INTO usuario_tareas_grupo (id_tarea_grupo, id_usuario)
              SELECT $id_tarea_grupo, id_usuario 
              FROM grupo_miembros 
              WHERE id_grupo = $id_grupo");

echo json_encode(["success" => true, "id_tarea" => $id_tarea_grupo]);
?>
