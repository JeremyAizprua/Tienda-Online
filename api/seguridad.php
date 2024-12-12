<?php
// Definición de la clase Seguridad
final class Seguridad {
    // Método estático para encriptar una contraseña
    public static function encriptarContrasena($contrasena) {
        // Utiliza password_hash para encriptar la contraseña con el algoritmo BCRYPT
        // El método PASSWORD_BCRYPT genera un hash seguro de la contraseña
        return password_hash($contrasena, PASSWORD_BCRYPT);
    }

    // Método estático para verificar si una contraseña coincide con un hash
    public static function verificarContrasena($contrasena, $hash) {
        // Verifica si la contraseña proporcionada coincide con el hash almacenado
        // Utiliza password_verify para hacer esta comparación de forma segura
        return password_verify($contrasena, $hash);
    }

    // Autentica al usuario mediante correo y contraseña revisando en la base de datos
    public static function autenticarUsuario($usuario, $contrasena, $conn) {
        // Validar usuario
        if (!preg_match('/^\w{1,50}$/', $usuario)) {
            die("El nombre de usuario solo puede contener letras, números y guiones bajos y debe tener entre 1 y 50 caracteres.");
        }
        // Prepara la sentencia SQL con consulta parametrizada
        $stmt = $conn->prepare("SELECT id, contrasena FROM usuarios WHERE apodo = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $stmt->store_result();

        // Verifica si se encontró el usuario
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashedPassword);
            $stmt->fetch();

            // Verifica la contraseña
            if (self::verificarContrasena($contrasena, $hashedPassword)) {
                return $id; // Devuelve el ID del usuario si la autenticación es exitosa
            }
        }
        return false; // Devuelve false si falla la autenticación
    }
}

?>
