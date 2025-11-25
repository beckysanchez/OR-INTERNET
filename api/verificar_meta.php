<?php
include __DIR__ . '/../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$id_usuario = $data['id_usuario'];
$descripcion = $data['descripcion'];
$id_usuario_tarea = $data['id_usuario_tarea'];

$response = ["success" => false];

// 🔹 1. Obtener los puntos y descripción real desde tareas_grupo
$stmt = $conn->prepare("
    SELECT tg.puntos, tg.descripcion 
    FROM usuario_tareas_grupo utg
    JOIN tareas_grupo tg ON utg.id_tarea_grupo = tg.id_tarea_grupo
    WHERE utg.id_usuario_tarea = ?
");
$stmt->bind_param("i", $id_usuario_tarea);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

$puntos_tarea = $result['puntos'] ?? 0;
$descripcion_real = $result['descripcion'] ?? '';
$descripcion = strtolower(trim($descripcion));


// 🔹 2. Validación según descripción
$validacion = false;

switch ($descripcion) {

    case "realizar una predicción":
        $sql = "SELECT COUNT(*) AS total FROM predicciones WHERE id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $validacion = $stmt->get_result()->fetch_assoc()['total'] > 0;
        break;

    case "ganar una predicción":
        $sql = "SELECT COUNT(*) AS total FROM predicciones WHERE id_usuario = ? AND acertado = 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $validacion = $stmt->get_result()->fetch_assoc()['total'] > 0;
        break;

    case "ganar 2 predicciones":
        $sql = "SELECT COUNT(*) AS total FROM predicciones WHERE id_usuario = ? AND acertado = 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $validacion = $stmt->get_result()->fetch_assoc()['total'] >= 2;
        break;

    case "llegar a 20 puntos":
        $sql = "SELECT puntos FROM usuario WHERE ID_USUARIO = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $validacion = $stmt->get_result()->fetch_assoc()['puntos'] >= 20;
        break;

    default:
        echo json_encode(["success" => false, "message" => "Tarea no reconocida"]);
        exit;
}

if ($validacion) {

    // 🔹 Marcar tarea como completada
    $update = $conn->prepare("UPDATE usuario_tareas_grupo 
                              SET completada = 1, fecha_completada = NOW() 
                              WHERE id_usuario_tarea = ?");
    $update->bind_param("i", $id_usuario_tarea);
    $update->execute();

    // 🔹 Sumar puntos al usuario
    $conn->query("UPDATE usuario SET puntos = puntos + $puntos_tarea WHERE ID_USUARIO = $id_usuario");

    // 🔹 Obtener nuevo total de puntos
    $nuevo_puntaje = $conn->query("SELECT puntos FROM usuario WHERE ID_USUARIO = $id_usuario")
                          ->fetch_assoc()['puntos'];

    $response = [
        "success" => true,
        "nuevos_puntos" => $nuevo_puntaje
    ];
}

echo json_encode($response);
?>
