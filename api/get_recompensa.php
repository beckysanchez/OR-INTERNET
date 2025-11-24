<?php
include '../db.php';
header('Content-Type: application/json');

$sql = "SELECT * FROM recompensas";
$result = $conn->query($sql);

$recompensas = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode(["success" => true, "recompensas" => $recompensas]);
?>
