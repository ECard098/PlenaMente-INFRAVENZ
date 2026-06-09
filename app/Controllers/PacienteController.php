<?php
namespace App\Controllers;

use App\Core\Controller;

class PacienteController extends Controller {

    // Protegemos el módulo para que solo entren usuarios con sesión
    public function __construct() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
    }
    
    // Método para listar los pacientes
    public function index() {
        $pacienteModel = $this->modelo('Paciente');
        
        $busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

        if (!empty($busqueda)) {
            // Si hay búsqueda, filtra los registros
            $pacientes = $pacienteModel->buscarPacientes($busqueda);
        } else {
            $pacientes = $pacienteModel->obtenerTodos();
        }

        // Envia los pacientes y la búsqueda actual a la vista
        $this->vista('pacientes/index', [
            'pacientes' => $pacientes,
            'busqueda' => $busqueda
        ]);
    }

    // Muestra la pantalla del formulario
    public function crear() {
        // Asumiendo que guardaste tus vistas en la carpeta "pacientes"
        $this->vista('pacientes/crear');
    }

    // Recibe los datos y los guarda en la base de datos
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            // Datos obligatorios
            $nie_dui = trim($_POST['nie_dui']);
            $nombres = trim($_POST['nombres']);
            $apellidos = trim($_POST['apellidos']);
            $fecha_nacimiento = $_POST['fecha_nacimiento'];
            $genero = $_POST['genero'];
            $tipo_paciente = $_POST['tipo_paciente'];
            
            // Datos opcionales
            $grado_seccion = !empty($_POST['grado_seccion']) ? trim($_POST['grado_seccion']) : null;
            $telefono_contacto = !empty($_POST['telefono_contacto']) ? trim($_POST['telefono_contacto']) : null;
            $correo_paciente = !empty($_POST['correo_paciente']) ? trim($_POST['correo_paciente']) : null;
            $nombre_responsable = !empty($_POST['nombre_responsable']) ? trim($_POST['nombre_responsable']) : null;

            $pacienteModel = $this->modelo('Paciente');
            
            $exito = $pacienteModel->crearPaciente($nie_dui, $nombres, $apellidos, $fecha_nacimiento, $genero, $tipo_paciente, $grado_seccion, $telefono_contacto, $correo_paciente, $nombre_responsable);

            if ($exito) {
                header('Location: ' . BASE_URL . '/paciente');
                exit;
            } else {
                echo "Hubo un error al guardar el paciente.";
            }
        }
    }
}