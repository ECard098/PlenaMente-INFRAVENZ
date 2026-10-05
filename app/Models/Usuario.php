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

    // —— Recuperación de contraseña ——

    // Obtiene el código activo (no usado y sin expirar) más reciente del usuario
    public function obtenerCodigoActivo($idUsuario, $ahora) {
        $sql = "SELECT * FROM recuperaciones_password 
                WHERE id_usuario = :id_usuario AND usado = 0 AND expira_en > :ahora 
                ORDER BY id_recuperacion DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_usuario', $idUsuario);
        $stmt->bindValue(':ahora', $ahora);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // Guarda un código de recuperación (solo su hash bcrypt)
    public function guardarCodigoRecuperacion($idUsuario, $codigoHash, $expiraEn) {
        $sql = "INSERT INTO recuperaciones_password (id_usuario, codigo_hash, expira_en, usado, creado_en) 
                VALUES (:id_usuario, :codigo_hash, :expira_en, 0, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_usuario', $idUsuario);
        $stmt->bindValue(':codigo_hash', $codigoHash);
        $stmt->bindValue(':expira_en', $expiraEn);
        return $stmt->execute();
    }

    // Cuenta códigos generados en los últimos 15 minutos (límite de reenvíos)
    public function contarRecuperacionesRecientes($idUsuario) {
        $sql = "SELECT COUNT(*) AS total FROM recuperaciones_password 
                WHERE id_usuario = :id_usuario AND creado_en > (NOW() - INTERVAL 15 MINUTE)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_usuario', $idUsuario);
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)($res['total'] ?? 0);
    }

    public function incrementarIntentosRecuperacion($idRecuperacion) {
        $sql = "UPDATE recuperaciones_password SET intentos = intentos + 1 WHERE id_recuperacion = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $idRecuperacion);
        return $stmt->execute();
    }

    public function marcarRecuperacionUsada($idRecuperacion) {
        $sql = "UPDATE recuperaciones_password SET usado = 1 WHERE id_recuperacion = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $idRecuperacion);
        return $stmt->execute();
    }

    // Invalida todos los códigos pendientes del usuario (al restablecer la contraseña)
    public function invalidarRecuperaciones($idUsuario) {
        $sql = "UPDATE recuperaciones_password SET usado = 1 WHERE id_usuario = :id_usuario AND usado = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_usuario', $idUsuario);
        return $stmt->execute();
    }

    public function actualizarPassword($idUsuario, $passwordHash) {
        $sql = "UPDATE usuarios SET password_hash = :password_hash WHERE id_usuario = :id_usuario";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':password_hash', $passwordHash);
        $stmt->bindValue(':id_usuario', $idUsuario);
        return $stmt->execute();
    }
}
?>
