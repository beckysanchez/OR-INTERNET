<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

include '../db.php';

$nombre = $_POST['NOMBRE'] ?? '';
$correo = $_POST['CORREO'] ?? '';
$username = $_POST['Username'] ?? '';
$contra = $_POST['CONTRA'] ?? '';
$puntos_iniciales = 10;

if (!$nombre || !$correo || !$username || !$contra) {
    http_response_code(400);
    echo json_encode(['msg' => 'Todos los campos son obligatorios']);
    exit;
}

$contra_hasheada = password_hash($contra, PASSWORD_DEFAULT);

$img_p_base64 = null;
if (isset($_FILES['img_p']) && $_FILES['img_p']['error'] === UPLOAD_ERR_OK) {
    $file_content = file_get_contents($_FILES['img_p']['tmp_name']);
    $img_p_base64 = base64_encode($file_content);
}

$sql = "INSERT INTO USUARIO (NOMBRE, CORREO, CONTRA, Username, puntos, img_p) 
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

$stmt->bind_param("ssssis", 
    $nombre, 
    $correo, 
    $contra_hasheada, 
    $username, 
    $puntos_iniciales, 
    $img_p_base64
);

if ($stmt->execute()) {
    echo json_encode(['msg' => 'Registro exitoso']);
} else {
    if ($conn->errno === 1062) {
        http_response_code(409);
        echo json_encode(['msg' => 'El correo o nombre de usuario ya está registrado']);
    } else {
        http_response_code(500);
        echo json_encode(['msg' => 'Error: ' . $conn->error]);
    }
}

$conn->close();
?>
