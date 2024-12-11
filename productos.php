<?php

session_start(); // Inicia la sesión

require_once('Conexion/Conexion.php');
require_once('Clases/Productos.php');
require_once('Clases/Categoria.php');

// Crear conexión a la base de datos
$database = new Conexion();
$db = $database->obtenerConexion();

// Crear instancia de Productos y Categoria
$productosModel = new Productos($db);
$categoriaModel = new Categoria($db);

// Obtener todas las categorías
$categorias = $categoriaModel->obtenerCategorias(); // Fetch categories

// Verificar si se ha seleccionado una categoría
$categoriaSeleccionada = isset($_GET['categoria']) ? $_GET['categoria'] : null;

// Obtener productos según la categoría seleccionada
if ($categoriaSeleccionada) {
    $query = "SELECT * FROM productos WHERE id_categoria = ?";
    if ($stmt = $db->prepare($query)) {
        $stmt->bind_param("i", $categoriaSeleccionada);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $productos = []; // Inicializar el array de productos filtrados

        while ($fila = $resultado->fetch_assoc()) {
            $productos[] = $fila; // Solo agregar los productos filtrados
        }

        $stmt->close();
    } else {
        echo json_encode(['error' => 'Error al preparar la consulta: ' . $db->error]);
        exit;
    }
} else {
    // Obtener todos los productos si no se ha seleccionado una categoría
    $productos = $productosModel->obtenerTodosLosProductos();
}

// Retornar los productos y categorías en formato JSON
echo json_encode(['categorias' => $categorias, 'productos' => $productos]);

?>
