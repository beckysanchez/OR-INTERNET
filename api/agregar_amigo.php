<?php
header('Content-Type: application/json');
include '../db.php';

$data = json_decode(file_get_contents("php://input"), true);

$usuario1 = $data['ID_USUARIO1'] ?? null;
$usuario2 = $data['ID_USUARIO2'] ?? null;


if (!$usuario1 || !$usuario2) {
    echo json_encode(['success' => false, 'msg' => 'Faltan datos']);
    exit;
}

$sql = "INSERT INTO amigos (ID_USUARIO1, ID_USUARIO2, FECHA_AMISTAD) 
        VALUES (?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $usuario1, $usuario2);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'msg' => 'Amigo agregado correctamente']);
} else {
    echo json_encode([
        'success' => false,
        'msg' => 'Error al agregar amigo',
        'error' => $stmt->error  // 👀 muestra el error real
    ]);
}


$stmt->close();
$conn->close();
?>
