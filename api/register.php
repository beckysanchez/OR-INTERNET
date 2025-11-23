<?php
// Mostrar errores (solo durante desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

include '../db.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método no permitido.';
    echo json_encode($response);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$nombre = $data['nombre'] ?? null;
$correo = $data['correo'] ?? null;
$username = $data['username'] ?? null;
$password = $data['password'] ?? null;

if (!$nombre || !$correo || !$username || !$password) {
    $response['message'] = 'Por favor, completa todos los campos.';
    echo json_encode($response);
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'El formato del correo no es válido.';
    echo json_encode($response);
    exit;
}

if (strlen($password) < 6) {
    $response['message'] = 'La contraseña debe tener al menos 6 caracteres.';
    echo json_encode($response);
    exit;
}

$passwordHash = password_hash($password, PASSWORD_BCRYPT);

$stmt_check = $conn->prepare("SELECT ID_USUARIO FROM usuario WHERE CORREO = ? OR Username = ?");
$stmt_check->bind_param("ss", $correo, $username);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows > 0) {
    $response['message'] = 'Correo o nombre de usuario ya registrados.';
} else {
    $stmt_insert = $conn->prepare(
        "INSERT INTO usuario (NOMBRE, CORREO, CONTRA, Username, puntos) VALUES (?, ?, ?, ?, 10)"
    );
    $stmt_insert->bind_param("ssss", $nombre, $correo, $passwordHash, $username);

    if ($stmt_insert->execute()) {
        $response['success'] = true;
        $response['message'] = '¡Registro exitoso! Ahora puedes iniciar sesión.';
    } else {
        $response['message'] = 'Error al crear la cuenta. Intenta más tarde.';
    }

    $stmt_insert->close();
}

$stmt_check->close();
$conn->close();

echo json_encode($response);
?>
