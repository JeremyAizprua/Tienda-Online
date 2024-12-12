<?php
session_start();
require_once('../Conexion/Conexion.php');

// Conexión a la base de datos
$database = new Conexion();
$db = $database->obtenerConexion();

$productosEnCarrito = $_SESSION['carrito'] ?? [];
$total = 0;

// Procesar solicitudes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id']) && isset($_POST['cantidad'])) {
        $id = intval($_POST['id']);
        $cantidad = max(1, intval($_POST['cantidad'])); // Evitar cantidades menores a 1
        if (isset($_SESSION['carrito'][$id])) {
            $_SESSION['carrito'][$id]['cantidad'] = $cantidad;
            $subtotal = $_SESSION['carrito'][$id]['precio'] * $cantidad;
            echo json_encode(['subtotal' => number_format($subtotal, 2)]);
        }
        exit();
    }

    if (isset($_POST['delete']) && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        unset($_SESSION['carrito'][$id]); // Eliminar producto del carrito
        echo json_encode(['success' => true]);
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
    body {
        background-color: #eed2ef;
        color: #000;
    }
    .comtainer{
        justify-content: center;
    }

    .btn {
        background-color: #e582e7 !important;
        font-size: x-large;
        margin-top: 20px;
        padding: 5px 11px;
        cursor: pointer;
        text-decoration: none;
        border: none;
        border-radius: 3px;
        box-shadow: 2px 4px 6px rgba(0, 0, 0, 0.3);
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
        color: white;
    }

    .btn:hover {
        background-color: #e582e7 !important;
    }

    .volver-icono {
        display: inline-block;
        margin: 10px;
        color: #e3a3e5;
        font-size: 1.8rem;
        text-decoration: none;
        transition: color 0.3s;
    }

    .volver-icono:hover {
        color: #e582e7;
    }

        .card-horizontal {
        width: 100%;
        max-width: 1000px;
        display: flex;
        flex-direction: row;
        align-items: center;
        margin-bottom: 20px;
        border: 2px solid #e3a3e5;
        border-radius: 10px;
        background-color: #fff;
        padding: 15px;
    }

    .card-horizontal img {
        width: 150px; /* Ancho de la imagen */
        height: 150px; /* Alto de la imagen */
        object-fit: cover;
        border-radius: 10px;
    }

    .card-horizontal .card-content {
        display: flex;
        flex-direction: column;
        flex-wrap: nowrap;
        align-items: flex-start;
        justify-content: center;
        padding: 15px;
    }

    .card-horizontal .card-title {
        color: #e3a3e5;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .card-horizontal p {
        color: #000;
        margin-bottom: 10px;
    }

    h5 {
        color: black;
        font-size: 1.8rem;
    }

    .total {
        font-weight: bold;
        color: #000;
        margin-top: 20px;
    }

    .input-field{
        display: flex;
        flex-direction: column-reverse;
    }

    .input-field input{
        width: 45px;
        height: 34px;
        font-size: large;
        border-style: none;
        border-bottom: solid;
        border-color: gray;
    }

    .card-content .btn{
        background-color: #d32f2f  !important;
        cursor: pointer;
        border: none;
        border-radius: 3px;
        box-shadow: 2px 4px 6px rgba(0, 0, 0, 0.3);
        transition: background-color 0.3s ease, box-shadow 0.3s ease;

    }
    .card-content .btn:hover {
        background-color: #f30000 !important; 
        box-shadow: 4px 8px 10px rgba(0, 0, 0, 0.4);
    }
    .pago{
        margin: 0% 13% 13% 19%;
    }
    .card-description {
        align-items: center;
        justify-content: center;
        margin-top: 10px; /* Space between content and description */
        color: #555; /* Dark gray color for text */
        font-size: 1rem; /* Standard font size */
    }
/* Modify the responsive styles */
@media (max-width: 768px) {
    .card-horizontal {
        flex-direction: row;
        align-items: flex-start;
        text-align: left;
    }

    .card-horizontal img {
        width: 120px;
        height: 120px;
        margin-right: 15px;
        margin-bottom: 0;
    }

    .card-horizontal .card-content {
        flex: 1;
        align-items: flex-start;
        padding: 0;
    }

    .card-description {
        display: none;
    }

    .card-description {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .card-horizontal {
        flex-direction: row;
        padding: 10px;
    }

    .card-horizontal img {
        width: 100px;
        height: 100px;
    }

    .card-title {
        font-size: 0.9rem;
    }

    .card-horizontal p {
        font-size: 0.8rem;
    }

    .input-field input {
        width: 40px;
        height: 25px;
        font-size: 0.8rem;
    }

    .btn {
        font-size: 0.7rem;
        padding: 5px 10px;
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
    <h5>Productos en el carrito:</h5>
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
                <form class="update-form" data-id="<?php echo $id; ?>">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <div class="input-field">
                        <input type="number" name="cantidad" value="<?php echo $producto['cantidad']; ?>" min="1">
                        <label for="cantidad">Cantidad</label>
                    </div>
                    <button type="submit" name="delete" class="btn">Eliminar</button>
                </form><br>
                <!-- Displaying the subtotal for this product -->
                <p class="subtotal">Subtotal: $<?php echo htmlspecialchars($producto['precio'] * $producto['cantidad'], 2); ?></p>
            </div>
            <div class="card-description">
                <p>Descripción: <?php echo htmlspecialchars($producto['descripcion'] ?? 'Descripción no disponible'); ?></p>
            </div>
        </div>
    </div>
    <?php 
    $total += $producto['precio'] * $producto['cantidad']; 
    endforeach; ?>

        <?php endif; ?>
    </div>
    
</div>
<div class="pago">
    <?php if (!empty($productosEnCarrito)): ?>
        <h5 class="total">Total: $<span id="total-amount"><?php echo number_format($total, 2, '.', ''); ?></span></h5>
        <?php if (isset($_SESSION['usuario'])): // Verificar si el usuario está logueado ?>
            <a href="checkout_resumen.php" class="btn">Finalizar compra</a>
        <?php else: ?>
            <a href="login.php" class="btn">Iniciar sesión para finalizar compra</a>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    $('.update-form input[name="cantidad"]').on('change', function() {
        var $form = $(this).closest('.update-form');
        var id = $form.data('id');
        var cantidad = $(this).val();

        $.ajax({
            type: 'POST',
            url: '', // URL de la misma página
            data: { id: id, cantidad: cantidad },
            success: function(response) {
                var data = JSON.parse(response);
                $form.siblings('.subtotal').text('Subtotal: $' + data.subtotal);

                // Actualizar el total
                updateTotal();
            }
        });
    });

    $('.update-form button[name="delete"]').on('click', function(e) {
        e.preventDefault();
        var $form = $(this).closest('.update-form');
        var id = $form.data('id');

        $.ajax({
            type: 'POST',
            url: '', // URL de la misma página
            data: { delete: true, id: id },
            success: function() {
                $form.closest('.col').fadeOut();
                location.reload(); // Recargar para actualizar el total
            }
        });
    });

    function updateTotal() {
        var total = 0;
        $('.subtotal').each(function() {
            var subtotal = parseFloat($(this).text().replace('Subtotal: $', '').replace(',', ''));
            if (!isNaN(subtotal)) {
                total += subtotal;
            }
        });
        $('#total-amount').text(total.toFixed(2));
    }
});
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
