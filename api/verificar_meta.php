<?php
include __DIR__ . '/../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$id_usuario = $data['id_usuario'];
$id_usuario_tarea = $data['id_usuario_tarea'];
$descripcion = $data['descripcion'];

$response = ["success" => false, "nuevos_puntos" => 0];

// 1️⃣ Obtener los puntos que otorga esta tarea
$stmtPuntos = $conn->prepare("SELECT puntos FROM usuario_tareas_grupo WHERE id_usuario_tarea = ?");
$stmtPuntos->bind_param("i", $id_usuario_tarea);
$stmtPuntos->execute();
$puntosMeta = $stmtPuntos->get_result()->fetch_assoc()['puntos'] ?? 0;

// 2️⃣ Validar cumplimiento según la meta
switch ($descripcion) {

    case "Realizar una predicción":
        $sql = "SELECT COUNT(*) AS total FROM predicciones WHERE id_usuario = ?";
        break;

    case "Ganar una predicción":
        $sql = "SELECT COUNT(*) AS total FROM predicciones WHERE id_usuario = ? AND puntos_obtenidos > 0";
        break;

    case "Llegar a 20 puntos":
        $sql = "SELECT CASE WHEN puntos >= 20 THEN 1 ELSE 0 END AS total 
                FROM usuario WHERE ID_USUARIO = ?";
        break;

    case "Ganar 2 predicciones":
        $sql = "SELECT COUNT(*) AS total FROM predicciones WHERE id_usuario = ? AND puntos_obtenidos > 0";
        break;

    default:
        echo json_encode(["error" => "Descripción de tarea no válida"]);
        exit;
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

$completado = false;
if ($descripcion === "Ganar 2 predicciones") {
    $completado = ($result['total'] >= 2);
} else {
    $completado = ($result['total'] > 0);
}

// 3️⃣ Si se completó, marcar como completada y sumar puntos
if ($completado) {

    // 🔹 Marcar tarea como completada
    $update = $conn->prepare("UPDATE usuario_tareas_grupo 
                              SET completada = 1, fecha_completada = NOW() 
                              WHERE id_usuario_tarea = ?");
    $update->bind_param("i", $id_usuario_tarea);
    $update->execute();

    // 🔹 Sumar los puntos al usuario
    $sumar = $conn->prepare("UPDATE usuario 
                             SET puntos = puntos + ? 
                             WHERE ID_USUARIO = ?");
    $sumar->bind_param("ii", $puntosMeta, $id_usuario);
    $sumar->execute();

    // 🔹 Obtener los nuevos puntos
    $getPuntos = $conn->prepare("SELECT puntos FROM usuario WHERE ID_USUARIO = ?");
    $getPuntos->bind_param("i", $id_usuario);
    $getPuntos->execute();
    $nuevoPuntaje = $getPuntos->get_result()->fetch_assoc()['puntos'];

    $response["success"] = true;
    $response["nuevos_puntos"] = $nuevoPuntaje;
}

echo json_encode($response);
?>
