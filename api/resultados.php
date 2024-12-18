<?php
session_start(); // Inicia la sesión
require_once('../Conexion/Conexion.php');
$database = new Conexion();
$db = $database->obtenerConexion();

// Inicializa la variable de búsqueda
$terminoBusqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';

// Prepara la consulta para buscar productos que coincidan con el término de búsqueda
$query = "SELECT * FROM productos WHERE nombre_producto LIKE ?";
$stmt = $db->prepare($query);
$terminoBusquedaParam = "%$terminoBusqueda%";
$stmt->bind_param("s", $terminoBusquedaParam);
$stmt->execute();
$resultado = $stmt->get_result();

// Inicializa el array de productos
$productos = [];

// Si hay resultados, los almacena en el array
while ($producto = $resultado->fetch_assoc()) {
    $productos[] = $producto;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de Búsqueda</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body, html {
            background-color: #eed2ef;
            height: 100%;
            margin: 0;
        }

        .volver-icon {
            color: #e3a3e5;
            font-size: 24px;
            margin: 20px;
            transition: color 0.3s;
        }

        .volver-icon:hover {
            color: #e582e7;
        }

        .container3 {
            max-width: 100%;
            padding: 0 15px;
            margin: auto;
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: -10px;
        }

        .col {
            flex: 0 0 100%;
            max-width: 100%;
            padding: 10px;
        }

        .card {
            height: 530px;
            width: 100%;
            border: 2px solid #e582e7;
            border-radius: 15px;
            overflow: hidden;
            margin-top: 10px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-image img {
            height: 275px;
            width: 100%;
            object-fit: cover;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            transition: transform 0.3s ease;
        }

        .card-image:hover img {
            transform: scale(1.1);
        }

        .card-content {
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            text-align: center;
        }

        .card-title {
            font-size: 1.5em;
            margin-bottom: 15px;
            color: #333;
        }

        .card-actions {
            display: flex;
            margin: -2%;
            flex-direction: column-reverse;
            flex-wrap: nowrap;
            align-items: stretch;
        }

        .add-to-cart {
            background-color: #e582e7;
            border: none;
            color: black;
            border-radius: 0px 0px 5px 5px;
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .add-to-cart:hover {
            background-color: #e3a3e5;
        }

        .add-to-wishlist {
            background-color: white;
            color: black;
            border: 2px solid black;
            border-radius: 5px 5px 0px 0px;
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.3s, border-color 0.3s;
        }

        .add-to-wishlist:hover {
            background-color: #b2eeeb;
        }

        .view-details {
            text-align: center;
            background-color: #f0f0f0;
            color: black;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
            margin-top: 5px;
        }

        .view-details:hover {
            background-color: #e0e0e0;
        }

        .ojo {
            display: flex;
            margin: -2%;
            flex-direction: column-reverse;
            flex-wrap: nowrap;
            align-items: stretch;
        }

        h1 {
            text-align: center;
            color: black;
            margin-top: 20px;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            display: flex;
            flex-direction: column;
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 2px solid #e582e7;
            width: 80%;
            max-width: 600px;
            border-radius: 10px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        #product-detail-image {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }

        #product-detail-title {
            font-size: 24px;
            margin-top: 15px;
        }

        #product-detail-price {
            font-size: 18px;
            font-weight: bold;
            color: black;
            margin-top: 10px;
        }

        #product-detail-description {
            margin-top: 15px;
        }

        #modal-add-to-cart {
            display: flex;
            justify-content: center;
            background-color: #e582e7;
            border: none;
            color: black;
            border-radius: 5px;
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        #modal-add-to-cart:hover {
            background-color: #e3a3e5;
        }

        /* Responsive styles */
        @media (max-width: 575px) {
            .col {
                flex: 0 0 50%;
                max-width: 50%;
            }
            .card {
                height: 100%;
            }
            .card-content {
                padding: 10px;
            }
            .card-title {
                font-size: 1em;
            }
            .card-image img {
                height: 80px;
            }
        }

        @media (min-width: 576px) and (max-width: 991px) {
            .col {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }

        @media (min-width: 992px) {
            .col {
                flex: 0 0 25%;
                max-width: 25%;
            }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container3">
    <h1>Resultados de búsqueda de: <?php echo htmlspecialchars($terminoBusqueda); ?></h1>
    <a href="javascript:history.back()" class="volver-icon">
        <i class="fas fa-arrow-left"></i>
    </a>

    <div class="row">
        <?php foreach ($productos as $index => $producto): ?>
            <div class="col s12 m4">
                <div class="card">
                    <div class="card-image">
                        <img src="<?php echo $producto['imagen_producto']; ?>" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
                        <div class="ojo">
                            <a href="#" class="view-details" title="Ver detalles" data-id="<?php echo $producto['id_producto']; ?>">
                                <i class="fas fa-eye"></i> Ver más
                            </a>
                        </div>
                    </div>
                    <div class="card-content">
                        <span class="card-title"><?php echo htmlspecialchars($producto['nombre_producto']); ?></span>
                        <p><?php echo htmlspecialchars($producto['precio']); ?></p><br>
                        <div class="card-actions">
                            <button class="add-to-cart" title="Añadir al carrito" onclick="agregarAlCarrito(<?php echo $producto['id_producto']; ?>)">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                            <button class="add-to-wishlist" title="Añadir a la lista de deseos" onclick="agregarAListaDeseos(<?php echo $producto['id_producto']; ?>)">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php if (($index + 1) % 4 == 0): ?>
                </div><div class="row">
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal for product details -->
<div id="product-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <img id="product-detail-image" src="" alt="Product Image">
        <h2 id="product-detail-title"></h2>
        <p id="product-detail-price"></p>
        <p id="product-detail-description"></p>
        <button id="modal-add-to-cart">
            <i class="fas fa-shopping-cart"></i>
        </button>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script>
    var modal = document.getElementById("product-modal");
    var span = document.getElementsByClassName("close")[0];

    span.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    function showProductDetails(productId) {
        fetch(`get_product_details.php?id=${productId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById("product-detail-image").src = data.imagen_producto;
                document.getElementById("product-detail-title").textContent = data.nombre_producto;
                document.getElementById("product-detail-price").textContent = `Precio: $${data.precio}`;
                document.getElementById("product-detail-description").textContent = data.descripcion;
                document.getElementById("modal-add-to-cart").onclick = function() {
                    agregarAlCarrito(data.id_producto);
                };
                modal.style.display = "block";
            })
            .catch(error => console.error('Error:', error));
    }

    document.querySelectorAll('.view-details').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const productId = e.currentTarget.getAttribute('data-id');
            showProductDetails(productId);
        });
    });

    function agregarAlCarrito(idProducto) {
        fetch('agregar_al_carrito.php?id=' + idProducto)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector('.cart-count').innerText = data.cantidad;
                    alert('Producto añadido al carrito!');
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
                    alert('Producto añadido a la lista de deseos');
                } else {
                    alert('Error al añadir a la lista de deseos');
                }
            })
            .catch(error => console.error('Error:', error));
    }
</script>
</body>
</html>
