<?php
include __DIR__ . '/../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$id_usuario = $data['id_usuario'];
$descripcion = $data['descripcion'];
$id_usuario_tarea = $data['id_usuario_tarea'];

$response = ["success" => false];

// 🔹 1. Validar según el tipo de tarea
switch ($descripcion) {
    
    case "Agregar un amigo":
        $sql = "SELECT COUNT(*) AS total FROM amigos WHERE id_usuario = ?";
        break;

    case "Realizar una predicción":
        $sql = "SELECT COUNT(*) AS total FROM predicciones WHERE id_usuario = ?";
        break;

    case "Ganar una predicción":
        $sql = "SELECT COUNT(*) AS total FROM predicciones WHERE id_usuario = ? AND resultado = 'ganada'";
        break;

    case "Crear un chat grupal":
        $sql = "SELECT COUNT(*) AS total FROM grupos WHERE creador_id = ?";
        break;
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($result['total'] > 0) {

    // 🔹 Si cumple, marcamos como completada
    $update = $conn->prepare("UPDATE usuario_tareas_grupo 
                              SET completada = 1, fecha_completada = NOW() 
                              WHERE id_usuario_tarea = ?");
    $update->bind_param("i", $id_usuario_tarea);
    $update->execute();

    $response["success"] = true;
}

echo json_encode($response);
?>
