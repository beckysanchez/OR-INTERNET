<?php
// Incluir el archivo de conexión (asumiendo que está en la carpeta superior)
include '../db.php';

// Configurar encabezados para responder JSON
header('Content-Type: application/json');

// La entrada JSON se lee de php://input, ya que el frontend envía un body JSON.
$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);

$correo = $data['CORREO'] ?? '';
$contra = $data['CONTRA'] ?? '';

// --- Validación de entrada ---
if (empty($correo) || empty($contra)) {
    http_response_code(400);
    echo json_encode(['msg' => 'Correo y contraseña son requeridos.']);
    exit;
}

// --- Buscar usuario por correo ---
$sql = "SELECT ID_USUARIO, NOMBRE, CORREO, CONTRA, Username, puntos, img_p FROM USUARIO WHERE CORREO = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['msg' => 'Error de preparación SQL: ' . $conn->error]);
    exit;
}

$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user_db = $result->fetch_assoc();
    
    // --- Verificar Contraseña ---
    // Usamos password_verify si se usó password_hash en el registro
    if (password_verify($contra, $user_db['CONTRA'])) {
        
        // Credenciales correctas. Preparamos el objeto del usuario (sin enviar la contraseña hasheada)
        $user = [
            'id_usuario' => $user_db['ID_USUARIO'],
            'nombre' => $user_db['NOMBRE'],
            'correo' => $user_db['CORREO'],
            'username' => $user_db['Username'],
            'puntos' => $user_db['puntos'],
            'img_p' => $user_db['img_p'] // Imagen en Base64
        ];

        http_response_code(200);
        echo json_encode(['msg' => 'Inicio de sesión exitoso', 'user' => $user]);
    } else {
        // Contraseña incorrecta
        http_response_code(401); // Unauthorized
        echo json_encode(['msg' => 'Credenciales incorrectas.']);
    }
} else {
    // Usuario no encontrado
    http_response_code(401); // Unauthorized
    echo json_encode(['msg' => 'Credenciales incorrectas.']);
}

$stmt->close();
$conn->close();
?>