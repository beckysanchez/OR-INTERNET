<?php
include '../db.php';
header('Content-Type: application/json');

$userId = $_POST['user_id'];
$recompensaId = $_POST['id_recompensa'];

$conn->query("INSERT INTO usuario_recompensas (id_usuario, id_recompensa) 
              VALUES ($userId, $recompensaId)");

$conn->query("UPDATE usuario SET puntos = puntos - 
              (SELECT costo FROM recompensas WHERE id_recompensa = $recompensaId)
              WHERE id_usuario = $userId");

echo json_encode(["success" => true, "message" => "Canje realizado"]);
?>
