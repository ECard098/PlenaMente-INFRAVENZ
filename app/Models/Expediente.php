<?php 
namespace App\Models; 
 
// Usamos la configuración de base de datos exacta de tu framework 
use App\Config\Database; 
use PDO; 
 
class Expediente { 
    private $db; 
 
    public function __construct() { 
        // Conectamos a la base de datos exactamente igual que en 
Paciente.php 
        $this->db = (new Database())->getConnection(); 
    } 
 
    // Obtener todos los expedientes haciendo un JOIN con pacientes 
para ver sus nombres 
    public function obtenerTodos() { 
        $sql = "SELECT e.*, p.nie_dui, p.nombres, p.apellidos, 
p.tipo_paciente 
                FROM expedientes e 
                INNER JOIN pacientes p ON e.id_paciente = p.id_paciente 
                ORDER BY e.id_expediente DESC"; 
        $stmt = $this->db->prepare($sql); 
        $stmt->execute(); 
        return $stmt->fetchAll(PDO::FETCH_ASSOC); 
    } 
 
    // Verificar si un paciente ya cuenta con un expediente abierto 
(Evita duplicados) 
    public function verificarSiExiste($id_paciente) { 
        $sql = "SELECT id_expediente FROM expedientes WHERE id_paciente 
= :id_paciente LIMIT 1"; 
        $stmt = $this->db->prepare($sql); 
        $stmt->bindValue(':id_paciente', $id_paciente); 
        $stmt->execute(); 
        return $stmt->fetch(PDO::FETCH_ASSOC); 
    } 
 
    // Crear un nuevo expediente médico/psicológico 
    public function crearExpediente($id_paciente, $fecha_apertura, 
$antecedentes_familiares, $antecedentes_medicos) { 
        $sql = "INSERT INTO expedientes (id_paciente, fecha_apertura, 
antecedentes_familiares, antecedentes_medicos) 
                VALUES (:id_paciente, :fecha_apertura, 
:antecedentes_familiares, :antecedentes_medicos)"; 
        
        $stmt = $this->db->prepare($sql); 
        $stmt->bindValue(':id_paciente', $id_paciente); 
        $stmt->bindValue(':fecha_apertura', $fecha_apertura); 
        $stmt->bindValue(':antecedentes_familiares', 
$antecedentes_familiares); 
        $stmt->bindValue(':antecedentes_medicos', 
$antecedentes_medicos); 
        
        return $stmt->execute(); 
    } 
 
    // Obtener un expediente específico por su ID con todos los datos 
detallados del paciente 
    public function obtenerPorId($id_expediente) { 
        $sql = "SELECT e.*, p.nie_dui, p.nombres, p.apellidos, 
p.tipo_paciente, 
                       p.grado_seccion, p.telefono_contacto, 
p.correo_paciente, 
                       p.genero, p.fecha_nacimiento, 
p.nombre_responsable 
                FROM expedientes e 
                INNER JOIN pacientes p ON e.id_paciente = p.id_paciente 
                WHERE e.id_expediente = :id_expediente LIMIT 1"; 
        $stmt = $this->db->prepare($sql); 
        $stmt->bindValue(':id_expediente', $id_expediente); 
        $stmt->execute(); 
        return $stmt->fetch(PDO::FETCH_ASSOC); 
    } 
 
    // Obtener todas las sesiones clínicas asociadas a este expediente 
    public function obtenerSesionesPorExpediente($id_expediente, $orden 
= 'DESC') { 
        $orden = strtoupper($orden) === 'ASC' ? 'ASC' : 'DESC'; 
        $sql = "SELECT * FROM sesiones_clinicas 
                WHERE id_expediente = :id_expediente 
                ORDER BY fecha_registro {$orden}, id_sesion {$orden}"; 
        $stmt = $this->db->prepare($sql); 
        $stmt->bindValue(':id_expediente', $id_expediente); 
        $stmt->execute(); 
        return $stmt->fetchAll(PDO::FETCH_ASSOC); 
    } 
 
    // Obtener el expediente anterior y siguiente para la navegación 
