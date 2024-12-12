<?php
session_start();
require_once('../Conexion/Conexion.php');
require_once 'funciones.php';
require_once 'Seguridad.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = sanitizarEntrada($_POST['correo']);
    $contrasena = $_POST['contrasena'];

    try {
        // Obtener la conexión
        $conexion = (new Conexion())->obtenerConexion();

        // Consulta SQL con parámetros de `mysqli`
        $query = "SELECT * FROM usuarios WHERE correo = ?";
        $stmt = $conexion->prepare($query);

        if ($stmt === false) {
            throw new Exception("Error en la preparación de la consulta: " . $conexion->error);
        }

        // Vincular parámetros
        $stmt->bind_param("s", $correo);
        $stmt->execute();

        // Obtener resultados
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();

        // Verificar si el usuario existe y si la contraseña es correcta
        if ($usuario && Seguridad::verificarContrasena($contrasena, $usuario['contrasena'])) {
            // Guardar el ID del usuario y otros datos en la sesión
            $_SESSION['id_usuario'] = $usuario['id']; // Almacena el ID del usuario
            $_SESSION['usuario'] = $usuario['nombre'];
            $_SESSION['correo'] = $correo; // Guardar el correo del usuario
            $_SESSION['isAdmin'] = (bool)$usuario['es_admin'];
            $_SESSION['telefono'] = $usuario['numero'];

            header('Location: perfil.php'); // Redirigir a perfil
            exit;
        } else {
            $error = "Correo o contraseña incorrectos.";
        }

        $stmt->close();
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body {
            background-image: url('https://wallpapers.com/images/hd/anime-collage-1920-x-1080-wallpaper-fwx8xyvh2rd4ju8n.jpg');
            background-color: #000;
            font-family: 'Montserrat', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 10px;
            color: #fff;
        }
        .login-container {
            background-color: #1a1a1a;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.6);
            width: 100%;
            max-width: 400px;
        }
        .login-container h3 {
            font-size: 24px;
            color: #7C3AED;
            text-align: center;
            margin-bottom: 20px;
        }
        .login-container .error {
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
            text-align: center;
        }
        .input-field label {
            color: #fff !important;
        }
        .input-field input {
            border-bottom: 1px solid #7C3AED !important;
            color: #fff !important;
        }
        .input-field input:focus {
            border-bottom: 2px solid #7C3AED !important;
            box-shadow: 0 1px 0 0 #7C3AED !important;
        }
        .input-field .prefix {
            color: #7C3AED !important;
        }
        .login-container button {
            background-color: #7C3AED;
            width: 100%;
            font-weight: bold;
        }
        .login-container button:hover {
            background-color: #5a27c6;
        }
        .login-container p {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        .login-container p a {
            color: #7C3AED;
            font-weight: bold;
        }
        @media screen and (max-width: 600px) {
            .login-container {
                padding: 20px;
            }
            .login-container h3 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h3 class="center-align">Iniciar Sesión</h3>
        <form action="login.php" method="POST" class="col s12">
            <div class="input-field">
                <i class="material-icons prefix">email</i>
                <input type="email" name="correo" id="correo" required>
                <label for="correo">Correo electrónico</label>
            </div>
            <div class="input-field">
                <i class="material-icons prefix">lock</i>
                <input type="password" name="contrasena" id="contrasena" required>
                <label for="contrasena">Contraseña</label>
            </div>
            <button type="submit" class="btn waves-effect waves-light">Iniciar Sesión</button>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
        </form>
        <p>¿No tienes una cuenta? <a href="Registro.php">Registrarse</a></p>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
