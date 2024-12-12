<?php
class Usuario {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function registrarUsuario($nombre, $correo, $contrasena, $numero) {
        $query = "INSERT INTO usuarios (nombre, correo, contrasena, numero) VALUES (?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($query);

        if ($stmt === false) {
            throw new Exception("Error en la preparación de la consulta: " . $this->conexion->error);
        }

        $stmt->bind_param("ssss", $nombre, $correo, $contrasena, $numero);

        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }

        $id_usuario = $stmt->insert_id;
        $stmt->close();

        return $id_usuario;
    }

    public function autenticarUsuario($correo, $contrasena) {
        $query = "SELECT id, contrasena FROM usuarios WHERE correo = ?";
        $stmt = $this->conexion->prepare($query);

        if ($stmt === false) {
            throw new Exception("Error en la preparación de la consulta: " . $this->conexion->error);
        }

        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->bind_result($id, $hashedPassword);

        if ($stmt->fetch() && password_verify($contrasena, $hashedPassword)) {
            $stmt->close();
            return $id; // Usuario autenticado con éxito
        }

        $stmt->close();
        return false; // Credenciales inválidas
    }
}
?>
