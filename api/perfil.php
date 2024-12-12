<?php
session_start();
require_once('../Conexion/Conexion.php');
require_once('../Clases/Pedido.php');

$database = new Conexion();
$db = $database->obtenerConexion();
$pedidos = new Pedido($db);

// Verifica si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario']) || !isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

// Obtén el historial de pedidos del usuario
$id_usuario = $_SESSION['id_usuario'];
// Obtener el orden actual, predeterminado a "asc"
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'asc';
$historialPedidos = $pedidos->obtenerHistorialPorUsuario($id_usuario, $orden);

// Supongamos que tienes un valor en $_SESSION que indica si el usuario es admin
$isAdmin = isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === true;

// Determinar el nuevo orden para el botón
$nuevoOrden = ($orden === 'asc') ? 'desc' : 'asc';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            color: #333;
            font-family: 'Roboto', sans-serif;
        }
        .navbar-fixed {
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        nav {
            background-color: #c859ca;
        }
        nav .brand-logo {
            font-weight: bold;
            padding-left: 15px;
        }
        .user-info {
            background-color: #c859ca;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .user-info h4 {
            margin: 0;
            font-weight: 300;
        }
        .user-actions {
            margin-top: 20px;
        }
        .user-actions a {
            margin: 5px;
        }
        h2 {
            color: #c859ca;
            font-weight: 300;
            margin-bottom: 30px;
        }
        .card {
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .card .card-content {
            padding: 20px;
        }
        .card-title {
            font-weight: bold;
            color: #c859ca;
        }
        .estado-pendiente {
            border-left: 5px solid #FFA000;
        }
        .estado-completo {
            border-left: 5px solid #4CAF50;
        }
        .pedido-detalle {
            margin-top: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
        }
        .detalle-imagen {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }
        .btn {
            background-color: #c859ca;
            margin-right: 10px;
        }
        .btn:hover {
            background-color: #6D28D9;
        }
        .btn-flat {
            color: #7C3AED;
        }
        @media only screen and (max-width: 992px) {
            nav .brand-logo {
                left: 50%;
                transform: translateX(-50%);
            }
        }
    </style>
</head>
<body>
    <div class="navbar-fixed">
        <nav>
            <div class="nav-wrapper">
                <a href="Index.php" class="brand-logo">Mi Tienda</a>
                <a href="#" data-target="mobile-demo" class="sidenav-trigger"><i class="material-icons">menu</i></a>
                <ul class="right hide-on-med-and-down">
                    <li><a href=".Index.php">Inicio</a></li>
                    <li><a href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </div>
        </nav>
    </div>

    <ul class="sidenav" id="mobile-demo">
        <li><a href="Index.php">Inicio</a></li>
        <li><a href="logout.php">Cerrar Sesión</a></li>
    </ul>

    <div class="user-info">
        <div class="container">
            <h4 class="center-align">Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?>!</h4>
            <div class="user-actions center-align">
                <a href="Index.php" class="btn waves-effect waves-light">Ir a la Tienda</a>
                <?php if ($isAdmin): ?>
                    <a href="pedidos.php" class="btn waves-effect waves-light">Ver Pedidos Recibidos</a>
                    <a href="gestionar_productos.php" class="btn waves-effect waves-light">Gestionar Productos</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container">
        <h2 class="center-align">Historial de Pedidos</h2>
        <div class="row">
            <div class="col s12">
                <div class="right-align">
                    <a href="?orden=<?php echo $nuevoOrden; ?>" class="btn waves-effect waves-light">
                        <i class="material-icons left">sort</i>
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
                <?php foreach ($historialPedidos as $index => $pedido): ?>  
                    <div class="col s12 m6">
                        <div class="card <?php echo (strtolower($pedido['estado']) === 'pendiente') ? 'estado-pendiente' : 'estado-completo'; ?>">
                            <div class="card-content">
                                <span class="card-title">Pedido #<?php echo $index + 1; ?></span>
                                <p><i class="material-icons tiny">date_range</i> <strong>Fecha:</strong> <?php echo htmlspecialchars($pedido['fecha_pedido']); ?></p>
                                <p><i class="material-icons tiny">attach_money</i> <strong>Total:</strong> $<?php echo htmlspecialchars($pedido['total']); ?></p>
                                <p><i class="material-icons tiny">phone</i> <strong>Teléfono:</strong> <?php echo htmlspecialchars($pedido['telefono']); ?></p>
                                <p><i class="material-icons tiny">email</i> <strong>Correo:</strong> <?php echo htmlspecialchars($pedido['correo']); ?></p>
                                <p><i class="material-icons tiny">info</i> <strong>Estado:</strong> <?php echo (strtolower($pedido['estado']) === 'completado') ? 'Procesado' : htmlspecialchars($pedido['estado']); ?></p>
                                <div class="card-action">
                                    <a class="btn-flat activator">Ver Detalles</a>
                                </div>
                            </div>
                            <div class="card-reveal">
                                <span class="card-title grey-text text-darken-4">Detalles del Pedido<i class="material-icons right">close</i></span>
                                <?php 
                                $detalles = json_decode($pedido['detalles'], true); 
                                if (!empty($detalles)): 
                                    foreach ($detalles as $detalle): 
                                ?>
                                    <div class="pedido-detalle">
                                        <h6><?php echo htmlspecialchars($detalle['nombre']); ?></h6>
                                        <p><strong>Precio:</strong> $<?php echo htmlspecialchars($detalle['precio']); ?></p>
                                        <p><strong>Cantidad:</strong> <?php echo htmlspecialchars($detalle['cantidad']); ?></p>
                                        <img src="<?php echo htmlspecialchars($detalle['imagen']); ?>" alt="<?php echo htmlspecialchars($detalle['nombre']); ?>" class="detalle-imagen">
                                        <p><strong>Descripción:</strong> <?php echo htmlspecialchars($detalle['descripcion']); ?></p>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var elems = document.querySelectorAll('.sidenav');
            var instances = M.Sidenav.init(elems);
        });
    </script>
</body>
</html>

