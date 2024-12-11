<?php
session_start();
require_once('Conexion/Conexion.php');
require_once('Clases/Productos.php');

$database = new Conexion();
$db = $database->obtenerConexion();
$productosModel = new Productos($db);

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit();
}

$usuario_id = $_SESSION['id_usuario'];

// Obtener los productos de la lista de deseos del usuario
$query = "SELECT p.* FROM productos p
          INNER JOIN lista_deseos ld ON p.id_producto = ld.id_producto
          WHERE ld.id_usuario = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$productos_deseados = $resultado->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Lista de Deseos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .wishlist-item {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .wishlist-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .wishlist-item-content {
            padding: 15px;
        }
        .wishlist-item-title {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .wishlist-item-price {
            font-weight: bold;
            color: #7C3AED;
        }
        .wishlist-item-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-remove {
            background-color: #ff4d4d;
            color: white;
        }
        .btn-remove:hover {
            background-color: #ff3333;
        }
        .btn-cart {
            background-color: #7C3AED;
            color: white;
        }
        .btn-cart:hover {
            background-color: #6A1B9A;
        }


        .volver-icono {
            display: inline-block;
            margin: 10px;
            color: #7C3AED;
            font-size: 1.8rem;
            text-decoration: none;
            transition: color 0.3s;
        }

        .volver-icono:hover {
            color: #5c29a8;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
    <a href="Index.php" class="volver-icono">
        <i class="fas fa-arrow-left"></i>
    </a>
        <h1>Mi Lista de Deseos</h1>
        <div class="wishlist-grid">
            <?php foreach ($productos_deseados as $producto): ?>
                <div class="wishlist-item">
                    <img src="<?php echo $producto['imagen_producto']; ?>" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
                    <div class="wishlist-item-content">
                        <h2 class="wishlist-item-title"><?php echo htmlspecialchars($producto['nombre_producto']); ?></h2>
                        <p class="wishlist-item-price">$<?php echo number_format($producto['precio'], 2); ?></p>
                        <div class="wishlist-item-actions">
                            <button class="btn btn-remove" onclick="quitarDeListaDeseos(<?php echo $producto['id_producto']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button class="btn btn-cart" onclick="agregarAlCarrito(<?php echo $producto['id_producto']; ?>)">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function quitarDeListaDeseos(idProducto) {
            fetch('quitar_de_lista_deseos.php?id=' + idProducto)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Producto quitado de la lista de deseos');
                        location.reload();
                    } else {
                        alert('Error al quitar de la lista de deseos');
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function agregarAlCarrito(idProducto) {
            fetch('agregar_al_carrito.php?id=' + idProducto)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector('.cart-count').innerText = data.cantidad;
                        alert('Producto añadido al carrito');
                    } else {
                        alert('Error al añadir al carrito');
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>

