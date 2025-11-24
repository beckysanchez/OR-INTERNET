<?php
require_once "conexion.php";

$idConversacion = $_GET['ID_CONVERSACION'];

$stmt = $conn->prepare("
    SELECT m.ID_MENSAJE, m.MENSAJE, m.FECHA_ENVIO, 
           u.Username AS nombre_emisor, u.ID_USUARIO AS id_emisor
    FROM mensaje m
    JOIN usuario u ON m.ID_EMISOR = u.ID_USUARIO
    WHERE m.ID_CONVERSACION = ?
    ORDER BY m.FECHA_ENVIO ASC
");
$stmt->bind_param("i", $idConversacion);
$stmt->execute();

$result = $stmt->get_result();
$mensajes = [];

while ($row = $result->fetch_assoc()) {
    $mensajes[] = $row;
}

echo json_encode($mensajes);
?>
