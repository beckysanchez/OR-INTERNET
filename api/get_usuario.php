<?php
include '../db.php';
header('Content-Type: application/json');

$id = $_GET['id'];

$q = $conn->query("SELECT ID_USUARIO, Username, puntos, foto_perfil FROM usuario WHERE ID_USUARIO = $id");

echo json_encode($q->fetch_assoc());
?>
