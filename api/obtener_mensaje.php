<?php
// api/obtener_mensaje.php
header('Content-Type: application/json; charset=utf-8');

// Para que mysqli lance excepciones (y podamos atraparlas)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Ajusta la ruta si tu db.php está en otra carpeta
    require_once __DIR__ . '/../db.php';  // usa $conn de db.php

    if (!isset($_GET['ID_CONVERSACION'])) {
        echo json_encode(["error" => true, "msg" => "Falta ID_CONVERSACION"]);
        exit;
    }

    $idConversacion = (int) $_GET['ID_CONVERSACION'];

    // Ajusta columnas según tu tabla MENSAJE
    $sql = "
        SELECT 
            ID_MENSAJE,
            ID_EMISOR,
            MENSAJE,
            FECHA_ENVIO,
            ARCHIVO_URL,
            ARCHIVO_MIME
        FROM mensaje
        WHERE ID_CONVERSACION = ?
        ORDER BY FECHA_ENVIO ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idConversacion);
    $stmt->execute();
    $result = $stmt->get_result();

    $mensajes = [];
    while ($row = $result->fetch_assoc()) {
        $mensajes[] = $row;
    }

    echo json_encode($mensajes);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "error" => true,
        "msg"   => $e->getMessage()
    ]);
}
