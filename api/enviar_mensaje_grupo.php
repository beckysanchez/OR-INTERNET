<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db.php';

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$id_grupo  = isset($data['id_grupo'])  ? (int)$data['id_grupo']  : 0;
$id_emisor = isset($data['id_emisor']) ? (int)$data['id_emisor'] : 0;
$mensaje   = trim($data['mensaje'] ?? '');

if ($id_grupo <= 0 || $id_emisor <= 0 || $mensaje === '') {
    http_response_code(400);
    echo json_encode(["msg" => "Datos incompletos"]);
    exit;
}

$sql = "
    INSERT INTO MENSAJE_GRUPO (ID_GRUPO, ID_EMISOR, MENSAJE)
    VALUES (?, ?, ?)
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $id_grupo, $id_emisor, $mensaje);
$stmt->execute();

echo json_encode([
    "msg"        => "Mensaje enviado",
    "id_mensaje" => $stmt->insert_id
]);
