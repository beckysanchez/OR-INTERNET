<?php
require_once "conexion.php";

$id1 = $_GET['id1'];
$id2 = $_GET['id2'];

$stmt = $conn->prepare("
    SELECT ID_CONVERSACION FROM conversacion 
    WHERE (ID_USUARIO1=? AND ID_USUARIO2=?) 
       OR (ID_USUARIO1=? AND ID_USUARIO2=?)
");
$stmt->bind_param("iiii", $id1, $id2, $id2, $id1);
$stmt->execute();
$stmt->bind_result($idConversacion);

if ($stmt->fetch()) {
    echo json_encode(["ID_CONVERSACION" => $idConversacion]);
} else {

    // 🔹 Insertamos incluyendo FECHA_CREACION con NOW()
    $stmt2 = $conn->prepare("
        INSERT INTO conversacion (ID_USUARIO1, ID_USUARIO2, FECHA_CREACION) 
        VALUES (?, ?, NOW())
    ");
    $stmt2->bind_param("ii", $id1, $id2);
    $stmt2->execute();

    echo json_encode(["ID_CONVERSACION" => $conn->insert_id]);
}

?>
