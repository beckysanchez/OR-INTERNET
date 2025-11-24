<?php
include '../conexion.php';
header('Content-Type: application/json');

$id_usuario_meta = $_POST['id_usuario_meta'] ?? 0;

if (!$id_usuario_meta) {
    echo json_encode(["success" => false]);
    exit;
}

$sql = "UPDATE usuario_metas SET completada = 1 WHERE id_usuario_meta = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario_meta);
$stmt->execute();

echo json_encode(["success" => true]);
?>
