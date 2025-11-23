<?php
$host = 'localhost'; 
$user = 'root'; 
$password = ''; 
$dbname = 'pwci'; 

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
