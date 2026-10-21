<?php 
namespace App\Controllers; 
 
use App\Core\Controller; 
use App\Core\Flash; 
use App\Core\Logger; 
 
class PacienteController extends Controller { 
 
    public function __construct() { 
        parent::__construct(); 
        
        if (!isset($_SESSION['usuario_id'])) { 
            $this->redirect(BASE_URL . '/auth/login'); 
        } 
 
        if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] !== 1) { 
            $this->redirect(BASE_URL . '/dashboard'); 
        } 
    } 
    
    // Método para listar los pacientes 
    public function index() { 
        $pacienteModel = $this->modelo('Paciente'); 
        
        $busqueda = trim($_GET['buscar'] ?? ''); 
        $mostrarInactivos = (($_GET['inactivos'] ?? '') === '1'); 
        $soloActivos = !$mostrarInactivos; 
 
        if (!empty($busqueda)) { 
            $pacientes = $pacienteModel->buscarPacientes($busqueda, 
$soloActivos); 
        } else { 
            $pacientes = $pacienteModel->obtenerTodos($soloActivos); 
        } 
 
        $this->vista('pacientes/index', [ 
            'pacientes' => $pacientes, 
            'busqueda' => $busqueda, 
            'mostrarInactivos' => $mostrarInactivos 
        ]); 
    } 
 
    // Muestra la pantalla del formulario 
    public function crear() { 
        $this->vista('pacientes/crear'); 
    } 
 
    // Recibe los datos y los guarda en la base de datos 
    public function guardar() { 
        if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
            // Datos obligatorios 
            $nie_dui = trim($_POST['nie_dui'] ?? ''); 
            $nombres = trim($_POST['nombres'] ?? ''); 
            $apellidos = trim($_POST['apellidos'] ?? ''); 
            $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? ''; 
            $genero = $_POST['genero'] ?? ''; 
            $tipo_paciente = $_POST['tipo_paciente'] ?? ''; 
            
            // Validación básica 
            if (!$nie_dui || !$nombres || !$apellidos || !$fecha_nacimiento || 
!$genero || !$tipo_paciente) { 
                Flash::error('Todos los campos obligatorios deben ser 
completados.'); 
                $this->redirect(BASE_URL . '/paciente/crear'); 
            } 
 
            // Datos opcionales 
            $grado_seccion = !empty($_POST['grado_seccion']) ? 
trim($_POST['grado_seccion']) : null; 
            $telefono_contacto = !empty($_POST['telefono_contacto']) ? 
trim($_POST['telefono_contacto']) : null; 
            $correo_paciente = !empty($_POST['correo_paciente']) ? 
trim($_POST['correo_paciente']) : null; 
            $nombre_responsable = !empty($_POST['nombre_responsable']) ? 
trim($_POST['nombre_responsable']) : null; 
 
            $pacienteModel = $this->modelo('Paciente'); 
            
            $exito = $pacienteModel->crearPaciente($nie_dui, $nombres, 
$apellidos, $fecha_nacimiento, $genero, $tipo_paciente, $grado_seccion, 
$telefono_contacto, $correo_paciente, $nombre_responsable); 
 
            if ($exito) { 
                Logger::info('Paciente creado', ['nie_dui' => $nie_dui, 
'nombres' => $nombres, 'apellidos' => $apellidos]); 
                Flash::success('Paciente registrado correctamente.'); 
                $this->redirect(BASE_URL . '/paciente'); 
            } else { 
                Logger::error('Error al crear paciente', ['nie_dui' => 
$nie_dui]); 
                Flash::error('Hubo un error al guardar el paciente.'); 
                $this->redirect(BASE_URL . '/paciente/crear'); 
            } 
        } 
    } 
 
    // Pantalla de confirmación (GET) y procesamiento (POST) de la eliminación lógica del paciente 
    public function eliminar($id_paciente) { 
        $pacienteModel = $this->modelo('Paciente'); 
        $paciente = $pacienteModel->obtenerPorId((int)$id_paciente); 
 
        if (!$paciente) { 
            Flash::error('Paciente no encontrado.'); 
            $this->redirect(BASE_URL . '/paciente'); 
        } 
 
        if (($paciente['estado_paciente'] ?? 'Activo') !== 'Activo') { 
            Flash::warning('El paciente ya se encuentra inactivo.'); 
            $this->redirect(BASE_URL . '/paciente'); 
        } 
 
        $dependencias = 
$pacienteModel->contarDependencias((int)$paciente['id_paciente']); 
        $critico = array_sum(array_map('intval', $dependencias)) > 0; 
 
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
            // Validación reforzada: si hay información crítica se exige escribir el NIE/DUI y aceptar 
            if ($critico) { 
                $confirmacion = trim($_POST['confirmacion_nie'] ?? ''); 
                $acepta = isset($_POST['aceptar']); 
 
                if (strcasecmp($confirmacion, $paciente['nie_dui']) !== 0) { 
                    Flash::error('La confirmación no coincide. Escribe 
exactamente el NIE/DUI del paciente.'); 
                    $this->redirect(BASE_URL . '/paciente/eliminar/' . 
$paciente['id_paciente']); 
                } 
                if (!$acepta) { 
                    Flash::error('Debes marcar la casilla para confirmar que 
entiendes las consecuencias.'); 
                    $this->redirect(BASE_URL . '/paciente/eliminar/' . 
$paciente['id_paciente']); 
                } 
            } 
 
            $ok = $pacienteModel->desactivar((int)$paciente['id_paciente']); 
 
            if ($ok) { 
                $nombreCompleto = $paciente['nombres'] . ' ' . 
$paciente['apellidos']; 
                $descripcion = 'Paciente desactivado (eliminación lógica): ' . 
$nombreCompleto 
                    . ' (NIE/DUI ' . $paciente['nie_dui'] . '). Registros 
asociados: ' . $this->resumenDependencias($dependencias) . '.'; 
                $this->registrarAuditoria('DESACTIVAR', 'paciente', 
(int)$paciente['id_paciente'], $descripcion); 
                Logger::info('Paciente desactivado', ['id_paciente' => 
$paciente['id_paciente'], 'usuario' => $_SESSION['usuario_id']]); 
                Flash::success('El paciente ' . $nombreCompleto . ' fue 
desactivado. Su historial se conserva.'); 
            } else { 
                Logger::error('Error al desactivar paciente', ['id_paciente' 
=> $paciente['id_paciente']]); 
                Flash::error('No se pudo desactivar el paciente.'); 
            } 
            $this->redirect(BASE_URL . '/paciente'); 
        } 
 
        $this->vista('pacientes/eliminar', [ 
            'paciente'     => $paciente, 
            'dependencias' => $dependencias, 
            'critico'      => $critico, 
            'flash'        => Flash::all() 
        ]); 
    } 
 
    // Reactivar un paciente previamente desactivado 
    public function reactivar($id_paciente) { 
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
            $this->redirect(BASE_URL . '/paciente'); 
        } 
 
        $pacienteModel = $this->modelo('Paciente'); 
        $paciente = $pacienteModel->obtenerPorId((int)$id_paciente); 
 
        if (!$paciente) { 
            Flash::error('Paciente no encontrado.'); 
            $this->redirect(BASE_URL . '/paciente'); 
        } 
 
        $ok = $pacienteModel->reactivar((int)$paciente['id_paciente']); 
 
        if ($ok) { 
            $nombreCompleto = $paciente['nombres'] . ' ' . 
$paciente['apellidos']; 
            $this->registrarAuditoria('REACTIVAR', 'paciente', 
(int)$paciente['id_paciente'], 'Paciente reactivado: ' . $nombreCompleto . ' 
(NIE/DUI ' . $paciente['nie_dui'] . ').'); 
            Logger::info('Paciente reactivado', ['id_paciente' => 
$paciente['id_paciente'], 'usuario' => $_SESSION['usuario_id']]); 
            Flash::success('Paciente ' . $nombreCompleto . ' reactivado 
correctamente.'); 
        } else { 
            Logger::error('Error al reactivar paciente', ['id_paciente' => 
$paciente['id_paciente']]); 
            Flash::error('No se pudo reactivar el paciente.'); 
        } 
 
        $this->redirect(BASE_URL . '/paciente?inactivos=1'); 
    } 
 
    // Registrar en la tabla de auditoría (sin interrumpir la operación si falla) 
    private function registrarAuditoria($accion, $entidad, $id_entidad, 
$descripcion) { 
        try { 
            $this->modelo('Auditoria')->registrar( 
                (int)($_SESSION['usuario_id'] ?? 0), 
                $accion, 
                $entidad, 
                $id_entidad, 
                $descripcion 
            ); 
        } catch (\Throwable $e) { 
            Logger::error('No se pudo registrar la acción en auditoría', 
['error' => $e->getMessage()]); 
        } 
    } 
 
    // Texto legible del detalle de registros asociados al paciente 
    private function resumenDependencias($dep) { 
        $etiquetas = [ 
            'expedientes' => 'expediente(s)', 
            'sesiones'    => 'sesión(es)', 
            'citas'       => 'cita(s)', 
            'reportes'    => 'reporte(s)', 
            'asistencias' => 'asistencia(s)' 
        ]; 
        $partes = []; 
        foreach ($etiquetas as $clave => $etiqueta) { 
            if (!empty($dep[$clave])) { 
                $partes[] = (int)$dep[$clave] . ' ' . $etiqueta; 
            } 
        } 
        return $partes ? implode(', ', $partes) : 'ninguno'; 
    } 
} 