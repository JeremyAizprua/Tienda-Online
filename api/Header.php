<?php
require_once('../Conexion/Conexion.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Esto se puede omitir si ya está en Conexion.php
}
$database = new Conexion();
$db = $database->obtenerConexion();

// Inicializa el carrito en la sesión si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Verifica si se ha recibido el ID del producto
if (isset($_GET['id'])) {
    $idProducto = intval($_GET['id']);

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
                    'imagen' => $producto['imagen_producto']
                ];
            }
        }
    }
}

// Retorna la cantidad de productos en el carrito
$cantidadTotal = array_sum(array_column($_SESSION['carrito'], 'cantidad'));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chérie Studio</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header>
<div>
     <div class="top-banner">
        <div class="container">
            <div class="shipping-info">
                <i class="fas fa-truck"></i>
                <span>Envío gratis en compras de más de $499</span>
            </div>
            <div class="top-links">
                <span>
                    <i class="fab fa-whatsapp" style="color: #25D366; margin-right: 5px;"></i>
                    Escribe a Whatsapp +507 6609-2208
                </span>
            </div>
        </div>
    </div>
    <div class="main-header">
        <div class="container">
            <div class="logo">Chérie Studio</div>
            <div class="search-bar">
                <form action="resultados.php" method="GET"> <!-- Modificado para enviar datos a resultados.php -->
                    <input type="text" name="buscar" placeholder="Buscar..." required>
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="header-icons">
                <?php if (!isset($_SESSION['usuario'])): ?>
                    <a href="login.php" class="icon-button"><i class="fas fa-sign-in-alt"></i></a>
                    <a href="registro.php" class="icon-button"><i class="fas fa-user-plus"></i></a>
                <?php else: ?>
                    <a href="perfil.php" class="icon-button"><i class="fas fa-user"></i></a>
                <?php endif; ?>
                <a href="wishlist.php" class="icon-button"><i class="fas fa-heart"></i></a>
                <a href="carrito.php" class="icon-button cart">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count"><?php echo $cantidadTotal; ?></span> <!-- Muestra la cantidad total de productos en el carrito -->
                </a>
            </div>
        </div>
    </div>
</div>
   
</header>

</body>
</html>
