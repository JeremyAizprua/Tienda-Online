<?php
class Productos {
    private $conexion;

    public function __construct($db) {
        $this->conexion = $db;
    }

    public function obtenerTodosLosProductos() {
        $productos = [];
        $query = "SELECT * FROM productos";

        if ($stmt = $this->conexion->prepare($query)) {
            $stmt->execute();
            $resultado = $stmt->get_result();

            while ($fila = $resultado->fetch_assoc()) {
                $productos[] = $fila;
            }

            $stmt->close();
        } else {
            echo "Error al preparar la consulta: " . $this->conexion->error;
        }

        return $productos;
    }

    public function obtenerProductosPorCategoria($idCategoria) {
        $productos = [];
        $query = "SELECT * FROM productos WHERE id_categoria = ?";
    
        if ($stmt = $this->conexion->prepare($query)) {
            $stmt->bind_param("i", $idCategoria);
            $stmt->execute();
            $resultado = $stmt->get_result();
    
            while ($fila = $resultado->fetch_assoc()) {
                $productos[] = $fila;
            }
    
            $stmt->close();
        } else {
            echo "Error al preparar la consulta: " . $this->conexion->error;
        }
    
        return $productos;
    }

    public function obtenerNombreCategoria($idCategoria) {
        $query = "SELECT nombre_categoria FROM categorias WHERE id_categoria = ?";

        if ($stmt = $this->conexion->prepare($query)) {
            $stmt->bind_param("i", $idCategoria); // "i" indica que es un entero
            $stmt->execute();
            $stmt->bind_result($nombreCategoria);
            $stmt->fetch();
            $stmt->close();
            return $nombreCategoria; // Devuelve el nombre de la categoría
        } else {
            die("Error al preparar la consulta: " . $this->conexion->error);
        }
    }
    
    

    public function obtenerProductoPorId($id) {
        $query = "SELECT * FROM productos WHERE id_producto = ?";
        
        if ($stmt = $this->conexion->prepare($query)) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $resultado = $stmt->get_result();
            return $resultado->fetch_assoc(); // Retorna un solo producto
        } else {
            throw new Exception("Error al preparar la consulta: " . $this->conexion->error);
        }
    }

    
    public function agregarProducto($nombre_producto, $descripcion, $precio, $stock, $id_categoria, $imagen_producto) {
        $query = "INSERT INTO productos (nombre_producto, descripcion, precio, stock, id_categoria, imagen_producto) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $this->conexion->prepare($query)) {
            // Vincular los parámetros
            $stmt->bind_param("ssdiss", $nombre_producto, $descripcion, $precio, $stock, $id_categoria, $imagen_producto);
            
            // Ejecutar la consulta
            return $stmt->execute();
        } else {
            echo "Error al preparar la consulta: " . $this->conexion->error;
            return false;
        }
    }
    
    
    public function actualizarProducto($id_producto, $nombre_producto, $descripcion, $precio, $stock, $id_categoria, $imagen_producto) {
        $query = "UPDATE productos SET nombre_producto = ?, descripcion = ?, precio = ?, stock = ?, id_categoria = ?, imagen_producto = ? WHERE id_producto = ?";
        $stmt = $this->conexion->prepare($query);
        return $stmt->execute([$nombre_producto, $descripcion, $precio, $stock, $id_categoria, $imagen_producto, $id_producto]);
    }

    // Method to delete a product
    public function eliminarProducto($id_producto) {
        $query = "DELETE FROM productos WHERE id_producto = ?";
        $stmt = $this->conexion->prepare($query);
        return $stmt->execute([$id_producto]);
    }
    public function obtenerProductosRelacionados($id_categoria, $id_producto_actual, $limite = 4) {
        $query = "SELECT * FROM productos WHERE id_categoria = ? AND id_producto != ? ORDER BY RAND() LIMIT ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("iii", $id_categoria, $id_producto_actual, $limite);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
