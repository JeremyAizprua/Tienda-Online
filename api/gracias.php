<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gracias por tu compra</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Arial', sans-serif;
            background-image: url('https://sumamosdesign.com/wp-content/uploads/2020/06/tv1.jpg'); /* URL de la imagen de fondo */
            background-size: cover; /* Cubrir toda la pantalla */
            background-position: center; /* Centrar la imagen */
            background-attachment: fixed; /* Mantener la imagen fija durante el scroll */
        }

        .container {
            text-align: center;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 500px;
        }

        h1 {
            color: #e582e7;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        p {
            color: #555;
            font-size: 1.2rem;
        }

        .btn {
            margin-top: 20px;
            background-color: #e582e7 !important;
        }

        .btn:hover {
            background-color: #e3a3e5 !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>¡Gracias por tu compra!</h1>
        <p>Tu pedido ha sido completado exitosamente.</p>
        <p>Recibirás un correo con los detalles de tu compra en breve.</p>
        <a href="index.php" class="btn waves-effect">Volver a la tienda</a>
    </div>
</body>
</html>
