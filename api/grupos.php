<?php
header('Content-Type: application/json; charset=utf-8');

// Si db.php está en la carpeta padre de api/
require_once __DIR__ . '/../db.php';

$user_id = $_GET['user_id'] ?? 0;

$sql = "
    SELECT 
        g.ID_GRUPO,
        g.NOMBRE,
        g.FECHA_CREACION,
        GROUP_CONCAT(u.Username ORDER BY u.Username SEPARATOR ', ') AS miembros_nombres
    FROM GRUPO g
    JOIN GRUPO_MIEMBROS gm ON gm.ID_GRUPO = g.ID_GRUPO
    JOIN USUARIO u         ON u.ID_USUARIO = gm.ID_USUARIO
    WHERE g.ID_GRUPO IN (
        SELECT ID_GRUPO 
        FROM GRUPO_MIEMBROS 
        WHERE ID_USUARIO = ?
    )
    GROUP BY g.ID_GRUPO, g.NOMBRE, g.FECHA_CREACION
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$grupos = [];
while ($row = $result->fetch_assoc()) {
    $grupos[] = $row;
}

echo json_encode($grupos);
