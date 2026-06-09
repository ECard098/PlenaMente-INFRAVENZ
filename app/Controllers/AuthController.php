<?php
namespace App\Controllers;

use App\Core\Controller;

class AuthController extends Controller {
    
    // 1. Mostrar la pantalla de Login
    public function login() {
        // Si el usuario ya inició sesión, no tiene sentido mostrarle el login, lo mandamos al dashboard
        if(isset($_SESSION['usuario_id'])) {
            header('Location: /plenamente-infravenz/public/dashboard');
            exit;
        }
        
        // Mostramos la vista del formulario
        $this->vista('auth/login');
    }

    // 2. Procesar los datos que vienen del formulario
    public function procesar() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $correo = trim($_POST['correo'] ?? '');
            $password = $_POST['password'] ?? '';

            $usuarioModel = $this->modelo('Usuario');
            $usuario = $usuarioModel->obtenerPorCorreo($correo);

            // 1. Verificamos si el usuario existe
            // 2. Usamos password_verify para comparar la contraseña plana con el hash de la BD
            if($usuario && password_verify($password, $usuario['password_hash'])) {
                
                // 3. ¡NUEVA REGLA! Verificamos si el usuario está activo
                if ($usuario['estado'] !== 'Activo') {
                    // Si está inactivo o suspendido, lo rebotamos con un mensaje específico
                    $this->vista('auth/login', ['error' => 'Esta cuenta ha sido desactivada. Consulte con el administrador.']);
                    return; // Detenemos la ejecución aquí
                }

                // Si está Activo, todo bien. Creamos las variables de sesión
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['usuario_nombre'] = $usuario['nombre_completo'];
                
                // Lo enviamos a su Dashboard
                header('Location: ' . BASE_URL . '/dashboard');
                exit;
            } else {
                // Error genérico para correo o contraseña equivocada
                $this->vista('auth/login', ['error' => 'Correo o contraseña incorrectos.']);
            }
        }
    }

    // 3. Cerrar sesión
    public function logout() {
        // Destruimos todas las variables de sesión
        session_unset();
        session_destroy();
        
        // Lo regresamos al login
        header('Location: /plenamente-infravenz/public/auth/login');
        exit;
    }
}
?>