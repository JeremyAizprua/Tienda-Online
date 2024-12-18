<?php
class Categoria {
    private $conexion;

    public function __construct($db) {
        $this->conexion = $db;
    }

    // Obtener todas las categorías
    public function obtenerCategorias() {
        $categorias = [];
        $query = "SELECT * FROM categorias";

        if ($stmt = $this->conexion->prepare($query)) {
            $stmt->execute();
            $resultado = $stmt->get_result();

            while ($fila = $resultado->fetch_assoc()) {
                $categorias[] = $fila;
            }

            $stmt->close();
        } else {
            echo "Error al preparar la consulta: " . $this->conexion->error;
        }

        return $categorias;
    }

    // Actualizar una categoría
    public function actualizarCategoria($id, $nombre, $descripcion) {
        $query = "UPDATE categorias SET nombre_categoria = ?, descripcion = ? WHERE id_categoria = ?";

        if ($stmt = $this->conexion->prepare($query)) {
            $stmt->bind_param("ssi", $nombre, $descripcion, $id);

            if ($stmt->execute()) {
                return true;
            } else {
                echo "Error al actualizar la categoría: " . $this->conexion->error;
            }

            $stmt->close();
        } else {
            echo "Error al preparar la consulta: " . $this->conexion->error;
        }

        return false;
    }

    // Eliminar una categoría
    public function eliminarCategoria($id) {
        $query = "DELETE FROM categorias WHERE id_categoria = ?";

        if ($stmt = $this->conexion->prepare($query)) {
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                return true;
            } else {
                echo "Error al eliminar la categoría: " . $this->conexion->error;
            }

            $stmt->close();
        } else {
            echo "Error al preparar la consulta: " . $this->conexion->error;
        }

        return false;
    } 
    
    public function agregarCategoria($nombre, $descripcion) {
        $query = "INSERT INTO categorias (nombre_categoria, descripcion) VALUES (?, ?)";
        if ($stmt = $this->conexion->prepare($query)) {
            $stmt->bind_param("ss", $nombre, $descripcion);
            return $stmt->execute();
        }
        return false;
    }
}
?>
