
<?php
include '../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$id_usuario = $data['id_usuario'];
$imagen_url = $data['imagen_url'];

$stmt = $conn->prepare("UPDATE usuario SET foto_perfil = ? WHERE id_usuario = ?");
$stmt->bind_param("si", $imagen_url, $id_usuario);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}
?>
