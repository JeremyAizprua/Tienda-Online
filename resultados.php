<?php
require_once('Conexion/Conexion.php');
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
        .volver-icon {
            color: #7C3AED; /* Color del icono */
            font-size: 24px; /* Tamaño del icono */
            margin: 20px; /* Espaciado */
            transition: color 0.3s; /* Transición para hover */
        }

        .volver-icon:hover {
            color: #6A1B9A; /* Color al pasar el mouse */
        }

        .card {
            height: 530px; /* Altura fija para las cards */
            width: 350px;  /* Ancho fijo para las cards */
            border: 2px solid #7C3AED;
            border-radius: 15px;
            overflow: hidden;
            margin: 10px;  /* Espaciado entre cards */
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-image img {
            height: 260px;
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
            padding: 30px;
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

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center; /* Centra las tarjetas en la fila */
        }

        .col {
            flex: 0 0 33.33%; /* Tres columnas (33.33% cada una) */
            display: flex;
            justify-content: center; /* Centra las tarjetas en la columna */
            padding: 10px;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?> <!-- Incluir el archivo header.php -->

<div class="container">
    <!-- Ícono de volver -->
    <a href="javascript:history.back()" class="volver-icon">
        <i class="fas fa-arrow-left"></i>
    </a>

    <div class="row">
        <?php foreach ($productos as $index => $producto): ?>
            <div class="col s12 m4"> <!-- Cambiar para usar m4 para 3 columnas -->
                <div class="card">
                    <div class="card-image">
                        <img src="<?php echo $producto['imagen_producto']; ?>" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
                    </div>
                    <div class="card-content">
                        <span class="card-title"><?php echo htmlspecialchars($producto['nombre_producto']); ?></span>
                        <p><?php echo htmlspecialchars($producto['descripcion']); ?></p><br>
                        <p><?php echo htmlspecialchars($producto['precio']); ?></p><br>
                        <button class="add-to-cart" title="Añadir al carrito" onclick="agregarAlCarrito(<?php echo $producto['id_producto']; ?>)">
                            <i class="fas fa-shopping-cart"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php if (($index + 1) % 3 === 0): ?>
                </div><div class="row"> <!-- Cerrar y abrir fila cada 3 productos -->
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<!-- Materialize JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

<script>
    function agregarAlCarrito(idProducto) {
        fetch('agregar_al_carrito.php?id=' + idProducto)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualiza el contador del carrito
                    document.querySelector('.cart-count').innerText = data.cantidad;
                    alert('Producto añadido al carrito!');
                } else {
                    alert('Error al añadir al carrito');
                }
            })
            .catch(error => console.error('Error:', error));
    }
</script>
</body>
</html>
