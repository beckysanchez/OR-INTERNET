<?php
header('Content-Type: application/json; charset=utf-8');

// Igual que en grupos.php (usas el db.php que está un nivel arriba)
require_once __DIR__ . '/../db.php';

$id_grupo = isset($_GET['id_grupo']) ? intval($_GET['id_grupo']) : 0;

if ($id_grupo <= 0) {
    echo json_encode([]);
    exit;
}

$sql = "
    SELECT 
        mg.ID_MENSAJE,
        mg.ID_GRUPO,
        mg.ID_EMISOR,
        mg.MENSAJE,
        mg.FECHA_ENVIO,
        u.Username AS autor
    FROM MENSAJE_GRUPO mg
    JOIN USUARIO u ON u.ID_USUARIO = mg.ID_EMISOR
    WHERE mg.ID_GRUPO = ?
    ORDER BY mg.FECHA_ENVIO ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_grupo);
$stmt->execute();
$result = $stmt->get_result();

$mensajes = [];
while ($row = $result->fetch_assoc()) {
    $mensajes[] = $row;
}

echo json_encode($mensajes);
