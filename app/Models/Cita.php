<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Cita {
    private $db;

    public function __construct() {
        // Conexión estándar de tu sistema
        $this->db = (new Database())->getConnection();
    }

    // Traer las citas unidas con los nombres del paciente (con rango de fechas y paginación opcionales)
    public function obtenerTodas($limit = null, $offset = 0, $desde = null, $hasta = null) {
        $sql = "SELECT c.*, p.nombres, p.apellidos, p.nie_dui 
                FROM citas c
                INNER JOIN pacientes p ON c.id_paciente = p.id_paciente";
        list($cond, $params) = $this->filtroFechas($desde, $hasta);
        if (!empty($cond)) {
            $sql .= " WHERE " . implode(' AND ', $cond);
        }
        $sql .= " ORDER BY c.fecha_hora ASC";
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }
        $stmt = $this->db->prepare($sql);
        foreach ($params as $clave => $valor) {
            $stmt->bindValue($clave, $valor);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Total de citas (con rango de fechas opcional) para la paginación
    public function contarTodas($desde = null, $hasta = null) {
        $sql = "SELECT COUNT(*) 
                FROM citas c
                INNER JOIN pacientes p ON c.id_paciente = p.id_paciente";
        list($cond, $params) = $this->filtroFechas($desde, $hasta);
        if (!empty($cond)) {
            $sql .= " WHERE " . implode(' AND ', $cond);
        }
        $stmt = $this->db->prepare($sql);
        foreach ($params as $clave => $valor) {
            $stmt->bindValue($clave, $valor);
        }
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    // Construye las condiciones de rango de fechas (sobre fecha_hora, apto para índice)
    private function filtroFechas($desde, $hasta) {
        $cond = [];
        $params = [];
        if (!empty($desde)) {
            $cond[] = "c.fecha_hora >= :desde";
            $params[':desde'] = $desde . ' 00:00:00';
        }
        if (!empty($hasta)) {
            $cond[] = "c.fecha_hora <= :hasta";
            $params[':hasta'] = $hasta . ' 23:59:59';
        }
        return [$cond, $params];
    }

    // Insertar la cita
    public function crearCita($id_paciente, $id_usuario_psicologo, $fecha_hora, $motivo_cita) {
        $sql = "INSERT INTO citas (id_paciente, id_usuario_psicologo, fecha_hora, motivo_cita, estado_cita) 
                VALUES (:id_paciente, :id_psicologo, :fecha_hora, :motivo, 'Programada')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_paciente', $id_paciente);
        $stmt->bindValue(':id_psicologo', $id_usuario_psicologo);
        $stmt->bindValue(':fecha_hora', $fecha_hora);
        $stmt->bindValue(':motivo', $motivo_cita);
        
        return $stmt->execute();
    }

    // Citas del día actual, ordenadas cronológicamente (con paginación opcional)
    public function obtenerCitasHoy($limit = null, $offset = 0) {
        $sql = "SELECT c.*, p.nombres, p.apellidos, p.nie_dui, p.tipo_paciente, p.grado_seccion,
                       p.telefono_contacto, e.id_expediente
                FROM citas c
                INNER JOIN pacientes p ON c.id_paciente = p.id_paciente
                LEFT JOIN expedientes e ON p.id_paciente = e.id_paciente
                WHERE DATE(c.fecha_hora) = CURDATE()
                ORDER BY c.fecha_hora ASC";
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Total de citas del día actual (para la paginación)
    public function contarCitasHoy() {
        $sql = "SELECT COUNT(*) 
                FROM citas c
                INNER JOIN pacientes p ON c.id_paciente = p.id_paciente
                WHERE DATE(c.fecha_hora) = CURDATE()";
        return (int)$this->db->query($sql)->fetchColumn();
    }

    // Detalle completo de una cita con datos del paciente y expediente
    public function obtenerPorId($id) {
        $sql = "SELECT c.*, p.nombres, p.apellidos, p.nie_dui, p.tipo_paciente, p.grado_seccion,
                       p.telefono_contacto, p.correo_paciente, p.nombre_responsable, e.id_expediente,
                       u.nombre_completo AS nombre_cancela
                FROM citas c
                INNER JOIN pacientes p ON c.id_paciente = p.id_paciente
                LEFT JOIN expedientes e ON p.id_paciente = e.id_paciente
                LEFT JOIN usuarios u ON c.id_usuario_cancela = u.id_usuario
                WHERE c.id_cita = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cancelar una cita (solo si está Programada), registrando motivo, fecha y quién la canceló
    public function cancelarCita($id_cita, $motivo, $id_usuario_cancela) {
        $sql = "UPDATE citas 
                SET estado_cita = 'Cancelada', 
                    motivo_cancelacion = :motivo, 
                    fecha_cancelacion = NOW(), 
                    id_usuario_cancela = :usuario 
                WHERE id_cita = :id AND estado_cita = 'Programada'";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':motivo', $motivo);
        $stmt->bindValue(':usuario', $id_usuario_cancela);
        $stmt->bindValue(':id', $id_cita);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Verifica disponibilidad: ¿existe otra cita Programada del mismo psicólogo
    // que solape en la franja de 1 hora con el horario propuesto?
    // Devuelve la cita en conflicto (datos básicos) o false si el horario está libre.
    public function hayConflictoHorario($fecha_hora, $id_usuario_psicologo, $excluir_id_cita = null) {
        $sql = "SELECT c.id_cita, c.fecha_hora, p.nombres, p.apellidos
                FROM citas c
                INNER JOIN pacientes p ON c.id_paciente = p.id_paciente
                WHERE c.estado_cita = 'Programada'
                  AND c.id_usuario_psicologo = :id_psicologo
                  AND ABS(TIMESTAMPDIFF(MINUTE, c.fecha_hora, :nueva)) < 60";
        if ($excluir_id_cita !== null) {
            $sql .= " AND c.id_cita <> :excluir";
        }
        $sql .= " ORDER BY c.fecha_hora ASC LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_psicologo', (int)$id_usuario_psicologo, PDO::PARAM_INT);
        $stmt->bindValue(':nueva', $fecha_hora);
        if ($excluir_id_cita !== null) {
            $stmt->bindValue(':excluir', (int)$excluir_id_cita, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Reprogramar una cita (fecha y hora), solo si sigue en estado Programada
    public function actualizarFechaHora($id_cita, $fecha_hora) {
        $sql = "UPDATE citas 
                SET fecha_hora = :fecha 
                WHERE id_cita = :id AND estado_cita = 'Programada'";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':fecha', $fecha_hora);
        $stmt->bindValue(':id', (int)$id_cita, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}