entre registros 
    public function obtenerVecinos($id_expediente) { 
        $sql = "SELECT 
                    (SELECT e2.id_expediente FROM expedientes e2 WHERE 
e2.id_expediente < :id ORDER BY e2.id_expediente DESC LIMIT 1) AS 
anterior, 
                    (SELECT e3.id_expediente FROM expedientes e3 WHERE 
e3.id_expediente > :id ORDER BY e3.id_expediente ASC LIMIT 1) AS 
siguiente"; 
        $stmt = $this->db->prepare($sql); 
        $stmt->bindValue(':id', (int)$id_expediente, PDO::PARAM_INT); 
        $stmt->execute(); 
        return $stmt->fetch(PDO::FETCH_ASSOC); 
    } 
 
    // Actualizar los antecedentes clínicos del expediente (sin tocar 
el resto del registro) 
    public function actualizarAntecedentes($id_expediente, 
$antecedentes_familiares, $antecedentes_medicos) { 
        $sql = "UPDATE expedientes 
                SET antecedentes_familiares = :af, antecedentes_medicos 
= :am 
                WHERE id_expediente = :id_expediente"; 
        $stmt = $this->db->prepare($sql); 
        $stmt->bindValue(':af', !empty($antecedentes_familiares) ? 
$antecedentes_familiares : null); 
        $stmt->bindValue(':am', !empty($antecedentes_medicos) ? 
$antecedentes_medicos : null); 
        $stmt->bindValue(':id_expediente', $id_expediente); 
        return $stmt->execute(); 
    } 
 
    // Actualizar el estado del caso del expediente (Activo, 
Seguimiento, Cerrado) 
    public function actualizarEstado($id_expediente, $estado) { 
        $sql = "UPDATE expedientes SET estado_caso = :estado WHERE 
id_expediente = :id_expediente"; 
        $stmt = $this->db->prepare($sql); 
        $stmt->bindValue(':estado', $estado); 
        $stmt->bindValue(':id_expediente', $id_expediente, 
PDO::PARAM_INT); 
        return $stmt->execute(); 
    } 
 
    // Registrar en el historial un cambio en los antecedentes del 
expediente 
    public function registrarCambioExpediente($id_expediente, 
$id_usuario, $tipo_cambio, $valor_anterior, $valor_nuevo) { 
        $sql = "INSERT INTO historial_expedientes (id_expediente, 
id_usuario, tipo_cambio, valor_anterior, valor_nuevo, 
fecha_modificacion) 
                VALUES (:id_expediente, :id_usuario, :tipo_cambio, 
:valor_anterior, :valor_nuevo, NOW())"; 
        $stmt = $this->db->prepare($sql); 
        $stmt->bindValue(':id_expediente', $id_expediente, 
PDO::PARAM_INT); 
        $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT); 
        $stmt->bindValue(':tipo_cambio', $tipo_cambio); 
        $stmt->bindValue(':valor_anterior', $valor_anterior); 
        $stmt->bindValue(':valor_nuevo', $valor_nuevo); 
        return $stmt->execute(); 
    } 
 
    // Obtener el historial de modificaciones de un expediente 
    public function obtenerHistorialExpediente($id_expediente) { 
        $sql = "SELECT h.*, u.nombre_completo 
                FROM historial_expedientes h 
                INNER JOIN usuarios u ON h.id_usuario = u.id_usuario 
                WHERE h.id_expediente = :id_expediente 
                ORDER BY h.fecha_modificacion DESC, h.id_historial 
DESC"; 
        $stmt = $this->db->prepare($sql); 
        $stmt->bindValue(':id_expediente', $id_expediente); 
        $stmt->execute(); 
        return $stmt->fetchAll(PDO::FETCH_ASSOC); 
    } 
 
    // Obtener una sesión clínica específica con los datos de su 
expediente 
    public function obtenerSesionPorId($id_sesion) { 
        $sql = "SELECT sc.*, e.id_expediente, p.nombres, p.apellidos, 
p.nie_dui 
                FROM sesiones_clinicas sc 
                INNER JOIN expedientes e ON sc.id_expediente = 
e.id_expediente 
                INNER JOIN pacientes p ON e.id_paciente = p.id_paciente 
                WHERE sc.id_sesion = :id_sesion LIMIT 1"; 
        $stmt = $this->db->prepare($sql); 
        $stmt->bindValue(':id_sesion', $id_sesion); 
        $stmt->execute(); 
        return $stmt->fetch(PDO::FETCH_ASSOC); 
    } 
 
    // Actualizar los campos editables de una sesión clínica 
    public function actualizarSesion($id_sesion, $tipo_consulta, 
$observaciones, $intervencion, $notas) { 
        $sql = "UPDATE sesiones_clinicas 
                SET tipo_consulta = :tipo, observaciones_generales = 
:obs, intervencion_realizada = :inter, notas_evolucion = :notas 
                WHERE id_sesion = :id_sesion"; 
        $stmt = $this->db->prepare($sql); 
        $stmt->bindValue(':tipo', $tipo_consulta); 
        $stmt->bindValue(':obs', $observaciones); 
        $stmt->bindValue(':inter', $intervencion); 
        $stmt->bindValue(':notas', $notas); 
        $stmt->bindValue(':id_sesion', $id_sesion); 
        return $stmt->execute(); 
    } 
 
    // Registrar en el historial un cambio en un campo de una sesión 
