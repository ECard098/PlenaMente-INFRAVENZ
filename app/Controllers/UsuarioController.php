<?php
namespace App\Controllers;

use App\Core\Controller;

class UsuarioController extends Controller {

    
    public function __construct() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
    }
    
    // Método principal que lista los usuarios
    public function index() {
        
        $usuarioModel = $this->modelo('Usuario');
        
        $usuarios = $usuarioModel->obtenerTodos();
        
        $this->vista('usuarios/index', [
            'usuarios' => $usuarios
        ]);
    }

    // Muestra la pantalla del formulario
    public function crear() {
        $this->vista('usuarios/crear');
    }

    // Recibe los datos del formulario, encripta y guarda
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // 1. Recibimos los datos limpios
            $nombre = trim($_POST['nombre_completo']);
            $correo = trim($_POST['correo']);
            $password = $_POST['password'];
            $id_rol = $_POST['id_rol'];

            
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            
            $usuarioModel = $this->modelo('Usuario');
            $exito = $usuarioModel->crearUsuario($id_rol, $nombre, $correo, $password_hash);

            if ($exito) {
                
                header('Location: ' . BASE_URL . '/usuario');
                exit;
            } else {
                echo "Hubo un error al guardar el usuario en la base de datos.";
            }
        }
    }

    // Muestra el formulario con los datos actuales
    public function editar($id = null) {
        if (!$id) {
            header('Location: ' . BASE_URL . '/usuario');
            exit;
        }

        $usuarioModel = $this->modelo('Usuario');
        $usuario = $usuarioModel->obtenerPorId($id);

        // Si el usuario no existe, lo regresamos
        if (!$usuario) {
            header('Location: ' . BASE_URL . '/usuario');
            exit;
        }

        // Le enviamos los datos a la vista
        $this->vista('usuarios/editar', ['usuario' => $usuario]);
    }

    // Procesa los cambios
    public function actualizar($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = trim($_POST['nombre_completo']);
            $correo = trim($_POST['correo']);
            $id_rol = $_POST['id_rol'];
            $estado = $_POST['estado']; // Agregamos el estado (Activo/Inactivo)
            $password = $_POST['password'];

            $password_hash = null;
            // Solo encriptamos si el usuario escribió algo en el campo de contraseña
            if (!empty($password)) {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
            }

            $usuarioModel = $this->modelo('Usuario');
            $usuarioModel->actualizarUsuario($id, $id_rol, $nombre, $correo, $estado, $password_hash);

            // Redirigimos a la lista
            header('Location: ' . BASE_URL . '/usuario');
            exit;
        }
    }

    // Procesa la eliminación lógica
    public function eliminar($id = null) {
        // Verificamos que nos envíen un ID
        if ($id) {
            // REGLA DE SEGURIDAD: Evitar que el usuario activo se desactive a sí mismo
            if ($id != $_SESSION['usuario_id']) {
                $usuarioModel = $this->modelo('Usuario');
                $usuarioModel->eliminarLogico($id);
            }
        }

        // Ya sea que se eliminó o intentó eliminarse a sí mismo, lo regresamos a la tabla
        header('Location: ' . BASE_URL . '/usuario');
        exit;
    }
}