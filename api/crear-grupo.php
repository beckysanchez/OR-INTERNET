<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db.php';

// Leer JSON del cuerpo
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["msg" => "JSON inválido"]);
    exit;
}

$nombre      = trim($data['nombre'] ?? '');
$creador_id  = $data['creador_id'] ?? 0;
// En tu JS lo llamas miembros_ids
$miembrosIds = $data['miembros_ids'] ?? [];

// Validaciones básicas
if ($nombre === '' || !$creador_id) {
    http_response_code(400);
    echo json_encode(["msg" => "Faltan datos: nombre o creador_id"]);
    exit;
}

if (!is_array($miembrosIds)) {
    http_response_code(400);
    echo json_encode(["msg" => "miembros_ids debe ser un arreglo"]);
    exit;
}

// Queremos EXACTAMENTE 3 personas: creador + 2 amigos
// En el front ya mandas [creador_id, amigo1, amigo2], pero por si acaso:
$miembrosIds = array_map('intval', $miembrosIds);
$miembrosIds = array_values(array_unique($miembrosIds)); // quitar duplicados

if (count($miembrosIds) !== 3) {
    http_response_code(400);
    echo json_encode(["msg" => "El grupo debe tener exactamente 3 integrantes (creador + 2 amigos)."]);
    exit;
}

try {
    $conn->begin_transaction();

    // Insertar grupo
    $sqlGrupo = "INSERT INTO GRUPO (NOMBRE) VALUES (?)";
    $stmt = $conn->prepare($sqlGrupo);
    $stmt->bind_param("s", $nombre);
    $stmt->execute();

    $grupoId = $stmt->insert_id;

    // Insertar miembros en GRUPO_MIEMBROS
    $sqlMiembro = "INSERT INTO GRUPO_MIEMBROS (ID_GRUPO, ID_USUARIO) VALUES (?, ?)";
    $stmtM = $conn->prepare($sqlMiembro);

    foreach ($miembrosIds as $idUsuario) {
        $stmtM->bind_param("ii", $grupoId, $idUsuario);
        $stmtM->execute();
    }

    $conn->commit();

    echo json_encode([
        "msg"      => "Grupo creado correctamente",
        "grupoId"  => $grupoId,
        "nombre"   => $nombre,
        "miembros" => $miembrosIds
    ]);

} catch (mysqli_sql_exception $e) {
    $conn->rollback();

    // Si el nombre del grupo es único y se repite
    if ($e->getCode() == 1062) {
        http_response_code(409);
        echo json_encode(["msg" => "Ya existe un grupo con ese nombre"]);
    } else {
        http_response_code(500);
        echo json_encode(["msg" => "Error al crear grupo", "error" => $e->getMessage()]);
    }
}
