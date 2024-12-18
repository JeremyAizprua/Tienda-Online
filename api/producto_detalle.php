<?php
session_start();
require_once('../Conexion/Conexion.php');
require_once('../Clases/Productos.php');
require_once('../Clases/Categoria.php');

$database = new Conexion();
$db = $database->obtenerConexion();
$productosModel = new Productos($db);
$categoriaModel = new Categoria($db);

$id_producto = isset($_POST['id']) ? intval($_POST['id']) : 0;
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
            background-color: #eed2ef;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .product-card {
            border: 2px solid #e582e7;
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
            color: black;
            margin-bottom: 20px;
        }
        .btn {
            background-color: #e582e7;
            color: black;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #e3a3e5;
        }
        .volver-icono {
            display: inline-block;
            margin-bottom: 20px;
            color: #e3a3e5;
            font-size: 1.8rem;
            text-decoration: none;
            transition: color 0.3s;
        }
        .volver-icono:hover {
            color: #e582e7;
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
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .carousel-item {
            border: 2px solid #e582e7;
            flex: 0 0 calc(25% - 20px);
            max-width: calc(25% - 20px);
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .carousel-item:hover {
            transform: translateY(-5px);
        }
        .carousel-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .carousel-item-content {
            padding: 15px;
        }
        .carousel-item-title {
            font-size: 16px;
            color: #333;
            margin-bottom: 5px;
        }
        .carousel-item-price {
            color: black;
            font-weight: bold;
        }
        .carousel-item a {
            text-decoration: none;
            color: inherit;
        }
        @media (max-width: 768px) {
            .product-card {
                flex-direction: column;
            }
            .product-image, .product-info {
                max-width: 100%;
            }
            .carousel-item {
                flex: 0 0 calc(50% - 20px);
                max-width: calc(50% - 20px);
            }
        }
        @media (max-width: 480px) {
            .carousel-item {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
        .carousel-item form {
            width: 100%;
            height: 100%;
        }
        .carousel-item button {
            width: 100%;
            height: 100%;
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            text-align: left;
        }
        .add-to-wishlist {
            background-color: white;
            color: black;
            border: 2px solid black;
            border-radius: 5px 5px 5px 5px;
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.3s, border-color 0.3s;
        }
        .add-to-wishlist:hover {
            background-color: #b2eeeb;
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
                    <button class="btn" title="Añadir al carrito" onclick="agregarAlCarrito(<?php echo $producto['id_producto']; ?>)">
                        <i class="fas fa-shopping-cart"></i>
                    </button>
                    <button class="add-to-wishlist" title="Añadir a la lista de deseos" onclick="agregarAListaDeseos(<?php echo $producto['id_producto']; ?>)">
                        <i class="fas fa-heart"></i>
                    </button>
            </div>
        </div>
        <div class="related-products">
            <h2>Productos relacionados</h2>
            <div class="carousel">
                <?php foreach ($productos_relacionados as $producto_relacionado): ?>
                    <div class="carousel-item">
                        <form action="producto_detalle.php" method="post">
                            <input type="hidden" name="id" value="<?php echo $producto_relacionado['id_producto']; ?>">
                            <button type="submit">
                                <img src="<?php echo $producto_relacionado['imagen_producto']; ?>" alt="<?php echo htmlspecialchars($producto_relacionado['nombre_producto']); ?>">
                                <div class="carousel-item-content">
                                    <div class="carousel-item-title"><?php echo htmlspecialchars($producto_relacionado['nombre_producto']); ?></div>
                                    <div class="carousel-item-price">$<?php echo number_format($producto_relacionado['precio'], 2); ?></div>
                                </div>
                            </button>
                        </form>
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
                    } else {
                        alert('Error al añadir al carrito');
                    }
                })
                .catch(error => console.error('Error:', error));
        }
        function agregarAListaDeseos(idProducto) {
        fetch('agregar_a_lista_deseos.php?id=' + idProducto)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Producto añadido a la lista de deseos.');
                } else {
                    alert('Error al añadir a la lista de deseos, debe iniciar sesion.');
                    window.location.href = 'login.php';
                }
            })
            .catch(error => console.error('Error:', error));
    }
    </script>
</body>
</html>

