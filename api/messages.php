<?php
include '../conexion.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'send') {
    $data = json_decode(file_get_contents('php://input'), true);
    $sender = $data['sender_id'];
    $receiver = $data['receiver_id'];
    $content = $data['content'];

    $query = "INSERT INTO mensaje (id_emisor, id_receptor, contenido, fecha_envio) 
              VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iis", $sender, $receiver, $content);
    $stmt->execute();

    echo json_encode(["success" => true]);
}

if ($action === 'get') {
    $user1 = $_GET['user_id'];
    $user2 = $_GET['friend_id'];

    $query = "SELECT * FROM mensaje 
              WHERE (id_emisor = ? AND id_receptor = ?) OR 
                    (id_emisor = ? AND id_receptor = ?)
              ORDER BY fecha_envio ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiii", $user1, $user2, $user2, $user1);
    $stmt->execute();

    $result = $stmt->get_result();
    echo json_encode(["success" => true, "messages" => $result->fetch_all(MYSQLI_ASSOC)]);
}
?>
