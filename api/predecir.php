<?php
include '../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$id_usuario      = $data['usuario_id'] ?? null;
$id_partido      = $data['partido_id'] ?? null;
$pred_local      = $data['marcador_local'] ?? null;
$pred_visitante  = $data['marcador_visitante'] ?? null;

if (!$id_usuario || !$id_partido || $pred_local === null || $pred_visitante === null) {
    echo json_encode(['success' => false, 'msg' => 'Datos incompletos']);
    exit;
}

// EVITAR DUPLICAR PREDICCIONES
$check = $conn->prepare("SELECT * FROM predicciones WHERE id_usuario = ? AND id_partido = ?");
$check->bind_param("ii", $id_usuario, $id_partido);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'msg' => 'Ya realizaste una predicción para este partido']);
    exit;
}

/* -----------------------------------------------------------
   🎲 SIMULACIÓN TEMPORAL DE RESULTADO DEL PARTIDO (NO REAL)
   mientras no haya marcador real en la BD
----------------------------------------------------------- */
$simLocal  = rand(1);
$simVisit  = rand(1);

// Determinar si acertó o no
$acertado = ($simLocal == $pred_local && $simVisit == $pred_visitante) ? 1 : 0;

// Si acertó, gana 25 puntos (temporal)
$puntosGanados = $acertado ? 25 : 0;


/* 🔽 GUARDAR PREDICCIÓN */
$sql = "INSERT INTO predicciones (id_usuario, id_partido, pred_local, pred_visitante, acertado)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiii", $id_usuario, $id_partido, $pred_local, $pred_visitante, $acertado);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'msg' => 'Error al guardar']);
    exit;
}

/* 👑 SI GANA, SUMAR PUNTOS */
if ($puntosGanados > 0) {
    $update = $conn->prepare("UPDATE usuario SET puntos = puntos + ? WHERE ID_USUARIO = ?");
    $update->bind_param("ii", $puntosGanados, $id_usuario);
    $update->execute();
}


/* 📢 RESPUESTA AL FRONTEND */
echo json_encode([
    'success'       => true,
    'msg'           => 'Predicción guardada',
    'puntosGanados' => $puntosGanados,
    'simulado'      => "$simLocal - $simVisit"
]);
