<?php
session_start();
require_once('../Conexion/Conexion.php');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit();
}

$usuario_id = $_SESSION['id_usuario'];
$producto_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($producto_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de producto inválido']);
    exit();
}

$database = new Conexion();
$db = $database->obtenerConexion();

$query = "INSERT IGNORE INTO lista_deseos (id_usuario, id_producto) VALUES (?, ?)";
$stmt = $db->prepare($query);
$stmt->bind_param("ii", $usuario_id, $producto_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Producto añadido a la lista de deseos']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al añadir el producto a la lista de deseos']);
}

$stmt->close();
$db->close();

