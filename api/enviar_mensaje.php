<?php
header("Content-Type: application/json");
require_once "conexion.php";

$data = json_decode(file_get_contents("php://input"), true);

$idConversacion = $data['ID_CONVERSACION'];
$idEmisor = $data['ID_EMISOR'];
$mensaje = $data['MENSAJE'];

$stmt = $conn->prepare("
    INSERT INTO mensaje (ID_CONVERSACION, ID_EMISOR, MENSAJE, FECHA_ENVIO)
    VALUES (?, ?, ?, NOW())
");
$stmt->bind_param("iis", $idConversacion, $idEmisor, $mensaje);

if ($stmt->execute()) {
    echo json_encode(["status" => "ok", "id" => $conn->insert_id]);
} else {
    echo json_encode(["status" => "error", "msg" => $stmt->error]);
}
?>
