<?php
// api/enviar_mensaje.php
header("Content-Type: application/json; charset=utf-8");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    require_once __DIR__ . '/../db.php'; // usa el mismo db.php

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode(["status" => "error", "msg" => "JSON inválido"]);
        exit;
    }

    $idConversacion = isset($data['ID_CONVERSACION']) ? (int)$data['ID_CONVERSACION'] : 0;
    $idEmisor       = isset($data['ID_EMISOR']) ? (int)$data['ID_EMISOR'] : 0;
    $mensaje        = isset($data['MENSAJE']) ? trim($data['MENSAJE']) : '';

    if ($idConversacion <= 0 || $idEmisor <= 0 || $mensaje === '') {
        echo json_encode(["status" => "error", "msg" => "Datos incompletos"]);
        exit;
    }

    $sql = "
        INSERT INTO mensaje (ID_CONVERSACION, ID_EMISOR, MENSAJE, FECHA_ENVIO)
        VALUES (?, ?, ?, NOW())
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $idConversacion, $idEmisor, $mensaje);
    $stmt->execute();

    echo json_encode([
        "status" => "ok",
        "id"     => $conn->insert_id
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "msg"    => $e->getMessage()
    ]);
}
