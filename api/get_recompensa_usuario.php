<?php
include __DIR__ . '/../db.php';
header('Content-Type: application/json');

$userId = $_GET['id_usuario'];

$sql = "SELECT r.id_recompensa, r.nombre, r.imagen_url, ur.seleccionada
        FROM usuario_recompensas ur
        JOIN recompensa r ON ur.id_recompensa = r.id_recompensa
        WHERE ur.id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

echo json_encode($result->fetch_all(MYSQLI_ASSOC));
?>