clínica 
    public function registrarCambioSesion($id_sesion, $id_usuario, 
$campo, $valor_anterior, $valor_nuevo) { 
        $sql = "INSERT INTO historial_sesiones (id_sesion, id_usuario, 
campo, valor_anterior, valor_nuevo, fecha_modificacion) 
                VALUES (:id_sesion, :id_usuario, :campo, 
:valor_anterior, :valor_nuevo, NOW())"; 
        $stmt = $this->db->prepare($sql); 
        $stmt->bindValue(':id_sesion', $id_sesion, PDO::PARAM_INT); 
        $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT); 
        $stmt->bindValue(':campo', $campo); 
        $stmt->bindValue(':valor_anterior', $valor_anterior); 
        $stmt->bindValue(':valor_nuevo', $valor_nuevo); 
        return $stmt->execute(); 
    } 
 
    // Obtener todo el historial de cambios de las sesiones de un 
expediente (para agrupar en la vista) 
    public function 
obtenerHistorialSesionesPorExpediente($id_expediente) { 
        $sql = "SELECT hs.*, u.nombre_completo 
                FROM historial_sesiones hs 
                INNER JOIN sesiones_clinicas sc ON hs.id_sesion = 
sc.id_sesion 
                INNER JOIN usuarios u ON hs.id_usuario = u.id_usuario 
                WHERE sc.id_expediente = :id_expediente 
                ORDER BY hs.fecha_modificacion DESC, hs.id_historial 
DESC"; 
        $stmt = $this->db->prepare($sql); 
        $stmt->bindValue(':id_expediente', $id_expediente); 
        $stmt->execute(); 
        return $stmt->fetchAll(PDO::FETCH_ASSOC); 
    } 
 
    // Guardar una nueva sesión clínica (Ahora con tipo_consulta) 
    public function guardarSesion($id_expediente, $tipo_consulta, 
$observaciones, $intervencion, $notas, $id_cita = null) { 
        $sql = "INSERT INTO sesiones_clinicas (id_cita, id_expediente, 
tipo_consulta, observaciones_generales, intervencion_realizada, 
notas_evolucion, fecha_registro) 
                VALUES (:id_cita, :id_expediente, :tipo_consulta, 
:observaciones, :intervencion, :notas, NOW())"; 
        
        $stmt = $this->db->prepare($sql); 
        $stmt->bindValue(':id_cita', !empty($id_cita) ? $id_cita : 
null, !empty($id_cita) ? PDO::PARAM_INT : PDO::PARAM_NULL); 
        $stmt->bindValue(':id_expediente', $id_expediente); 
        $stmt->bindValue(':tipo_consulta', $tipo_consulta); 
        $stmt->bindValue(':observaciones', $observaciones); 
        $stmt->bindValue(':intervencion', $intervencion); 
        $stmt->bindValue(':notas', $notas); 
        
        return $stmt->execute(); 
    } 
 
    // Obtener los datos básicos de una cita (para saber qué paciente 
la agendó) 
    public function obtenerCitaPorId($id_cita) { 
        $sql = "SELECT * FROM citas WHERE id_cita = :id_cita LIMIT 1"; 
        $stmt = $this->db->prepare($sql); 
        $stmt->bindValue(':id_cita', $id_cita); 
        $stmt->execute(); 
        return $stmt->fetch(PDO::FETCH_ASSOC); 
    } 
 
    // Actualizar el estado de la cita a 'Atendida' 
    public function marcarCitaComoAtendida($id_cita) { 
        $sql = "UPDATE citas SET estado_cita = 'Completada' WHERE 
id_cita = :id_cita"; 
        $stmt = $this->db->prepare($sql); 
        $stmt->bindValue(':id_cita', $id_cita, PDO::PARAM_INT); 
        return $stmt->execute(); 
    } 
} 