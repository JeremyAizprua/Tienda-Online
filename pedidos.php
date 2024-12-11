<?php
session_start();

// Verifica si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Supongamos que tienes un valor en $_SESSION que indica si el usuario es admin
$isAdmin = isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === true;

require_once('Conexion/Conexion.php');

// Conectar a la base de datos
$conexion = (new Conexion())->obtenerConexion();
$query = "SELECT * FROM pedidos WHERE estado = 'Pendiente' OR estado = 'Completado' ORDER BY fecha_pedido DESC";
$result = $conexion->query($query);

// Verifica si se ha enviado un formulario para actualizar el estado del pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_pedido'])) {
    $id_pedido = intval($_POST['id_pedido']);
    $nuevo_estado = $_POST['nuevo_estado'];

    // Actualiza el estado del pedido en la base de datos
    $update_query = "UPDATE pedidos SET estado = ? WHERE id_pedido = ?";
    $stmt = $conexion->prepare($update_query);
    $stmt->bind_param("si", $nuevo_estado, $id_pedido);
    $stmt->execute();
    $stmt->close();

    // Redirige para evitar reenvío de formulario
    header('Location: Pedidos.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos Recibidos</title>
    <!-- Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <style>
    body {
        background-color: #f9f9f9; /* Color de fondo */
    }
    nav {
        background-color: #7C3AED; /* Color del nav */
    }
    nav a {
        color: white; /* Color del texto del nav */
    }
    h1 {
        color: #7C3AED; /* Color del título */
        text-align: center; /* Centrar el título */
    }
    h5 {
        color: #7C3AED; /* Color del título */
        text-align: center; /* Centrar el título */
    }
    .card {
        border-radius: 10px;
        width: 100%;
        
        margin: 20px 0; /* Margen entre tarjetas */
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        max-height: 400px; /* Limita la altura máxima */
    }
    .card-content {
        overflow-y: auto; /* Habilitar scroll si es necesario */
        padding: 10px;
    }
    .card-image {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 150px; /* Altura fija para el contenedor de la imagen */
        background-color: #f0f0f0; /* Color de fondo para mejor visualización */
    }
    .card-image img {
        width: 100px; /* Tamaño cuadrado de la imagen */
        height: 100px;
        object-fit: cover; /* Recorta la imagen proporcionalmente */
        border-radius: 8px; /* Bordes ligeramente redondeados */
    }
    .row-container {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
    }
    .column {
        flex: 1 1 45%; /* Dos columnas con espacio */
        margin: 10px;
    }
    .btn {
        background-color: #7C3AED !important;
    }

    .btn:hover {
        background-color: #5c29a8 !important;
    }

    .volver-icono {
    display: inline-block;
    margin: 10px;
    color: white; /* Cambiar a blanco para que contraste con el fondo */
    background-color: #7C3AED; /* Mismo color de fondo que el botón */
    font-size: 1rem;
    text-decoration: none;
    transition: background-color 0.3s;
}

.volver-icono:hover {
    background-color: #5c29a8; /* Color al pasar el mouse */
}
</style>

</head>
<body>
    <nav>
        <div class="container">
            <a href="Perfil.php" class="brand-logo">Mi Tienda</a>
        </div>
    </nav>
    
    <div class="row-container">
        <div class="column">
            <h5>Pedidos Pendientes</h5>
            <?php
            $result->data_seek(0); // Reinicia el puntero de resultados
            while ($pedido = $result->fetch_assoc()):
                if ($pedido['estado'] !== 'Pendiente') continue;
                $detalles = json_decode($pedido['detalles'], true);
            ?>
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Pedido #<?php echo $pedido['id_pedido']; ?></span>
                    <p><strong>Total:</strong> $<?php echo number_format($pedido['total'], 2); ?></p>
                    <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($pedido['telefono']); ?></p>
                    <p><strong>Correo:</strong> <?php echo htmlspecialchars($pedido['correo']); ?></p>
                    <h5>Detalles del Pedido:</h5>
                    <div class="row">
                        <?php foreach ($detalles as $producto): ?>
                            <div class="col s12 m6 l4">
                                <div class="card">
                                    <div class="card-image">
                                        <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                    </div>
                                    <div class="card-content">
                                        <span class="card-title"><?php echo htmlspecialchars($producto['nombre']); ?></span>
                                        <p><strong>Cantidad:</strong> <?php echo $producto['cantidad']; ?></p>
                                        <p><strong>Precio:</strong> $<?php echo number_format($producto['precio'], 2); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php if ($isAdmin): ?>
                    <form method="POST" action="Pedidos.php" class="card-action">
                        <input type="hidden" name="id_pedido" value="<?php echo $pedido['id_pedido']; ?>">
                        <input type="hidden" name="nuevo_estado" value="Completado">
                        <button type="submit" name="update_pedido" class="btn waves-effect waves-light">Marcar como Completado</button>
                    </form>
                <?php endif; ?>
            </div>

            <?php endwhile; ?>
        </div>

        <div class="column">
            <h5>Pedidos Completados</h5>
            <?php
            $result->data_seek(0); // Reinicia el puntero de resultados
            while ($pedido = $result->fetch_assoc()):
                if ($pedido['estado'] !== 'Completado') continue;
                $detalles = json_decode($pedido['detalles'], true);
            ?>
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Pedido #<?php echo $pedido['id_pedido']; ?></span>
                    <p><strong>Total:</strong> $<?php echo number_format($pedido['total'], 2); ?></p>
                    <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($pedido['telefono']); ?></p>
                    <p><strong>Correo:</strong> <?php echo htmlspecialchars($pedido['correo']); ?></p>
                    <h5>Detalles del Pedido:</h5>
                    <div class="row">
                        <?php foreach ($detalles as $producto): ?>
                            <div class="col s12 m6 l4">
                                <div class="card">
                                    <div class="card-image">
                                        <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                    </div>
                                    <div class="card-content">
                                        <span class="card-title"><?php echo htmlspecialchars($producto['nombre']); ?></span>
                                        <p><strong>Cantidad:</strong> <?php echo $producto['cantidad']; ?></p>
                                        <p><strong>Precio:</strong> $<?php echo number_format($producto['precio'], 2); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php if ($isAdmin): ?>
                    <form method="POST" action="Pedidos.php" class="card-action">
                        <input type="hidden" name="id_pedido" value="<?php echo $pedido['id_pedido']; ?>">
                        <input type="hidden" name="nuevo_estado" value="Pendiente">
                        <button type="submit" name="update_pedido" class="btn waves-effect waves-light">Marcar como Pendiente</button>
                    </form>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>
</div>

    <!-- Materialize JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var elems = document.querySelectorAll('select');
            M.FormSelect.init(elems);
        });
    </script>
</body>
</html>

<?php
$conexion->close();
?>
