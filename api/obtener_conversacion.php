<?php
header('Content-Type: application/json; charset=utf-8');

// 👇 IMPORTANTE: aquí usas db.php, que es el que sí existe
require_once __DIR__ . '/../db.php';


// Validar parámetros
if (!isset($_GET['id1'], $_GET['id2'])) {
    http_response_code(400);
    echo json_encode(["error" => "Parámetros id1 e id2 son requeridos"]);
    exit;
}

$id1 = (int)$_GET['id1'];
$id2 = (int)$_GET['id2'];

if ($id1 <= 0 || $id2 <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "IDs inválidos"]);
    exit;
}

// Asegúrate que $conn viene de db.php
if (!isset($conn) || $conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Conexión a BD no inicializada"]);
    exit;
}

// Buscar si ya existe conversación entre esos 2 usuarios
$sql = "
    SELECT ID_CONVERSACION 
    FROM CONVERSACION 
    WHERE (ID_USUARIO1 = ? AND ID_USUARIO2 = ?)
       OR (ID_USUARIO1 = ? AND ID_USUARIO2 = ?)
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Error al preparar consulta", "detalle" => $conn->error]);
    exit;
}

$stmt->bind_param("iiii", $id1, $id2, $id2, $id1);
$stmt->execute();
$stmt->bind_result($idConversacion);

if ($stmt->fetch()) {
    // ✅ Ya existe conversación
    echo json_encode(["ID_CONVERSACION" => $idConversacion]);
    $stmt->close();
    exit;
}

$stmt->close();

// ❌ No existe → la creamos
$sqlInsert = "
    INSERT INTO CONVERSACION (ID_USUARIO1, ID_USUARIO2) 
    VALUES (?, ?)
";
$stmt2 = $conn->prepare($sqlInsert);
if (!$stmt2) {
    http_response_code(500);
    echo json_encode(["error" => "Error al preparar insert", "detalle" => $conn->error]);
    exit;
}

$stmt2->bind_param("ii", $id1, $id2);
if (!$stmt2->execute()) {
    http_response_code(500);
    echo json_encode(["error" => "Error al crear conversación", "detalle" => $stmt2->error]);
    $stmt2->close();
    exit;
}

$newId = $stmt2->insert_id;
$stmt2->close();

// ✅ Devolvemos la nueva conversación
echo json_encode(["ID_CONVERSACION" => $newId]);
