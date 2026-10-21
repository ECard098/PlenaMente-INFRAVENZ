<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class Paciente
{
    private $db;

    public function __construct()
    {
        // Conectamos a la base de datos 
        $this->db = (new Database())->getConnection();
    }

    // Obtener todos los pacientes detectando si ya tienen expediente 
    public function obtenerTodos($soloActivos = true)
    {
        $sql = "SELECT p.*, e.id_expediente 
                FROM pacientes p 
                LEFT JOIN expedientes e ON p.id_paciente = e.id_paciente";
        if ($soloActivos) {
            $sql .= " WHERE p.estado_paciente = 'Activo'";
        }
        $sql .= " ORDER BY p.id_paciente DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Crear un nuevo paciente/estudiante 
    public function crearPaciente(
        $nie_dui,
        $nombres,
        $apellidos,
        $fecha_nacimiento,
        $genero,
        $tipo_paciente,
        $grado_seccion,
        $telefono_contacto,
        $correo_paciente,
        $nombre_responsable
    ) {

        $sql = "INSERT INTO pacientes (nie_dui, nombres, apellidos, 
fecha_nacimiento, genero, tipo_paciente, grado_seccion, telefono_contacto, 
correo_paciente, nombre_responsable) 
                VALUES (:nie_dui, :nombres, :apellidos, :fecha_nacimiento, 
:genero, :tipo_paciente, :grado_seccion, :telefono_contacto, :correo_paciente, 
:nombre_responsable)";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':nie_dui', $nie_dui);
        $stmt->bindValue(':nombres', $nombres);
        $stmt->bindValue(':apellidos', $apellidos);
        $stmt->bindValue(':fecha_nacimiento', $fecha_nacimiento);
        $stmt->bindValue(':genero', $genero);
        $stmt->bindValue(':tipo_paciente', $tipo_paciente);

        // Campos opcionales (pueden ir nulos) 
        $stmt->bindValue(':grado_seccion', $grado_seccion);
        $stmt->bindValue(':telefono_contacto', $telefono_contacto);
        $stmt->bindValue(':correo_paciente', $correo_paciente);
        $stmt->bindValue(':nombre_responsable', $nombre_responsable);

        return $stmt->execute();
    }

    // Buscar pacientes detectando si ya tienen expediente 
    public function buscarPacientes($termino, $soloActivos = true)
    {
        $sql = "SELECT p.*, e.id_expediente 
                FROM pacientes p 
                LEFT JOIN expedientes e ON p.id_paciente = e.id_paciente 
                WHERE (p.nie_dui LIKE :termino 
                OR p.nombres LIKE :termino 
                OR p.apellidos LIKE :termino)";
        if ($soloActivos) {
            $sql .= " AND p.estado_paciente = 'Activo'";
        }
        $sql .= " ORDER BY p.id_paciente DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':termino', '%' . $termino . '%');
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM pacientes WHERE id_paciente = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // Detalle de registros dependientes del paciente (para validar la eliminación/desactivación) 
    public function contarDependencias($id_paciente)
    {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM expedientes e WHERE e.id_paciente = 
:id1) AS expedientes, 
                    (SELECT COUNT(*) FROM citas c WHERE c.id_paciente = :id2) 
AS citas, 
                    (SELECT COUNT(*) FROM sesiones_clinicas sc 
                        INNER JOIN expedientes e ON sc.id_expediente = 
e.id_expediente 
                        WHERE e.id_paciente = :id3) AS sesiones, 
                    (SELECT COUNT(*) FROM reportes r 
                        INNER JOIN expedientes e ON r.id_expediente = 
e.id_expediente 
                        WHERE e.id_paciente = :id4) AS reportes, 
                    (SELECT COUNT(*) FROM asistencias a 
                        INNER JOIN expedientes e ON a.id_expediente = 
e.id_expediente 
                        WHERE e.id_paciente = :id5) AS asistencias";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id1', $id_paciente, PDO::PARAM_INT);
        $stmt->bindValue(':id2', $id_paciente, PDO::PARAM_INT);
        $stmt->bindValue(':id3', $id_paciente, PDO::PARAM_INT);
        $stmt->bindValue(':id4', $id_paciente, PDO::PARAM_INT);
        $stmt->bindValue(':id5', $id_paciente, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ¿El paciente posee información crítica (cualquier registro dependiente)? 
    public function tieneInformacionCritica($id_paciente)
    {
        $dep = $this->contarDependencias($id_paciente);
        return array_sum(array_map('intval', $dep)) > 0;
    }

    // Desactivar (eliminar lógicamente) un paciente, conservando su historial 
    public function desactivar($id_paciente)
    {
        $sql = "UPDATE pacientes SET estado_paciente = 'Inactivo' WHERE 
id_paciente = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id_paciente, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Reactivar un paciente previamente desactivado 
    public function reactivar($id_paciente)
    {
        $sql = "UPDATE pacientes SET estado_paciente = 'Activo' WHERE 
id_paciente = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id_paciente, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
