<?php
include '../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$id_usuario = $data['id_usuario'];
$id_recompensa = $data['id_recompensa'];

// 1️⃣ Obtener datos del usuario y recompensa
$usuario_q = $conn->query("SELECT puntos, foto_perfil FROM usuario WHERE ID_USUARIO = $id_usuario");
$recompensa_q = $conn->query("SELECT costo, imagen_url FROM recompensas WHERE id_recompensa = $id_recompensa");

$usuario = $usuario_q->fetch_assoc();
$recompensa = $recompensa_q->fetch_assoc();

if (!$usuario || !$recompensa) {
    echo json_encode(["status" => "error", "message" => "Usuario o recompensa no encontrada"]);
    exit;
}

$costo = $recompensa['costo'];
$imagen_url = $recompensa['imagen_url'];
$puntos_actuales = $usuario['puntos'];

// 2️⃣ Verificar si ya tiene la recompensa comprada
$compra_q = $conn->query("SELECT * FROM usuario_recompensas WHERE id_usuario = $id_usuario AND id_recompensa = $id_recompensa");
$ya_comprada = $compra_q->num_rows > 0;

// 3️⃣ Si NO la tiene comprada, verificar puntos
if (!$ya_comprada) {
    if ($puntos_actuales < $costo) {
        echo json_encode(["status" => "saldo_insuficiente"]);
        exit;
    }

    // Registrar compra
    $conn->query("INSERT INTO usuario_recompensas (id_usuario, id_recompensa, fecha_canje, seleccionada)
                  VALUES ($id_usuario, $id_recompensa, NOW(), 1)");

    // Descontar puntos
    $nuevos_puntos = $puntos_actuales - $costo;
    $conn->query("UPDATE usuario SET puntos = $nuevos_puntos WHERE ID_USUARIO = $id_usuario");
} else {
    // Si ya la tenía, solo actualizar estado activa
    $nuevos_puntos = $puntos_actuales;
    
    // Cambiar a seleccionada = 1
    $conn->query("UPDATE usuario_recompensas 
                  SET seleccionada = 1 
                  WHERE id_usuario = $id_usuario AND id_recompensa = $id_recompensa");
}

// 4️⃣ Poner la foto como perfil
$conn->query("UPDATE usuario SET foto_perfil = '$imagen_url' WHERE ID_USUARIO = $id_usuario");

// 5️⃣ Desactivar otras recompensas
$conn->query("UPDATE usuario_recompensas 
              SET seleccionada = 0 
              WHERE id_usuario = $id_usuario AND id_recompensa != $id_recompensa");

echo json_encode([
    "status" => "ok",
    "nueva_foto" => $imagen_url,
    "nuevos_puntos" => $nuevos_puntos
]);
?>
