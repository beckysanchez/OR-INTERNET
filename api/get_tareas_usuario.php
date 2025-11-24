<?php
include '../conexion.php';
header('Content-Type: application/json');

$id_usuario = $_GET['id_usuario'];

$sql = "SELECT utg.id_usuario_tarea, tg.descripcion, tg.puntos, utg.completada
        FROM usuario_tareas_grupo utg
        JOIN tareas_grupo tg ON utg.id_tarea_grupo = tg.id_tarea_grupo
        WHERE utg.id_usuario = $id_usuario";

$result = $conn->query($sql);
echo json_encode($result->fetch_all(MYSQLI_ASSOC));
?>
