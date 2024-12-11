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
        echo "Error al preparar la consulta: " . $db->error;
    }
} else {
    // Obtener todos los productos si no se ha seleccionado una categoría
    $productos = $productosModel->obtenerTodosLosProductos();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda Online - Productos</title>  
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        body, html {
            height: 100%;
            margin: 0;
        }

        .wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%; /* Hace que el contenedor ocupe toda la ventana */
        }
        nav {
            background: transparent; /* Hacer el fondo del nav transparente */
            padding: 10px 0; /* Espaciado interno */
            border: none; /* Eliminar bordes */
            box-shadow: none; /* Eliminar sombra */
        }
        nav a {
            color: #7C3AED; /* Color de los enlaces */
            text-decoration: none; /* Sin subrayado */
            font-weight: bold; /* Texto en negrita */
        }
        section {
            background-color: #f0f0f0f0;
            border-radius: 15px;
            border: 2px solid #7C3AED;
            display: flex;
            overflow: hidden;
            justify-content: center; /* Centrar horizontalmente */
            align-items: center; /* Centrar verticalmente */
            margin: auto auto auto auto; /* Centrarse respecto al contenedor principal */
            width: 81%;
        }

        section img {
            width: 210px; /* Ancho de la imagen */
            height: 250px;
            object-fit: cover; /* Ajustar imagen */
            opacity: .8;
            transition: 0.5s ease; /* Transición suave */
        }

        section img:hover {
            cursor: pointer; /* Cambiar cursor al pasar sobre la imagen */
            width: 400px;
            opacity: 1; /* Cambiar opacidad al pasar el cursor */
            filter: contrast(120%); /* Mejorar contraste */
        }
        
        section .carousel {
            
            display: flex; /* Usar flexbox para apilar las imágenes horizontalmente */
            animation: slide 10s linear infinite; /* Animación para el desplazamiento */
        }
        @keyframes slide {
            0% {
                transform: translateX(100%); /* Comienza fuera de la pantalla a la derecha */
            }
            25% {
                transform: translateX(0); /* Llega al centro */
            }
            75% {
                transform: translateX(0); /* Mantiene el centro */
            }
            100% {
                transform: translateX(-100%); /* Sale de la pantalla a la izquierda */
            }
        }


        .card:hover {
            transform: translateY(-5px);
        }

        .card-image img {
            height: 275px;
            width: 100%; /* Asegura que la imagen ocupe el ancho completo */
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
            align-items: center;
        }

        .card-title {
            font-size: 1.5em;
            margin-bottom: 15px;
            color: #333;
        }

        .add-to-cart {
            background-color: #7C3AED;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .add-to-cart:hover {
            background-color: #6A1B9A;
        }

        .container2 {
            max-width: 60%;
            margin: 0 auto;
            padding: 0 15px;
        }
        .container3 {
            max-width: 100%;
            padding: 0 15px;
            margin: auto;
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: -10px; /* Negative margin to counteract padding on cols */
        }


        .col {
            flex: 0 0 100%;
            max-width: 100%;
            padding: 10px;
        }
        .card {
            height: 530px; /* Allow height to adjust based on content */
            width: 100%;
            border: 2px solid #7C3AED;
            border-radius: 15px;
            overflow: hidden;
            margin-top: 10px;  /* Espaciado entre cards */
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }

        /* Responsive breakpoints */
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
        .card-content p:nth-child(2) {
            display: none;
        }
        .card-image img {
                height: 80px;
                width: 100%; /* Asegura que la imagen ocupe el ancho completo */
                border-top-left-radius: 15px;
                border-top-right-radius: 15px;
                transition: transform 0.3s ease;
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
/* Responsive: para pantallas más pequeñas */
@media (max-width: 768px) {
    nav ul {
        flex-direction: column; /* Cambia a columna en pantallas pequeñas */
        align-items: center; /* Centra los elementos en la dirección vertical */
    }

    nav li {
        margin: 5px 0; /* Reduce el margen entre elementos */
    }
}

@media (max-width: 576px) {
    nav a {
        font-size: 14px; /* Ajusta el tamaño del texto en dispositivos más pequeños */
    }
}
.card-link {
    text-decoration: none;
    color: inherit;
}

.card-link:hover {
    text-decoration: none;
}
        .card-actions {
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin-top: 10px;
        }
        .add-to-wishlist {
            background-color: #FFA500;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .add-to-wishlist:hover {
            background-color: #FF8C00;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?> <!-- Incluir el archivo header.php -->
<nav class="cat">
        <div class="container2">
            <ul>
                <?php foreach ($categorias as $categoria): ?>
                    <li>
                        <a href="busqueda_categoria.php?categoria=<?php echo $categoria['id_categoria']; ?>">
                            <?php echo htmlspecialchars($categoria['nombre_categoria']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav><br><br>
<section>
    <div class="carousel">
        <?php foreach ($productos as $producto): ?>
            <img src="<?php echo $producto['imagen_producto']; ?>" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
        <?php endforeach; ?>
    </div>
</section>

<div class="container3">
    <div class="row">
        <?php foreach ($productos as $index => $producto): ?>
            <div class="col s12 m4">
                    <div class="card">
                        <div class="card-image">
                            <img src="<?php echo $producto['imagen_producto']; ?>" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
                        </div>
                        <div class="card-content">
                            <span class="card-title"><?php echo htmlspecialchars($producto['nombre_producto']); ?></span>
                            <p><?php echo htmlspecialchars($producto['descripcion']); ?></p><br>
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

<!-- Materialize JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
<script>
    function agregarAlCarrito(idProducto) {
        fetch('agregar_al_carrito.php?id=' + idProducto)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualiza el contador del carrito
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
                        alert('Producto añadido a la lista de deseos');
                    } else {
                        alert('Error al añadir a la lista de deseos');
                    }
                })
                .catch(error => console.error('Error:', error));
        }
</script>

</html>