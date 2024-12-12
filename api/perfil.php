<?php
session_start();
require_once('../Conexion/Conexion.php');
require_once('../Clases/Pedido.php'); // Asegúrate de incluir la clase Pedidos

$database = new Conexion();
$db = $database->obtenerConexion();
$pedidos = new Pedido($db);

// Verifica si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Obtén el historial de pedidos del usuario
$id_usuario = $_SESSION['id_usuario']; // Asegúrate de tener el ID del usuario en la sesión
// Obtener el orden actual, predeterminado a "asc"
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'asc';
$historialPedidos = $pedidos->obtenerHistorialPorUsuario($id_usuario, $orden);

// Supongamos que tienes un valor en $_SESSION que indica si el usuario es admin
$isAdmin = isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === true;

// Determinar el nuevo orden para el botón
$nuevoOrden = ($orden === 'asc') ? 'desc' : 'asc';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <!-- Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f9f9f9; /* Color de fondo */
        }
        nav {
            background-color: #7C3AED; /* Color del nav */
            height: 85px;
        }
        nav a {
            color: white; /* Color del texto del nav */
        }
        h1 {
            color: #7C3AED; /* Color del título */
            text-align: center; /* Centrar el título */
            margin-bottom: 30px;
        }
        ul {
            list-style-type: none; /* Eliminar puntos de la lista */
            padding: 0; /* Eliminar padding */
            text-align: center; /* Centrar texto */
        }
        li {
            margin: 10px 0; /* Margen entre los enlaces */
        }
        a {
            color: #7C3AED; /* Color de los enlaces */
            text-decoration: none; /* Sin subrayado */
            font-weight: bold; /* Texto en negrita */
        }
        a:hover {
            color: black; /* Cambiar color al pasar el ratón */
        }
        .card {
            height: 400px;
            border-radius: 10px;
            overflow-y: auto; /* Habilitar scroll vertical si el contenido excede la altura */
        }
        .estado-pendiente {
            background-color: #FFDDDD; /* Color de fondo para estado pendiente (rojo claro) */
        }
        .estado-completo {
            background-color: #DDFFDD; /* Color de fondo para estado completo (verde claro) */
        }
        .pedido-detalle {
            margin-top: 20px;
        }
        .detalle-imagen {
            max-width: 100%;
            height: auto;
        }
        .btn{
            background: #7C3AED;
        }
    </style>
</head>
<body>
    <nav>
        <div class="container">
            <a href="Index.php" class="brand-logo">Mi Tienda</a>
        </div>
    </nav>

    <div class="container">
        <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?>!</h1>
        <nav>
            <ul>
                <li><a href="Index.php">Ir a la Tienda</a></li>
                <li><a href="logout.php">Cerrar Sesión</a></li>
                <?php if ($isAdmin): ?>
                    <li><a href="pedidos.php">Ver Pedidos Recibidos</a></li>
                    <li><a href="gestionar_productos.php">Gestionar Productos</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <div class="container">
        <h2>Historial de Pedidos</h2>
        <div class="row">
            <!-- Botón de filtrado único -->
            <div class="col s12">
                <div class="right-align">
                    <a href="?orden=<?php echo $nuevoOrden; ?>" class="btn">
                        Fecha <?php echo ($orden === 'asc') ? 'Descendente' : 'Ascendente'; ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="row">
            <?php if (empty($historialPedidos)): ?>
                <div class="col s12">
                    <div class="card">
                        <div class="card-content center-align">
                            <p>No hay historial de pedidos para mostrar.</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php $pedidoCounter = 1; ?>
                <?php foreach ($historialPedidos as $pedido): ?>  
                    <div class="col s12 m6 l4">
                        <div class="card <?php echo (strtolower($pedido['estado']) === 'pendiente') ? 'estado-pendiente' : 'estado-completo'; ?>">
                            <div class="card-content">
                                <span class="card-title">Pedido #<?php echo $pedidoCounter++; ?></span>
                                <p><strong>Fecha:</strong> <?php echo htmlspecialchars($pedido['fecha_pedido']); ?></p>
                                <p><strong>Total:</strong> $<?php echo htmlspecialchars($pedido['total']); ?></p>
                                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($pedido['telefono']); ?></p>
                                <p><strong>Correo:</strong> <?php echo htmlspecialchars($pedido['correo']); ?></p>
                                <p><strong>Estado:</strong> <?php echo (strtolower($pedido['estado']) === 'completado') ? 'Procesado' : htmlspecialchars($pedido['estado']); ?></p>
                                <h5>Detalles del Pedido</h5>
                                <?php 
                                $detalles = json_decode($pedido['detalles'], true); 
                                if (!empty($detalles)): 
                                    foreach ($detalles as $detalle): 
                                ?>
                                    <div class="card pedido-detalle">
                                        <div class="card-content">
                                            <span class="card-title"><?php echo htmlspecialchars($detalle['nombre']); ?></span>
                                            <p><strong>Precio:</strong> $<?php echo htmlspecialchars($detalle['precio']); ?></p>
                                            <p><strong>Cantidad:</strong> <?php echo htmlspecialchars($detalle['cantidad']); ?></p>
                                            <img src="<?php echo htmlspecialchars($detalle['imagen']); ?>" alt="<?php echo htmlspecialchars($detalle['nombre']); ?>" class="detalle-imagen">
                                            <p><strong>Descripción:</strong> <?php echo htmlspecialchars($detalle['descripcion']); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No hay detalles disponibles para este pedido.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
