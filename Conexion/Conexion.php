<?php
// config/Database.php

class Conexion {
    private $host = "bg5eqxanx9okbebe9tsd-mysql.services.clever-cloud.com";
    private $db_name = "bg5eqxanx9okbebe9tsd";
    private $username = "uywkvr040u11clpe";
    private $password = "Ptb0ZSA4ajoVj93u4uPO";
    private $port = "3306"; // Se agrega el puerto
    public $conn;

    public function obtenerConexion() {
        $this->conn = null;
        try {
            // Creación de conexión MySQLi
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name, $this->port);

            // Verificación de errores
            if ($this->conn->connect_error) {
                throw new Exception("Error de conexión: " . $this->conn->connect_error);
            }
        } catch(Exception $exception) {
            echo $exception->getMessage();
        }
        return $this->conn;
    }
}
