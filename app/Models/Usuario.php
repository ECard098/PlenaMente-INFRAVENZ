<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Usuario {
    private $db;

    public function __construct() {
        // Conectamos a la base de datos al instanciar el modelo
        $this->db = (new Database())->getConnection();
    }

    // Función para buscar un usuario por su correo electrónico
    public function obtenerPorCorreo($correo) {
        // Asumimos que tu tabla se llama 'usuarios'
        $query = "SELECT * FROM usuarios WHERE correo = :correo LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        
        // Retorna un arreglo asociativo con los datos del usuario o false si no existe
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener todos los usuarios 
    public function obtenerTodos() {
        $sql = "SELECT * FROM usuarios ORDER BY id_usuario DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener un usuario específico por su ID
    public function obtenerPorId($id) {
        $sql = "SELECT * FROM usuarios WHERE id_usuario = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // función para Registrar Usuario (PBI-05)
    public function crearUsuario($id_rol, $nombre_completo, $correo, $password_hash) {
        $sql = "INSERT INTO usuarios (id_rol, nombre_completo, correo, password_hash, estado) 
                VALUES (:id_rol, :nombre_completo, :correo, :password_hash, 'Activo')";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':id_rol', $id_rol);
        $stmt->bindValue(':nombre_completo', $nombre_completo);
        $stmt->bindValue(':correo', $correo);
        $stmt->bindValue(':password_hash', $password_hash);
        
        return $stmt->execute();
    }

    // Actualizar los datos del usuario
    public function actualizarUsuario($id, $id_rol, $nombre_completo, $correo, $estado, $password_hash = null) {
        // Si nos enviaron una contraseña nueva, la actualizamos. Si no, la dejamos igual.
        if ($password_hash) {
            $sql = "UPDATE usuarios SET id_rol = :id_rol, nombre_completo = :nombre, correo = :correo, estado = :estado, password_hash = :pass WHERE id_usuario = :id";
        } else {
            $sql = "UPDATE usuarios SET id_rol = :id_rol, nombre_completo = :nombre, correo = :correo, estado = :estado WHERE id_usuario = :id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_rol', $id_rol);
        $stmt->bindValue(':nombre', $nombre_completo);
        $stmt->bindValue(':correo', $correo);
        $stmt->bindValue(':estado', $estado);
        $stmt->bindValue(':id', $id);
        
        if ($password_hash) {
            $stmt->bindValue(':pass', $password_hash);
        }
        
        return $stmt->execute();
    }

    // Eliminación lógica: Solo cambia el estado a Inactivo
    public function eliminarLogico($id) {
        $sql = "UPDATE usuarios SET estado = 'Inactivo' WHERE id_usuario = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}
?>