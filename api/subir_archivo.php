<?php
header('Content-Type: application/json; charset=utf-8');

// Carpeta donde se guardarán los archivos físicamente: /OR-INTERNET/uploads
$uploadDir = __DIR__ . '/../uploads/';

// Crear carpeta si no existe
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Validar que venga el archivo
if (!isset($_FILES['archivo'])) {
    http_response_code(400);
    echo json_encode(["error" => "No se envió archivo"]);
    exit;
}

$file = $_FILES['archivo'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(["error" => "Error al subir el archivo"]);
    exit;
}

// Tipos permitidos (puedes agregar más)
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['png','jpg','jpeg','gif','mp4','mp3','wav','ogg','pdf'];

if (!in_array($ext, $allowed)) {
    http_response_code(400);
    echo json_encode(["error" => "Tipo de archivo no permitido"]);
    exit;
}

// Nombre único para evitar choques
$uniqueName = uniqid('chat_', true) . '.' . $ext;
$destPath   = $uploadDir . $uniqueName;

// Mover archivo a /uploads
if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    http_response_code(500);
    echo json_encode(["error" => "No se pudo guardar el archivo"]);
    exit;
}

// Detectar MIME real
$mime = mime_content_type($destPath);

// Como tu index.php está en /OR-INTERNET/, esta URL relativa funciona:
// <img src="uploads/xxx.png"> → /OR-INTERNET/uploads/xxx.png
$publicUrl = 'uploads/' . $uniqueName;

echo json_encode([
    "url"    => $publicUrl,
    "mime"   => $mime,
    "nombre" => $file['name']
]);
