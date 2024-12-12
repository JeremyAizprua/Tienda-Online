<?php

session_start();
$productosEnCarrito = $_SESSION['carrito'] ?? [];
$total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Compra</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <script
            src="https://www.paypal.com/sdk/js?client-id=Aa1fmzD2wXijkEiAlCWL8EnzySKozf7Qx3AO_Zpi1tP_Lc1P2iS9GTVfheze9qach_dYhV9NK4Th-hv7&buyer-country=US&currency=USD&components=buttons&enable-funding=venmo,paylater,card"
            data-sdk-integration-source="developer-studio"
        ></script>
    <style>
        body {
            background-color: #f4f4f4;
            color: #000;
            display: flex;
            justify-content: center; /* Centrar horizontalmente */
            align-items: center; /* Centrar verticalmente */
            height: 100vh;
            margin: 0;
            background-image: url('https://sumamosdesign.com/wp-content/uploads/2020/06/tv1.jpg'); /* URL de la imagen de fondo */
            background-size: cover; /* Cubrir toda la pantalla */
            background-position: center; /* Centrar la imagen */
            background-attachment: fixed; /* Mantener la imagen fija durante el scroll */
        }

        .container {
            width: 90%;
            max-width: 800px;
            max-height: 80vh; /* Altura máxima del contenedor */
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            overflow-y: auto; /* Habilitar desplazamiento vertical si es necesario */
        }

        .btn {
            background-color: #7C3AED !important;
        }

        .btn:hover {
            background-color: #5c29a8 !important;
        }

        .volver-btn {
            margin-bottom: 20px;
        }

        .card-horizontal {
            display: flex;
            flex-direction: row;
            align-items: center;
            margin-bottom: 20px;
            border: 2px solid #7C3AED;
            border-radius: 10px;
            background-color: #fff;
            padding: 15px;
        }

        .card-horizontal img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 15px;
        }

        .card-horizontal .card-content {
            flex: 1;
        }

        .card-horizontal .card-title {
            color: #7C3AED;
            font-weight: bold;
            margin-bottom: 10px;
        }

        h5 {
            color: #7C3AED;
            font-size: 1.8rem;
            text-align: center;
        }

        .total {
            font-weight: bold;
            color: #000;
            margin-top: 20px;
            text-align: center;
        }

        .payment-options {
            width: 100%;
            margin-top: 30px;
            text-align: center;
        }

        .payment-options #paypal-button-container {
            max-width: 400px;
            margin: 0 auto;
        }

        .payment-options h5 {
            margin-bottom: 10px;
            font-size: 1.2rem;
        }

        /* Estilos para el overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none; /* Oculto por defecto */
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .overlay .card {
            border-radius: 20px; /* Aumenta el radio de la esquina para hacerlo más redondeado */
            width: 300px;
            text-align: center;
            background-color: #fff; /* Asegúrate de que el fondo de la tarjeta sea blanco */
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2); /* Añadir sombra para un mejor efecto visual */
        }

        .overlay .card .card-content {
            padding: 20px;
        }

    </style>
</head>
<body>
<br><br>
<div class="container">
    <a href="carrito.php" class="btn volver-btn waves-effect">
    <i class="fas fa-arrow-left"></i>Volver
    </a>
    <h5>Resumen de compra:</h5>
    <div class="row">
        <?php if (empty($productosEnCarrito)): ?>
            <p>No hay productos en el carrito.</p>
        <?php else: ?>
            <?php foreach ($productosEnCarrito as $id => $producto): ?>
                <div class="col s12">
                    <div class="card-horizontal">
                        <img src="<?php echo $producto['imagen']; ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                        <div class="card-content">
                            <span class="card-title"><?php echo htmlspecialchars($producto['nombre']); ?></span>
                            <p>Precio: $<?php echo number_format($producto['precio'], 2); ?></p>
                            <p>Cantidad: <?php echo $producto['cantidad']; ?></p>
                            <p>Subtotal: $<?php echo number_format($producto['precio'] * $producto['cantidad'], 2); ?></p>
                        </div>
                    </div>
                </div>
                <?php 
                $total += $producto['precio'] * $producto['cantidad']; 
                endforeach; 
                ?>
        <?php endif; ?>
    </div>
    <h5 class="total">Total: $<?php echo number_format($total, 2); ?></h5>
    <div class="payment-options">
        <div class="payment-options">
            <button id="confirmarPedido" class="btn">Confirmar Pedido</button>
            <strong><p id="mensaje"></p></strong>
        </div>
    </div>
</div>

<!-- Overlay para mostrar el mensaje de confirmación -->
<div class="overlay" id="overlay">
    <div class="card">
        <div class="card-content">
            <h5 id="mensajeConfirmacion"></h5>
            <button id="cerrarOverlay" class="btn">Cerrar</button>
        </div>
    </div>
</div>

<script>
    document.getElementById('confirmarPedido').addEventListener('click', async () => {
        const button = document.getElementById('confirmarPedido');
        const mensaje = document.getElementById('mensaje');
        const mensajeConfirmacion = document.getElementById('mensajeConfirmacion');
        const overlay = document.getElementById('overlay');
        const total = "<?php echo $total; ?>";
        const detalles = <?php echo json_encode($productosEnCarrito); ?>;
        const telefono = "<?php echo htmlspecialchars($_SESSION['telefono'] ?? ''); ?>";

        const data = {
            total: total,
            detalles: JSON.stringify(detalles),
            telefono: telefono
        };
        button.style.display = 'none';
        mensaje.style.color ='Green';
        mensaje.textContent = 'Pedido confirmado';
        
        try {
            const response = await fetch('procesar_pago.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                await fetch('clear_cart.php', { method: 'POST' });
                mensajeConfirmacion.textContent = 'Pedido confirmado. Redirigiendo...';
                overlay.style.display = 'flex'; // Mostrar el overlay
                setTimeout(() => {
                    window.location.href = 'gracias.php';
                }, 5000); // Redirigir después de 2 segundos
            } else {
                mensajeConfirmacion.textContent = 'Error al confirmar el pedido.';
                overlay.style.display = 'flex'; // Mostrar el overlay
            }
        } catch (error) {
            console.error('Error al procesar el pedido:', error);
            mensajeConfirmacion.textContent = 'Hubo un problema al procesar el pedido.';
            overlay.style.display = 'flex'; // Mostrar el overlay
        }
    });

    // Cerrar el overlay
    document.getElementById('cerrarOverlay').addEventListener('click', () => {
        document.getElementById('overlay').style.display = 'none';
        window.location.href = 'gracias.php'; // Recargar la página al cerrar el overlay
    });
</script>
</body>
</html>
