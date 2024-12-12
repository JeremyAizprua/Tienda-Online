<?php
require_once('../Conexion/Conexion.php');
require_once('../Clases/Productos.php');

$database = new Conexion();
$db = $database->obtenerConexion();
$productosModel = new Productos($db);

$id_producto = isset($_GET['id']) ? intval($_GET['id']) : 0;
$producto = $productosModel->obtenerProductoPorId($id_producto);

if ($producto) {
    echo json_encode($producto);
} else {
    echo json_encode(['error' => 'Producto no encontrado']);
}

