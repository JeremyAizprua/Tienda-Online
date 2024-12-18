<?php
session_start();
require_once '../Clases/Pedido.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Debugging: Log the input
    error_log(print_r($input, true));
    
    $conexion = (new Conexion())->obtenerConexion();
    $total = $input['total'] ?? 0;
    $telefono = $_SESSION['telefono'] ?? '';
    $correo = $_SESSION['correo'] ?? '';
    $id_usuario = $_SESSION['id_usuario'] ?? null;
    $detalles = $input['detalles'] ?? '';
    $productos = $_SESSION['carrito'] ?? []; // Ensure products are retrieved from the session

    // Validate user authentication
    if ($id_usuario === null) {
        http_response_code(400);
        echo "Error: Usuario no autenticado.";
        exit;
    }

    // Create a new Pedido instance
    $pedido = new Pedido();
    $pedido_id = $pedido->guardarPedido($id_usuario, $total, $telefono, $correo, $detalles, $productos);

    // Check if the order was saved successfully
    if ($pedido_id) {
        // Prepare email details
        $to = $correo;
        $subject = 'Detalles de su Pedido';

        // Prepare the HTML message
        $message = "
    <html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f9f9f9;
                margin: 0;
                padding: 0;
            }
            .container {
                border: 1px solid #7C3AED;
                max-width: 600px;
                margin: 20px auto;
                background: white;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
            .header {
                background-color: #7C3AED; /* New primary color */
                padding: 20px;
                color: white;
                text-align: center;
                font-size: 24px;
                font-weight: bold;
                border-radius: 10px 10px 0 0;
            }
            .content {
                margin-top: 20px;
                color: #333;
            }
            .formal {
                margin: 20px 0;
                padding: 10px;
                border: 1px solid #7C3AED; /* Borders with new primary color */
                border-radius: 5px;
                background-color: #eaf4ff; /* Light background */
            }
            .card-group {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                margin-top: 20px;
                gap: 10px;
            }
            .card {
                flex: 0 0 calc(33.33% - 10px); /* Change to 33.33% for three cards */
                box-sizing: border-box;
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: 8px;
                overflow: hidden;
                background-color: #f9f9f9;
                text-align: center;
                width: calc(33.33% - 10px);
            }
            .card img {
                width: 100%;
                height: 150px;
                object-fit: cover;
            }
            .card-header {
                background-color: #f1f1f1;
                padding: 10px;
                font-size: 16px;
                font-weight: bold;
                color: #7C3AED; /* New header color */
            }
            .card-content {
                padding: 10px;
            }
            .total {
                text-align: center;
                margin-top: 20px;
                font-size: 18px;
                font-weight: bold;
                color: #7C3AED; /* New total color */
            }
            .footer {
                text-align: center;
                margin-top: 20px;
                color: #555;
                font-size: 14px;
            }
            /* Responsive styles */
            @media (max-width: 768px) {
                .card {
                    flex: 0 1 calc(50% - 5px);
                }
            }

            @media (max-width: 480px) {
                .card {
                    flex: 0 1 100%;
                }
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>Detalles de su Pedido</div>
            <div class='content'>
                <div class='formal'>
                    <p>Estimado(a) cliente, " . htmlspecialchars($_SESSION['usuario']) . "</p>
                    <p>A continuación, le presentamos los detalles de su pedido:</p>
                    <p><strong>Total:</strong> $$total</p>
                    <p><strong>Teléfono para Yappy:</strong> 6609-2208</p>
                    <p><strong>Cuenta bancaria para transferencia:</strong> 123-456789-10 (Banco General)</p>
                </div>
                <h3>Productos:</h3>
                <div class='card-group'>";

        // Group products into cards, with a new row every three cards
        if (is_array($productos) && count($productos) > 0) {
            $counter = 0; // Counter to track the number of cards
            foreach ($productos as $producto) {
                // Ensure products have an image
                $imagen = $producto['imagen'] ?? 'ruta/a/imagen/default.jpg';
                if ($counter % 3 == 0 && $counter != 0) {
                    $message .= "</div><div class='card-group'>"; // Start a new row every three cards
                }
                $message .= "
                    <div class='card'>
                        <div class='card-header'>" . htmlspecialchars($producto['nombre']) . "</div>
                        <div class='card-content'>
                            <img src='$imagen' alt='" . htmlspecialchars($producto['nombre']) . "' />
                            <p><strong>Cantidad:</strong> " . htmlspecialchars($producto['cantidad']) . "</p>
                            <p><strong>Precio Unitario:</strong> $$producto[precio]</p>
                            <p><strong>Subtotal:</strong> $" . number_format($producto['cantidad'] * $producto['precio'], 2) . "</p>
                        </div>
                    </div>";
                $counter++;
            }
        } else {
            $message .= "<p>No se encontraron productos.</p>";
        }

        $message .= "
                    </div>
                    <div class='total'>Total a pagar: $$total</div>
                </div>
                <div class='footer'>
                    <p>Gracias por elegirnos. Para cualquier consulta, no dude en contactarnos.</p>
                </div>
            </div>
        </body>
        </html>";


        // Configure email headers
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: <salmonclub2116@gmail.com>" . "\r\n";

        // Send the email
        if (mail($to, $subject, $message, $headers)) {
            unset($_SESSION['carrito']); // Clear the cart
            http_response_code(200);
            echo "Pedido realizado con éxito.";
        } else {
            http_response_code(500);
            echo "Error al enviar el correo.";
        }
    } else {
        http_response_code(500);
        echo "Error al realizar el pedido.";
    }

    $conexion->close();
}
?>
