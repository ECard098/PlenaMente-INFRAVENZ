<?php
// Ubicación: app/Config/Database.example.php

namespace App\Config;

use PDO;
use PDOException;

class Database {
    // Cambiar estas credenciales en el archivo Database.php local
    private $host = "localhost";
    private $db_name = "plenamente_bd";
    private $username = "tu_usuario_aqui"; 
    private $password = "tu_password_aqui"; 
    public $conn;

    // Método para obtener la conexión a la base de datos
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Error de conexión a la Base de Datos: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>