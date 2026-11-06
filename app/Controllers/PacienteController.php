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
            $pacientes = $pacienteModel->buscarPacientes($busqueda, $soloActivos);
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
            if (!$nie_dui || !$nombres || !$apellidos || !$fecha_nacimiento || !$genero || !$tipo_paciente) {
                Flash::error('Todos los campos obligatorios deben ser completados.');
                $this->redirect(BASE_URL . '/paciente/crear');
            }

            // Datos opcionales
            $grado_seccion = !empty($_POST['grado_seccion']) ? trim($_POST['grado_seccion']) : null;
            $telefono_contacto = !empty($_POST['telefono_contacto']) ? trim($_POST['telefono_contacto']) : null;
            $correo_paciente = !empty($_POST['correo_paciente']) ? trim($_POST['correo_paciente']) : null;
            $nombre_responsable = !empty($_POST['nombre_responsable']) ? trim($_POST['nombre_responsable']) : null;

            $pacienteModel = $this->modelo('Paciente');
            
            $exito = $pacienteModel->crearPaciente($nie_dui, $nombres, $apellidos, $fecha_nacimiento, $genero, $tipo_paciente, $grado_seccion, $telefono_contacto, $correo_paciente, $nombre_responsable);

            if ($exito) {
                Logger::info('Paciente creado', ['nie_dui' => $nie_dui, 'nombres' => $nombres, 'apellidos' => $apellidos]);
                Flash::success('Paciente registrado correctamente.');
                $this->redirect(BASE_URL . '/paciente');
            } else {
                Logger::error('Error al crear paciente', ['nie_dui' => $nie_dui]);
                Flash::error('Hubo un error al guardar el paciente.');
                $this->redirect(BASE_URL . '/paciente/crear');
            }
        }
    }

    // Muestra el formulario de edición con los datos actuales del paciente
    public function editar($id_paciente) {
        $pacienteModel = $this->modelo('Paciente');
        $paciente = $pacienteModel->obtenerPorId((int)$id_paciente);

        if (!$paciente) {
            Flash::error('Paciente no encontrado.');
            $this->redirect(BASE_URL . '/paciente');
        }

        $this->vista('pacientes/editar', [
            'paciente' => $paciente,
            'flash'    => Flash::all()
        ]);
    }

    // Valida los datos ingresados y actualiza el registro del paciente
    public function actualizar($id_paciente) {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect(BASE_URL . '/paciente');
        }

        $id_paciente = (int)$id_paciente;
        $pacienteModel = $this->modelo('Paciente');
        $paciente = $pacienteModel->obtenerPorId($id_paciente);

        if (!$paciente) {
            Flash::error('Paciente no encontrado.');
            $this->redirect(BASE_URL . '/paciente');
        }

        // Datos del formulario
        $nie_dui           = trim($_POST['nie_dui'] ?? '');
        $nombres           = trim($_POST['nombres'] ?? '');
        $apellidos         = trim($_POST['apellidos'] ?? '');
        $fecha_nacimiento  = trim($_POST['fecha_nacimiento'] ?? '');
        $genero            = trim($_POST['genero'] ?? '');
        $tipo_paciente     = trim($_POST['tipo_paciente'] ?? '');
        $grado_seccion     = trim($_POST['grado_seccion'] ?? '');
        $telefono_contacto = trim($_POST['telefono_contacto'] ?? '');
        $correo_paciente   = trim($_POST['correo_paciente'] ?? '');
        $nombre_responsable = trim($_POST['nombre_responsable'] ?? '');

        // Validación de los datos ingresados antes de guardar
        $errores = $this->validarDatosPaciente([
            'id_paciente'         => $id_paciente,
            'nie_dui'             => $nie_dui,
            'nombres'             => $nombres,
            'apellidos'           => $apellidos,
            'fecha_nacimiento'    => $fecha_nacimiento,
            'genero'              => $genero,
            'tipo_paciente'       => $tipo_paciente,
            'telefono_contacto'   => $telefono_contacto,
            'correo_paciente'     => $correo_paciente,
            'nieDuiexiste'        => $pacienteModel->nieDuiexiste($nie_dui, $id_paciente)
        ]);

        if (!empty($errores)) {
            Flash::error(implode(' ', $errores));
            $this->redirect(BASE_URL . '/paciente/editar/' . $id_paciente);
        }

        // Campos opcionales: si el paciente es empleado, el grado/sección no aplica
        $grado_seccion_valor = ($tipo_paciente === 'Empleado') ? null : ($grado_seccion !== '' ? $grado_seccion : null);
        $telefono_contacto_valor = ($telefono_contacto !== '') ? $telefono_contacto : null;
        $correo_paciente_valor = ($correo_paciente !== '') ? $correo_paciente : null;
        $nombre_responsable_valor = ($nombre_responsable !== '') ? $nombre_responsable : null;

        // Detectar qué campos cambiaron para el registro de auditoría
        $cambios = $this->detectarCambios($paciente, [
            'nie_dui'             => $nie_dui,
            'nombres'             => $nombres,
            'apellidos'           => $apellidos,
            'fecha_nacimiento'    => $fecha_nacimiento,
            'genero'              => $genero,
            'tipo_paciente'       => $tipo_paciente,
            'grado_seccion'       => $grado_seccion_valor,
            'telefono_contacto'   => $telefono_contacto_valor,
            'correo_paciente'     => $correo_paciente_valor,
            'nombre_responsable' => $nombre_responsable_valor
        ]);

        $exito = $pacienteModel->actualizar(
            $id_paciente,
            $nie_dui,
            $nombres,
            $apellidos,
            $fecha_nacimiento,
            $genero,
            $tipo_paciente,
            $grado_seccion_valor,
            $telefono_contacto_valor,
            $correo_paciente_valor,
            $nombre_responsable_valor
        );

        if ($exito) {
            $nombreCompleto = $nombres . ' ' . $apellidos;
            $this->registrarAuditoria('ACTUALIZAR', 'paciente', $id_paciente, 'Datos del paciente actualizados: '
                . $nombreCompleto . ' (NIE/DUI ' . $nie_dui . '). Campos modificados: '
                . (empty($cambios) ? 'ninguno' : implode(', ', $cambios)) . '.');
            Logger::info('Paciente actualizado', [
                'id_paciente' => $id_paciente,
                'usuario'     => $_SESSION['usuario_id'],
                'campos'      => empty($cambios) ? 'ninguno' : implode(', ', $cambios)
            ]);
            Flash::success('Los datos del paciente ' . $nombreCompleto . ' se actualizaron correctamente.');
            $this->redirect(BASE_URL . '/paciente');
        } else {
            Logger::error('Error al actualizar paciente', ['id_paciente' => $id_paciente]);
            Flash::error('Hubo un error al actualizar el paciente.');
            $this->redirect(BASE_URL . '/paciente/editar/' . $id_paciente);
        }
    }

    // Valida el conjunto de datos del paciente; devuelve la lista de errores
    private function validarDatosPaciente($d) {
        $errores = [];

        if ($d['nie_dui'] === '') {
            $errores[] = 'El NIE/DUI es obligatorio.';
        } elseif (strlen($d['nie_dui']) > 20) {
            $errores[] = 'El NIE/DUI no puede superar los 20 caracteres.';
        } elseif (!empty($d['nieDuiexiste'])) {
            $errores[] = 'El NIE/DUI ya está registrado en otro paciente.';
        }

        if ($d['nombres'] === '') {
            $errores[] = 'Los nombres son obligatorios.';
        } elseif (strlen($d['nombres']) > 100) {
            $errores[] = 'Los nombres no pueden superar los 100 caracteres.';
        }

        if ($d['apellidos'] === '') {
            $errores[] = 'Los apellidos son obligatorios.';
        } elseif (strlen($d['apellidos']) > 100) {
            $errores[] = 'Los apellidos no pueden superar los 100 caracteres.';
        }

        if ($d['fecha_nacimiento'] === '') {
            $errores[] = 'La fecha de nacimiento es obligatoria.';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d['fecha_nacimiento'])) {
            $errores[] = 'La fecha de nacimiento no tiene un formato válido.';
        } else {
            $nacimiento = \DateTime::createFromFormat('Y-m-d', $d['fecha_nacimiento']);
            $nacimientoValido = $nacimiento && $nacimiento->format('Y-m-d') === $d['fecha_nacimiento'];
            if (!$nacimientoValido) {
                $errores[] = 'La fecha de nacimiento no es una fecha válida.';
            } elseif ($nacimiento->getTimestamp() > strtotime('today')) {
                $errores[] = 'La fecha de nacimiento no puede ser una fecha futura.';
            } elseif ($nacimiento->getTimestamp() < strtotime('1900-01-01')) {
                $errores[] = 'La fecha de nacimiento es demasiado antigua.';
            }
        }

        if (!in_array($d['genero'], ['Masculino', 'Femenino', 'Otro'], true)) {
            $errores[] = 'Selecciona un género válido.';
        }

        if (!in_array($d['tipo_paciente'], ['Estudiante', 'Empleado'], true)) {
            $errores[] = 'Selecciona un tipo de paciente válido.';
        }

        if ($d['correo_paciente'] !== '' && !filter_var($d['correo_paciente'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El correo electrónico no tiene un formato válido.';
        }

        if ($d['telefono_contacto'] !== '' && !preg_match('/^\+?[0-9\s\-()]{7,20}$/', $d['telefono_contacto'])) {
            $errores[] = 'El teléfono de contacto no tiene un formato válido.';
        }

        return $errores;
    }

    // Lista los campos que cambiaron respecto a los valores guardados
    private function detectarCambios($paciente, $nuevos) {
        $etiquetas = [
            'nie_dui'             => 'NIE/DUI',
            'nombres'             => 'nombres',
            'apellidos'           => 'apellidos',
            'fecha_nacimiento'    => 'fecha de nacimiento',
            'genero'              => 'género',
            'tipo_paciente'       => 'tipo de paciente',
            'grado_seccion'       => 'grado/sección',
            'telefono_contacto'   => 'teléfono de contacto',
            'correo_paciente'     => 'correo electrónico',
            'nombre_responsable' => 'nombre del responsable'
        ];

        $cambios = [];
        foreach ($etiquetas as $campo => $etiqueta) {
            $valorAnterior = (string)($paciente[$campo] ?? '');
            $valorNuevo = (string)($nuevos[$campo] ?? '');
            if ($valorAnterior !== $valorNuevo) {
                $cambios[] = $etiqueta;
            }
        }
        return $cambios;
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

        $dependencias = $pacienteModel->contarDependencias((int)$paciente['id_paciente']);
        $critico = array_sum(array_map('intval', $dependencias)) > 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validación reforzada: si hay información crítica se exige escribir el NIE/DUI y aceptar
            if ($critico) {
                $confirmacion = trim($_POST['confirmacion_nie'] ?? '');
                $acepta = isset($_POST['aceptar']);

                if (strcasecmp($confirmacion, $paciente['nie_dui']) !== 0) {
                    Flash::error('La confirmación no coincide. Escribe exactamente el NIE/DUI del paciente.');
                    $this->redirect(BASE_URL . '/paciente/eliminar/' . $paciente['id_paciente']);
                }
                if (!$acepta) {
                    Flash::error('Debes marcar la casilla para confirmar que entiendes las consecuencias.');
                    $this->redirect(BASE_URL . '/paciente/eliminar/' . $paciente['id_paciente']);
                }
            }

            $ok = $pacienteModel->desactivar((int)$paciente['id_paciente']);

            if ($ok) {
                $nombreCompleto = $paciente['nombres'] . ' ' . $paciente['apellidos'];
                $descripcion = 'Paciente desactivado (eliminación lógica): ' . $nombreCompleto
                    . ' (NIE/DUI ' . $paciente['nie_dui'] . '). Registros asociados: ' . $this->resumenDependencias($dependencias) . '.';
                $this->registrarAuditoria('DESACTIVAR', 'paciente', (int)$paciente['id_paciente'], $descripcion);
                Logger::info('Paciente desactivado', ['id_paciente' => $paciente['id_paciente'], 'usuario' => $_SESSION['usuario_id']]);
                Flash::success('El paciente ' . $nombreCompleto . ' fue desactivado. Su historial se conserva.');
            } else {
                Logger::error('Error al desactivar paciente', ['id_paciente' => $paciente['id_paciente']]);
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
            $nombreCompleto = $paciente['nombres'] . ' ' . $paciente['apellidos'];
            $this->registrarAuditoria('REACTIVAR', 'paciente', (int)$paciente['id_paciente'], 'Paciente reactivado: ' . $nombreCompleto . ' (NIE/DUI ' . $paciente['nie_dui'] . ').');
            Logger::info('Paciente reactivado', ['id_paciente' => $paciente['id_paciente'], 'usuario' => $_SESSION['usuario_id']]);
            Flash::success('Paciente ' . $nombreCompleto . ' reactivado correctamente.');
        } else {
            Logger::error('Error al reactivar paciente', ['id_paciente' => $paciente['id_paciente']]);
            Flash::error('No se pudo reactivar el paciente.');
        }

        $this->redirect(BASE_URL . '/paciente?inactivos=1');
    }

    // Registrar en la tabla de auditoría (sin interrumpir la operación si falla)
    private function registrarAuditoria($accion, $entidad, $id_entidad, $descripcion) {
        try {
            $this->modelo('Auditoria')->registrar(
                (int)($_SESSION['usuario_id'] ?? 0),
                $accion,
                $entidad,
                $id_entidad,
                $descripcion
            );
        } catch (\Throwable $e) {
            Logger::error('No se pudo registrar la acción en auditoría', ['error' => $e->getMessage()]);
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