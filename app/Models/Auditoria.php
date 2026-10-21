<?php 
namespace App\Models; 
 
use App\Config\Database; 
use PDO; 
 
class Auditoria { 
    private $db; 
 
    public function __construct() { 
        $this->db = (new Database())->getConnection(); 
    } 
 
    // Registrar una acción en la auditoría del sistema 
    public function registrar($id_usuario, $accion, $entidad, $id_entidad, 
$descripcion) { 
        $sql = "INSERT INTO auditoria_acciones (id_usuario, accion, entidad, 
id_entidad, descripcion, fecha) 
                VALUES (:id_usuario, :accion, :entidad, :id_entidad, 
:descripcion, NOW())"; 
        $stmt = $this->db->prepare($sql); 
        $stmt->bindValue(':id_usuario', $id_usuario ?: null, $id_usuario ? 
PDO::PARAM_INT : PDO::PARAM_NULL); 
        $stmt->bindValue(':accion', $accion); 
        $stmt->bindValue(':entidad', $entidad); 
        $stmt->bindValue(':id_entidad', $id_entidad ?: null, $id_entidad ? 
PDO::PARAM_INT : PDO::PARAM_NULL); 
        $stmt->bindValue(':descripcion', $descripcion); 
        return $stmt->execute(); 
    } 
 
    // Últimas acciones registradas (para consulta) 
    public function obtenerRecientes($limite = 50) { 
        $limite = (int)$limite; 
        $sql = "SELECT a.*, u.nombre_completo 
                FROM auditoria_acciones a 
                LEFT JOIN usuarios u ON a.id_usuario = u.id_usuario 
                ORDER BY a.fecha DESC, a.id_auditoria DESC 
                LIMIT {$limite}"; 
        $stmt = $this->db->prepare($sql); 
        $stmt->execute(); 
        return $stmt->fetchAll(PDO::FETCH_ASSOC); 
    } 
} 
 