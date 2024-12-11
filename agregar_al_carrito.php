<?php
session_start();
require_once('Conexion/Conexion.php');

$database = new Conexion();
$db = $database->obtenerConexion();

// Verifica si se ha recibido el ID del producto
if (isset($_GET['id'])) {
    $idProducto = intval($_GET['id']);
    
    // Inicializa el carrito en la sesión si no existe
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }

    // Verifica si el producto ya está en el carrito
    if (isset($_SESSION['carrito'][$idProducto])) {
        $_SESSION['carrito'][$idProducto]['cantidad']++;
    } else {
        // Obtiene el producto de la base de datos
        $query = "SELECT * FROM productos WHERE id_producto = ?";
        if ($stmt = $db->prepare($query)) {
            $stmt->bind_param("i", $idProducto);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $producto = $resultado->fetch_assoc();
            if ($producto) {
                // Agrega el producto al carrito
                $_SESSION['carrito'][$idProducto] = [
                    'nombre' => $producto['nombre_producto'],
                    'precio' => $producto['precio'],
                    'cantidad' => 1,
                    'imagen' => $producto['imagen_producto'],
                    'descripcion' => $producto['descripcion'] // Agregar la descripción aquí
                ];
            }
        }
    }

    // Retorna la cantidad de productos en el carrito
    $cantidadTotal = array_sum(array_column($_SESSION['carrito'], 'cantidad'));
    echo json_encode(['success' => true, 'cantidad' => $cantidadTotal]);
} else {
    echo json_encode(['success' => false]);
}
?>
