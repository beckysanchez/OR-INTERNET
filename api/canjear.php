<?php
include __DIR__ . '/../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['id_usuario'];
$recompensaId = $data['id_recompensa'];

// 1. Obtener costo de recompensa
$sql = "SELECT costo FROM recompensa WHERE id_recompensa = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $recompensaId);
$stmt->execute();
$stmt->bind_result($costo);
$stmt->fetch();
$stmt->close();

// 2. Verificar puntos del usuario
$sql = "SELECT puntos FROM usuario WHERE ID_USUARIO = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($puntos);
$stmt->fetch();
$stmt->close();

if ($puntos < $costo) {
    echo json_encode(["success" => false, "message" => "No tienes suficientes puntos"]);
    exit;
}

// 3. Insertar en usuario_recompensas
$sql = "INSERT INTO usuario_recompensas (id_usuario, id_recompensa) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $userId, $recompensaId);
$stmt->execute();

// 4. Restar puntos
$conn->query("UPDATE usuario SET puntos = puntos - $costo WHERE ID_USUARIO = $userId");

echo json_encode(["success" => true, "message" => "Recompensa canjeada correctamente"]);
?>
