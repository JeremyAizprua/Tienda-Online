<?php
session_start();
require_once('../Conexion/Conexion.php');
require_once '../Clases/Usuario.php';
require_once 'funciones.php';
require_once 'Seguridad.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = sanitizarEntrada($_POST['nombre']);
    $correo = sanitizarEntrada($_POST['correo']);
    $contrasena = $_POST['contrasena'];
    $contrasena_confirmacion = $_POST['contrasena_confirmacion'];
    $numero = sanitizarEntrada($_POST['numero']);
    
    if ($contrasena !== $contrasena_confirmacion) {
        $error = "Las contraseñas no coinciden.";
    } else {
        $contrasena = Seguridad::encriptarContrasena($contrasena);

        try {
            $conexion = (new Conexion())->obtenerConexion();
            $usuario = new Usuario($conexion);

            $id_usuario = $usuario->registrarUsuario($nombre, $correo, $contrasena, $numero);

            if ($id_usuario) {
                $_SESSION['usuario'] = $nombre;
                $_SESSION['id_usuario'] = $id_usuario;
                header('Location: perfil.php');
                exit;
            } else {
                $error = "Error al registrar el usuario.";
            }
        } catch (Exception $e) {
            $error = "Ha ocurrido un error: el correo que intenta utilizar ya ha sido utilizado" ;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body {
            background-image: url('https://wallpapers.com/images/hd/anime-collage-1920-x-1080-wallpaper-fwx8xyvh2rd4ju8n.jpg');
            font-family: 'Montserrat', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            background-color: #000000;
            color: #ffffff;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .container {
            background-color: rgba(26, 26, 26, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.6);
            width: 100%;
            max-width: 400px;
        }
        h3 {
            font-size: 24px;
            color: #7C3AED;
            text-align: center;
            margin-bottom: 20px;
        }
        .input-field label {
            color: #ffffff !important;
        }
        .input-field input {
            border-bottom: 1px solid #7C3AED !important;
            color: #ffffff !important;
        }
        .input-field input:focus {
            border-bottom: 2px solid #7C3AED !important;
            box-shadow: 0 1px 0 0 #7C3AED !important;
        }
        .input-field .prefix {
            color: #7C3AED !important;
        }
        .container button {
            background-color: #7C3AED;
            width: 100%;
            font-weight: bold;
        }
        .container button:hover {
            background-color: #5a27c6;
        }
        p {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        p a {
            color: #7C3AED;
            font-weight: bold;
        }
        .error-message {
            color: #ff3333;
            text-align: center;
            margin-bottom: 15px;
            background-color: rgba(255, 51, 51, 0.1);
            border: 1px solid #ff3333;
            border-radius: 5px;
            padding: 10px;
        }
        @media screen and (max-width: 600px) {
            .container {
                padding: 20px;
            }
            h3 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h3 class="center-align">Registro</h3>
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="registro.php" method="POST" class="row">
            <div class="input-field col s12">
                <i class="material-icons prefix">person</i>
                <input type="text" name="nombre" id="nombre" required>
                <label for="nombre">Nombre completo</label>
            </div>
            <div class="input-field col s12">
                <i class="material-icons prefix">email</i>
                <input type="email" name="correo" id="correo" required>
                <label for="correo">Correo electrónico</label>
            </div>
            <div class="input-field col s12">
                <i class="material-icons prefix">lock</i>
                <input type="password" name="contrasena" id="contrasena" required>
                <label for="contrasena">Contraseña</label>
            </div>
            <div class="input-field col s12">
                <i class="material-icons prefix">lock</i>
                <input type="password" name="contrasena_confirmacion" id="contrasena_confirmacion" required>
                <label for="contrasena_confirmacion">Confirmar Contraseña</label>
            </div>
            <div class="input-field col s12">
                <i class="material-icons prefix">phone</i>
                <input type="tel" name="numero" id="numero" required>
                <label for="numero">Número de contacto</label>
            </div>
            <div class="col s12">
                <button type="submit" class="btn waves-effect waves-light">Registrarse</button>
            </div>
        </form>
        <p>¿Ya tienes una cuenta? <a href="login.php">Iniciar sesión</a></p>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>

