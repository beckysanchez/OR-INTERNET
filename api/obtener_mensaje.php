<?php
header('Content-Type: application/json; charset=utf-8');

// Igual que en obtener_conversacion.php: subimos un nivel
require_once __DIR__ . '/../db.php';

// Validar parámetro
if (!isset($_GET['ID_CONVERSACION'])) {
    http_response_code(400);
    echo json_encode(["error" => "Falta ID_CONVERSACION"]);
    exit;
}

$idConversacion = (int)$_GET['ID_CONVERSACION'];
if ($idConversacion <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "ID_CONVERSACION inválido"]);
    exit;
}

// $conn viene de db.php
if (!isset($conn) || $conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Conexión a BD no inicializada"]);
    exit;
}

// Traer TODOS los mensajes de esa conversación
$sql = "
    SELECT 
        ID_MENSAJE,
        ID_CONVERSACION,
        ID_EMISOR,
        MENSAJE,
        TIPO,
        ARCHIVO_URL,
        ARCHIVO_MIME,
        ARCHIVO_NOMBRE_ORIGINAL,
        FECHA_ENVIO
    FROM MENSAJE
    WHERE ID_CONVERSACION = ?
    ORDER BY FECHA_ENVIO ASC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Error al preparar consulta", "detalle" => $conn->error]);
    exit;
}

$stmt->bind_param("i", $idConversacion);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["error" => "Error al ejecutar consulta", "detalle" => $stmt->error]);
    $stmt->close();
    exit;
}

$result = $stmt->get_result();
$mensajes = [];

while ($row = $result->fetch_assoc()) {
    $mensajes[] = $row;
}

$stmt->close();

// Devolvemos array (puede venir vacío si no hay mensajes todavía)
echo json_encode($mensajes);
