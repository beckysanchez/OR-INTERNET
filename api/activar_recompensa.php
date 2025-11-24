<?php
include __DIR__ . '/../db.php';

$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['id_usuario'];
$recompensaId = $data['id_recompensa'];

// Quitar la seleccionada previa
$conn->query("UPDATE usuario_recompensas SET seleccionada = 0 WHERE id_usuario = $userId");

// Activar la nueva
$conn->query("UPDATE usuario_recompensas SET seleccionada = 1 
              WHERE id_usuario = $userId AND id_recompensa = $recompensaId");

echo json_encode(["success" => true, "message" => "Recompensa activada"]);
?>
