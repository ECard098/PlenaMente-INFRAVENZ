<?php 
namespace App\Controllers; 
 
use App\Core\Controller; 
use App\Core\Flash; 
use App\Core\Logger; 
 
class ExpedienteController extends Controller { 
 
    public function __construct() { 
        parent::__construct(); 
        
        if (!isset($_SESSION['usuario_id'])) { 
            $this->redirect(BASE_URL . '/auth/login'); 
        } 
 
        if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] !== 1) { 
            $this->redirect(BASE_URL . '/dashboard'); 
        } 
    } 
 
    // Pantalla principal del listado de expedientes 
    public function index() { 
        $expedienteModel = $this->modelo('Expediente'); 
        $expedientes = $expedienteModel->obtenerTodos(); 
 
        $this->vista('expedientes/index', [ 
            'expedientes' => $expedientes 
        ]); 
    } 
 
    // Mostrar el formulario para abrir un expediente (pasa el id del 
paciente seleccionado) 
    public function crear($id_paciente) { 
        $pacienteModel = $this->modelo('Paciente'); 
        $expedienteModel = $this->modelo('Expediente'); 
 
        // Validamos si ya existe un expediente para este paciente 
        if ($expedienteModel->verificarSiExiste($id_paciente)) { 
            Flash::warning('Este paciente ya tiene un expediente 
abierto.'); 
            $this->redirect(BASE_URL . '/expediente'); 
        } 
 
        // Buscamos los datos básicos del paciente para mostrarlos en 
el formulario 
        $paciente = $pacienteModel->obtenerPorId($id_paciente); 
 
        if (!$paciente) { 
            Flash::error('Paciente no encontrado.'); 
            $this->redirect(BASE_URL . '/paciente'); 
        } 
 
        $this->vista('expedientes/crear', [ 
            'paciente' => $paciente 
        ]); 
    } 
 
    // Procesar la inserción de los datos en la base de datos 
    public function guardar() { 
        if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
            $id_paciente = (int)($_POST['id_paciente'] ?? 0); 
            $fecha_apertura = $_POST['fecha_apertura'] ?? ''; 
            $antecedentes_familiares = 
!empty($_POST['antecedentes_familiares']) ? 
trim($_POST['antecedentes_familiares']) : null; 
            $antecedentes_medicos = 
!empty($_POST['antecedentes_medicos']) ? 
trim($_POST['antecedentes_medicos']) : null; 
 
            if (!$id_paciente || !$fecha_apertura) { 
                Flash::error('Paciente y fecha de apertura son 
obligatorios.'); 
                $this->redirect(BASE_URL . '/expediente/crear/' . 
$id_paciente); 
            } 
 
            $expedienteModel = $this->modelo('Expediente'); 
            $exito = $expedienteModel->crearExpediente($id_paciente, 
$fecha_apertura, $antecedentes_familiares, $antecedentes_medicos); 
 
            if ($exito) { 
                Logger::info('Expediente creado', ['id_paciente' => 
$id_paciente]); 
                Flash::success('Expediente abierto correctamente.'); 
                $this->redirect(BASE_URL . '/expediente'); 
            } else { 
                Logger::error('Error al crear expediente', 
['id_paciente' => $id_paciente]); 
                Flash::error('Hubo un error al intentar abrir el 
expediente.'); 
                $this->redirect(BASE_URL . '/expediente/crear/' . 
$id_paciente); 
            } 
        } 
    } 
 
    public function ver($id) { 
        $modeloExpediente = $this->modelo('Expediente'); 
        
        $expediente = $modeloExpediente->obtenerPorId($id); 
        
        if (!$expediente) { 
            Flash::error('Expediente no encontrado.'); 
            $this->redirect(BASE_URL . '/paciente'); 
        } 
 
        $orden = strtoupper($_GET['orden'] ?? 'DESC'); 
        $orden = in_array($orden, ['ASC', 'DESC']) ? $orden : 'DESC'; 
        
        $sesiones = 
$modeloExpediente->obtenerSesionesPorExpediente($id, $orden); 
        $vecinos = $modeloExpediente->obtenerVecinos($id); 
        $historial = 
$modeloExpediente->obtenerHistorialExpediente($id); 
        $historialSesiones = []; 
        foreach 
($modeloExpediente->obtenerHistorialSesionesPorExpediente($id) as $hs) 
{ 
            $historialSesiones[$hs['id_sesion']][] = $hs; 
        } 
        
        $this->vista('expedientes/ver', [ 
            'expediente' => $expediente, 
            'sesiones'   => $sesiones, 
            'orden'      => $orden, 
            'total_sesiones' => count($sesiones), 
            'vecinos'    => $vecinos, 
            'historial'  => $historial, 
            'historial_sesiones' => $historialSesiones 
        ]); 
    } 
 
    // Mostrar el formulario para actualizar los antecedentes clínicos 
del expediente 
    public function editar($id_expediente) { 
        $modeloExpediente = $this->modelo('Expediente'); 
        $expediente = $modeloExpediente->obtenerPorId($id_expediente); 
 
        if (!$expediente) { 
            Flash::error('Expediente no encontrado.'); 
            $this->redirect(BASE_URL . '/expediente'); 
        } 
 
        $this->vista('expedientes/editar', [ 
            'expediente' => $expediente 
        ]); 
    } 
 
    // Procesar la actualización de antecedentes conservando el 
historial de cambios 
    public function actualizar() { 
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
            $this->redirect(BASE_URL . '/expediente'); 
        } 
 
        $id_expediente = (int)($_POST['id_expediente'] ?? 0); 
        $antecedentes_familiares = 
!empty($_POST['antecedentes_familiares']) ? 
trim($_POST['antecedentes_familiares']) : null; 
        $antecedentes_medicos = !empty($_POST['antecedentes_medicos']) 
? trim($_POST['antecedentes_medicos']) : null; 
 
        if (!$id_expediente) { 
            Flash::error('Expediente inválido.'); 
            $this->redirect(BASE_URL . '/expediente'); 
        } 
 
        $modeloExpediente = $this->modelo('Expediente'); 
        $expediente = $modeloExpediente->obtenerPorId($id_expediente); 
 
        if (!$expediente) { 
            Flash::error('Expediente no encontrado.'); 
            $this->redirect(BASE_URL . '/expediente'); 
        } 
 
        $id_usuario = (int)$_SESSION['usuario_id']; 
 
        // Verificamos cada campo: solo se registra en el historial si 
hubo un cambio real 
        $cambios = [ 
            'Antecedentes familiares' => 
[$expediente['antecedentes_familiares'], $antecedentes_familiares], 
            'Antecedentes médicos' => 
[$expediente['antecedentes_medicos'], $antecedentes_medicos] 
        ]; 
 
        $ok = $modeloExpediente->actualizarAntecedentes($id_expediente, 
$antecedentes_familiares, $antecedentes_medicos); 
 
        $hayCambios = false; 
        foreach ($cambios as $tipo => $par) { 
            list($viejo, $nuevo) = $par; 
            if (trim((string)($viejo ?? '')) !== trim((string)($nuevo 
?? ''))) { 
                
$modeloExpediente->registrarCambioExpediente($id_expediente, 
$id_usuario, $tipo, $viejo, $nuevo); 
                $hayCambios = true; 
            } 
        } 
 
        if ($ok && $hayCambios) { 
            Logger::info('Expediente actualizado', ['id_expediente' => 
$id_expediente, 'usuario' => $id_usuario]); 
            Flash::success('Expediente actualizado correctamente. Los 
cambios quedaron registrados en el historial.'); 
        } elseif ($ok) { 
            Flash::info('No se registraron cambios: los valores son 
iguales a los actuales.'); 
        } else { 
            Logger::error('Error al actualizar expediente', 
['id_expediente' => $id_expediente, 'usuario' => $id_usuario]); 
            Flash::error('Error al actualizar el expediente.'); 
        } 
 
        $this->redirect(BASE_URL . '/expediente/ver/' . 
$id_expediente); 
    } 
 
    // Cambiar el estado del caso (Activo, Seguimiento, Cerrado) 
registrando el historial 
    public function cambiar_estado() { 
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
            $this->redirect(BASE_URL . '/expediente'); 
        } 
 
        $id_expediente = (int)($_POST['id_expediente'] ?? 0); 
        $estado = trim($_POST['estado_caso'] ?? ''); 
        $estadosValidos = ['Activo', 'Seguimiento', 'Cerrado']; 
 
        if (!$id_expediente || !in_array($estado, $estadosValidos, 
true)) { 
            Flash::error('Estado del caso inválido.'); 
            $this->redirect(BASE_URL . '/expediente'); 
        } 
 
        $modeloExpediente = $this->modelo('Expediente'); 
        $expediente = $modeloExpediente->obtenerPorId($id_expediente); 
 
        if (!$expediente) { 
            Flash::error('Expediente no encontrado.'); 
            $this->redirect(BASE_URL . '/expediente'); 
        } 
 
        $estadoAnterior = $expediente['estado_caso'] ?? 'Activo'; 
 
        if ($estadoAnterior === $estado) { 
            Flash::success('El estado del caso ya es el 
seleccionado.'); 
            $this->redirect(BASE_URL . '/expediente/ver/' . 
$id_expediente); 
        } 
 
        $id_usuario = (int)$_SESSION['usuario_id']; 
        $ok = $modeloExpediente->actualizarEstado($id_expediente, 
$estado); 
 
        if ($ok) { 
            
$modeloExpediente->registrarCambioExpediente($id_expediente, 
$id_usuario, 'Estado del caso', $estadoAnterior, $estado); 
            Logger::info('Estado del caso actualizado', [ 
                'id_expediente' => $id_expediente, 
                'anterior' => $estadoAnterior, 
                'nuevo' => $estado, 
                'usuario' => $id_usuario 
            ]); 
            Flash::success('Estado del caso actualizado a "' . $estado 
. '". El cambio quedó registrado en el historial.'); 
        } else { 
            Logger::error('Error al actualizar estado del caso', 
['id_expediente' => $id_expediente]); 
            Flash::error('No se pudo actualizar el estado del caso.'); 
        } 
 
        $this->redirect(BASE_URL . '/expediente/ver/' . 
$id_expediente); 
    } 
 
    // Puente inteligente: Recibe el ID de la cita desde la agenda 
    public function atender_cita($id_cita) { 
        $modeloExpediente = $this->modelo('Expediente'); 
        
        $cita = $modeloExpediente->obtenerCitaPorId($id_cita); 
        if (!$cita) { 
            Flash::error('Cita no encontrada.'); 
            $this->redirect(BASE_URL . '/cita'); 
        } 
 
        $expediente = 
$modeloExpediente->verificarSiExiste($cita['id_paciente']); 
        
        if (!$expediente) { 
            Flash::warning('El paciente no tiene expediente abierto. 
Debe crearlo primero.'); 
            $this->redirect(BASE_URL . '/paciente'); 
        } 
 
        $this->redirect(BASE_URL . 
"/expediente/nueva_sesion/{$expediente['id_expediente']}?id_cita={$id_c
ita}"); 
    } 
 
    // Mostrar formulario de nueva sesión 
    public function nueva_sesion($id_expediente) { 
        $modeloExpediente = $this->modelo('Expediente'); 
        $expediente = $modeloExpediente->obtenerPorId($id_expediente); 
        
        if (!$expediente) { 
            Flash::error('Expediente no encontrado.'); 
            $this->redirect(BASE_URL . '/expediente'); 
        } 
        
        $id_cita = $_GET['id_cita'] ?? null; 
 
        $this->vista('expedientes/nueva_sesion', [ 
            'expediente' => $expediente, 
            'id_cita'    => $id_cita 
        ]); 
    } 
 
    // Procesar el guardado de la sesión 
    public function guardar_sesion() { 
        if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
            $id_expediente = (int)($_POST['id_expediente'] ?? 0); 
            
            $id_cita = (!empty($_POST['id_cita'])) ? 
(int)$_POST['id_cita'] : null; 
            
            $tipo_consulta = trim($_POST['tipo_consulta'] ?? ''); 
            $observaciones = trim($_POST['observaciones_generales'] ?? 
''); 
            $intervencion  = trim($_POST['intervencion_realizada'] ?? 
''); 
            $notas         = trim($_POST['notas_evolucion'] ?? ''); 
 
            if (!$id_expediente || !$tipo_consulta || !$observaciones 
|| !$intervencion || !$notas) { 
                Flash::error('Todos los campos son obligatorios.'); 
                $this->redirect(BASE_URL . 
"/expediente/nueva_sesion/{$id_expediente}" . ($id_cita ? 
"?id_cita={$id_cita}" : '')); 
            } 
 
            $modeloExpediente = $this->modelo('Expediente'); 
            
            $exito = $modeloExpediente->guardarSesion($id_expediente, 
$tipo_consulta, $observaciones, $intervencion, $notas, $id_cita); 
 
            if ($exito) { 
                if ($id_cita) { 
                    
$modeloExpediente->marcarCitaComoAtendida($id_cita); 
                } 
                Logger::info('Sesión clínica guardada', 
['id_expediente' => $id_expediente, 'id_cita' => $id_cita]); 
                Flash::success('Sesión clínica registrada 
correctamente.'); 
                $this->redirect(BASE_URL . '/expediente/ver/' . 
$id_expediente); 
            } else { 
                Logger::error('Error al guardar sesión clínica', 
['id_expediente' => $id_expediente]); 
                Flash::error('Error al guardar la sesión clínica.'); 
                $this->redirect(BASE_URL . 
"/expediente/nueva_sesion/{$id_expediente}" . ($id_cita ? 
"?id_cita={$id_cita}" : '')); 
            } 
        } 
    } 
 
    // Mostrar el formulario para editar una sesión clínica existente 
    public function editar_sesion($id_sesion) { 
        $modeloExpediente = $this->modelo('Expediente'); 
        $sesion = 
$modeloExpediente->obtenerSesionPorId((int)$id_sesion); 
 
        if (!$sesion) { 
            Flash::error('Sesión clínica no encontrada.'); 
            $this->redirect(BASE_URL . '/expediente'); 
        } 
 
        $this->vista('expedientes/editar_sesion', [ 
            'sesion' => $sesion 
        ]); 
    } 
 
    // Procesar la actualización de una sesión conservando el historial 
de cambios 
    public function actualizar_sesion() { 
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
            $this->redirect(BASE_URL . '/expediente'); 
        } 
 
        $id_sesion = (int)($_POST['id_sesion'] ?? 0); 
        $tipo_consulta = trim($_POST['tipo_consulta'] ?? ''); 
        $observaciones = trim($_POST['observaciones_generales'] ?? ''); 
        $intervencion  = trim($_POST['intervencion_realizada'] ?? ''); 
        $notas         = trim($_POST['notas_evolucion'] ?? ''); 
 
        if (!$id_sesion || !$tipo_consulta || !$observaciones || 
!$intervencion || !$notas) { 
            Flash::error('Todos los campos son obligatorios.'); 
            $this->redirect(BASE_URL . '/expediente/editar_sesion/' . 
$id_sesion); 
        } 
 
        $modeloExpediente = $this->modelo('Expediente'); 
        $sesion = $modeloExpediente->obtenerSesionPorId($id_sesion); 
 
        if (!$sesion) { 
            Flash::error('Sesión clínica no encontrada.'); 
            $this->redirect(BASE_URL . '/expediente'); 
        } 
 
        $id_usuario = (int)$_SESSION['usuario_id']; 
 
        $campos = [ 
            'tipo_consulta'           => $tipo_consulta, 
            'observaciones_generales' => $observaciones, 
            'intervencion_realizada'  => $intervencion, 
            'notas_evolucion'         => $notas 
        ]; 
 
        $etiquetas = [ 
            'tipo_consulta'           => 'Tipo de Consulta', 
            'observaciones_generales' => 'Observaciones Generales', 
            'intervencion_realizada'  => 'Intervención Realizada', 
            'notas_evolucion'         => 'Notas de Evolución' 
        ]; 
 
        // Solo registrar en el historial los campos que realmente 
cambiaron 
        $hayCambios = false; 
        foreach ($campos as $campo => $nuevo) { 
            $viejo = $sesion[$campo]; 
            if (trim((string)($viejo ?? '')) !== trim((string)($nuevo 
?? ''))) { 
                $modeloExpediente->registrarCambioSesion($id_sesion, 
$id_usuario, $etiquetas[$campo], $viejo, $nuevo); 
                $hayCambios = true; 
            } 
        } 
 
        $ok = $modeloExpediente->actualizarSesion($id_sesion, 
$tipo_consulta, $observaciones, $intervencion, $notas); 
 
        if ($ok && $hayCambios) { 
            Logger::info('Sesión clínica actualizada', ['id_sesion' => 
$id_sesion, 'id_expediente' => $sesion['id_expediente'], 'usuario' => 
$id_usuario]); 
            Flash::success('Sesión clínica actualizada correctamente. 
Los cambios quedaron registrados.'); 
        } elseif ($ok) { 
            Flash::info('No se registraron cambios: los valores son 
iguales a los actuales.'); 
        } else { 
            Logger::error('Error al actualizar sesión clínica', 
['id_sesion' => $id_sesion, 'usuario' => $id_usuario]); 
            Flash::error('Error al actualizar la sesión clínica.'); 
        } 
 
        $this->redirect(BASE_URL . '/expediente/ver/' . 
$sesion['id_expediente']); 
    } 
} 
 