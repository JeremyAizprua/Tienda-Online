<?php
require_once('../Conexion/Conexion.php');
class Pedido {
    private $conexion;

    public function __construct() {
        $this->conexion = (new Conexion())->obtenerConexion();
    }

    public function guardarPedido($id_usuario, $total, $telefono, $correo, $detalles, $productos) {
        // Consulta para guardar el pedido
        $query = "INSERT INTO pedidos (id_usuario, total, telefono, correo, detalles) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($query);

        if ($stmt) {
            $stmt->bind_param("idsss", $id_usuario, $total, $telefono, $correo, $detalles);
            if ($stmt->execute()) {
                $pedido_id = $stmt->insert_id; // Obtener el ID del pedido
                $stmt->close();

                return $pedido_id;
            } else {
                throw new Exception("Error al guardar el pedido: " . $stmt->error);
            }
        } else {
            throw new Exception("Error al preparar la consulta: " . $this->conexion->error);
        }
    }


    public function obtenerHistorialPorUsuario($id_usuario, $orden = 'ASC') {
        // Asegúrate de que el parámetro $orden solo acepte valores válidos
        $orden = strtoupper($orden);
        if ($orden !== 'ASC' && $orden !== 'DESC') {
            throw new Exception("Orden inválido. Solo se permiten 'ASC' o 'DESC'.");
        }
    
        $pedidos = [];
        $query = "SELECT * FROM pedidos WHERE id_usuario = ? ORDER BY fecha_pedido $orden"; // Orden dinámico
    
        if ($stmt = $this->conexion->prepare($query)) {
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $resultado = $stmt->get_result();
    
            while ($fila = $resultado->fetch_assoc()) {
                $pedidos[] = $fila;
            }
    
            $stmt->close();
        } else {
            throw new Exception("Error al preparar la consulta: " . $this->conexion->error);
        }
    
        return $pedidos;
    }
    
    

    public function __destruct() {
        $this->conexion->close();
    }
}
