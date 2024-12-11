<?php
class Categoria {
    private $conexion;

    public function __construct($db) {
        $this->conexion = $db;
    }

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
}
