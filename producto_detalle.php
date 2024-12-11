<?php
session_start();
require_once('Conexion/Conexion.php');
require_once('Clases/Productos.php');
require_once('Clases/Categoria.php');

$database = new Conexion();
$db = $database->obtenerConexion();
$productosModel = new Productos($db);
$categoriaModel = new Categoria($db);

$id_producto = isset($_GET['id']) ? intval($_GET['id']) : 0;
$producto = $productosModel->obtenerProductoPorId($id_producto);

if (!$producto) {
    header("Location: index.php");
    exit();
}

$productos_relacionados = $productosModel->obtenerProductosRelacionados($producto['id_categoria'], $id_producto, 8);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($producto['nombre_producto']); ?> - Detalle</title>
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
        .product-card {
            display: flex;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }
        .product-image {
            flex: 1;
            max-width: 500px;
        }
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .product-info {
            flex: 1;
            padding: 20px;
        }
        .product-title {
            font-size: 24px;
            margin-bottom: 10px;
            color: #333;
        }
        .product-description {
            margin-bottom: 15px;
            color: #666;
        }
        .product-price {
            font-size: 20px;
            font-weight: bold;
            color: #7C3AED;
            margin-bottom: 20px;
        }
        .btn {
            background-color: #7C3AED;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #5c29a8;
        }
        .volver-icono {
            display: inline-block;
            margin-bottom: 20px;
            color: #7C3AED;
            font-size: 1.8rem;
            text-decoration: none;
            transition: color 0.3s;
        }
        .volver-icono:hover {
            color: #5c29a8;
        }
        .related-products {
            margin-top: 40px;
        }
        .related-products h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .carousel {
            display: flex;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .carousel::-webkit-scrollbar {
            display: none;
        }
        .carousel-item {
            flex: 0 0 auto;
            width: 250px;
            margin-right: 20px;
            scroll-snap-align: start;
        }
        .carousel-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }
        .carousel-item-title {
            margin-top: 10px;
            font-size: 16px;
            color: #333;
        }
        .carousel-item-price {
            color: #7C3AED;
            font-weight: bold;
        }
        @media (max-width: 768px) {
            .product-card {
                flex-direction: column;
            }
            .product-image, .product-info {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <a href="Index.php" class="volver-icono">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="product-card">
            <div class="product-image">
                <img src="<?php echo $producto['imagen_producto']; ?>" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
            </div>
            <div class="product-info">
                <h1 class="product-title"><?php echo htmlspecialchars($producto['nombre_producto']); ?></h1>
                <p class="product-description"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                <p class="product-price">Precio: $<?php echo number_format($producto['precio'], 2); ?></p>
                <button class="btn" onclick="agregarAlCarrito(<?php echo $producto['id_producto']; ?>)">
                    Añadir al carrito
                </button>
            </div>
        </div>

        <div class="related-products">
            <h2>Productos relacionados</h2>
            <div class="carousel">
                <?php foreach ($productos_relacionados as $producto_relacionado): ?>
                    <div class="carousel-item">
                        <a href="producto_detalle.php?id=<?php echo $producto_relacionado['id_producto']; ?>">
                            <img src="<?php echo $producto_relacionado['imagen_producto']; ?>" alt="<?php echo htmlspecialchars($producto_relacionado['nombre_producto']); ?>">
                            <div class="carousel-item-title"><?php echo htmlspecialchars($producto_relacionado['nombre_producto']); ?></div>
                            <div class="carousel-item-price">$<?php echo number_format($producto_relacionado['precio'], 2); ?></div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
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

