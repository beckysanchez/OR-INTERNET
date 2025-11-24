<?php
include '../db.php';
header('Content-Type: application/json');

$userId = $_GET['user_id'] ?? 0;
$today = date('Y-m-d');

if (!$userId) {
    echo json_encode(["success" => false, "message" => "ID requerido"]);
    exit;
}

// 1️⃣ Buscar si ya tiene metas asignadas hoy
$sql = "SELECT um.id_usuario_meta, m.descripcion, m.puntos, um.completada
        FROM usuario_metas um
        JOIN metas m ON um.id_meta = m.id_meta
        WHERE um.id_usuario = ? AND um.fecha = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $userId, $today);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // 2️⃣ Si no tiene metas → asignamos 3 aleatorias
    $conn->query("INSERT INTO usuario_metas (id_usuario, id_meta, fecha)
                  SELECT $userId, id_meta, '$today' 
                  FROM metas ORDER BY RAND() LIMIT 3");

    $stmt->execute(); 
    $result = $stmt->get_result();
}

$metas = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode(["success" => true, "metas" => $metas]);
?>
