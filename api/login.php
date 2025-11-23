<?php
include '../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$correo = $data['CORREO'] ?? '';
$contra = $data['CONTRA'] ?? '';

if (empty($correo) || empty($contra)) {
    http_response_code(400);
    echo json_encode(['msg' => 'Correo y contraseña son requeridos.']);
    exit;
}

$sql = "SELECT ID_USUARIO, NOMBRE, CORREO, CONTRA, Username, puntos 
        FROM usuario WHERE CORREO = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['msg' => 'Error en la consulta: ' . $conn->error]);
    exit;
}

$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user_db = $result->fetch_assoc();

    if (password_verify($contra, $user_db['CONTRA'])) {
        unset($user_db['CONTRA']); // No mandar el hash al frontend
        http_response_code(200);
        echo json_encode(['msg' => 'Inicio de sesión exitoso', 'user' => $user_db]);
    } else {
        http_response_code(401);
        echo json_encode(['msg' => 'Credenciales incorrectas.']);
    }
} else {
    http_response_code(401);
    echo json_encode(['msg' => 'Credenciales incorrectas.']);
}

$stmt->close();
$conn->close();
?>
