<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Core\Logger;
use App\Libraries\Correo;

// No hay autoloader: cargar la librería de correo explícitamente
require_once __DIR__ . '/../Libraries/Correo.php';

class AuthController extends Controller {
    
    // 1. Mostrar la pantalla de Login
    public function login() {
        if (isset($_SESSION['usuario_id'])) {
            $this->redirect(BASE_URL . '/dashboard');
        }
        $this->vista('auth/login');
    }

    // 2. Procesar los datos que vienen del formulario
    public function procesar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $correo = trim($_POST['correo'] ?? '');
            $password = $_POST['password'] ?? '';

            if (!$correo || !$password) {
                Flash::error('Correo y contraseña son obligatorios.');
                $this->redirect(BASE_URL . '/auth/login');
            }

            $usuarioModel = $this->modelo('Usuario');
            $usuario = $usuarioModel->obtenerPorCorreo($correo);

            if ($usuario && password_verify($password, $usuario['password_hash'])) {
                if ($usuario['estado'] !== 'Activo') {
                    Logger::warning('Intento de login con cuenta inactiva', ['correo' => $correo]);
                    Flash::error('Esta cuenta ha sido desactivada. Consulte con el administrador.');
                    $this->redirect(BASE_URL . '/auth/login');
                }

                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['usuario_nombre'] = $usuario['nombre_completo'];
                $_SESSION['id_rol'] = $usuario['id_rol'];
                
                Logger::info('Login exitoso', ['usuario_id' => $usuario['id_usuario'], 'rol' => $usuario['id_rol']]);
                Flash::success('Bienvenido, ' . htmlspecialchars($usuario['nombre_completo']));
                $this->redirect(BASE_URL . '/dashboard');
            } else {
                Logger::warning('Intento de login fallido', ['correo' => $correo]);
                Flash::error('Correo o contraseña incorrectos.');
                $this->redirect(BASE_URL . '/auth/login');
            }
        }
    }

    // 3. Cerrar sesión
    public function logout() {
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        session_unset();
        session_destroy();
        
        if ($usuarioId) {
            Logger::info('Logout', ['usuario_id' => $usuarioId]);
        }
        Flash::success('Sesión cerrada correctamente.');
        $this->redirect(BASE_URL . '/auth/login');
    }

    // 4. Solicitar la recuperación de contraseña (envía código temporal)
    public function recuperar() {
        if (isset($_SESSION['usuario_id'])) {
            $this->redirect(BASE_URL . '/dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $correo = trim($_POST['correo'] ?? '');

            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                Flash::error('Ingresa un correo electrónico válido.');
                $this->redirect(BASE_URL . '/auth/recuperar');
            }

            // Respuesta genérica para no revelar si el correo está registrado
            $mensajeGenerico = 'Si el correo ingresado está registrado, recibirás un código para restablecer tu contraseña.';

            $usuarioModel = $this->modelo('Usuario');
            $usuario = $usuarioModel->obtenerPorCorreo($correo);

            if (!$usuario || $usuario['estado'] !== 'Activo') {
                Flash::success($mensajeGenerico);
                $this->redirect(BASE_URL . '/auth/restablecer');
            }

            // Límite de reenvíos: máximo 3 solicitudes por 15 minutos
            if ($usuarioModel->contarRecuperacionesRecientes($usuario['id_usuario']) >= 3) {
                Flash::warning('Ya se generó un código recientemente. Revisa tu correo o espera unos minutos.');
                $this->redirect(BASE_URL . '/auth/recuperar');
            }

            // Código numérico de 6 dígitos criptográficamente seguro
            $codigo = (string) random_int(100000, 999999);

            $expiraEn = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            $usuarioModel->guardarCodigoRecuperacion(
                $usuario['id_usuario'],
                password_hash($codigo, PASSWORD_DEFAULT),
                $expiraEn
            );

            $html = "<h2>Recuperación de contraseña - PlenaMente</h2>"
                . "<p>Hola <strong>" . htmlspecialchars($usuario['nombre_completo']) . "</strong>:</p>"
                . "<p>Tu código temporal para restablecer tu contraseña es:</p>"
                . "<p style=\"font-size:28px; font-weight:bold; letter-spacing:6px; background:#f4f4f4; padding:10px; border-radius:6px; text-align:center;\">" . $codigo . "</p>"
                . "<p>El código es de <strong>un solo uso</strong> y expira en <strong>15 minutos</strong>.</p>"
                . "<p>Si no solicitaste este cambio, ignora este correo.</p>";

            $enviado = Correo::enviar($correo, 'Código de recuperación - PlenaMente', $html);

            if ($enviado) {
                Logger::info('Código de recuperación enviado', ['usuario_id' => $usuario['id_usuario']]);
            } else {
                Logger::error('No se pudo enviar el código de recuperación', ['usuario_id' => $usuario['id_usuario']]);
            }

            Flash::success($mensajeGenerico);
            $this->redirect(BASE_URL . '/auth/restablecer');
        }

        $this->vista('auth/recuperar');
    }

    // 5. Verificar el código y restablecer la contraseña
    public function restablecer() {
        if (isset($_SESSION['usuario_id'])) {
            $this->redirect(BASE_URL . '/dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $correo = trim($_POST['correo'] ?? '');
            $codigo = trim($_POST['codigo'] ?? '');
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';

            if (!$correo || !$codigo || !$password || !$passwordConfirm) {
                Flash::error('Todos los campos son obligatorios.');
                $this->redirect(BASE_URL . '/auth/restablecer');
            }

            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                Flash::error('El correo ingresado no es válido.');
                $this->redirect(BASE_URL . '/auth/restablecer');
            }

            if ($password !== $passwordConfirm) {
                Flash::error('Las contraseñas no coinciden.');
                $this->redirect(BASE_URL . '/auth/restablecer');
            }

            if (!$this->cumpleCriteriosPassword($password)) {
                Flash::error('La contraseña debe tener mínimo 8 caracteres e incluir al menos una mayúscula, una minúscula, un número y un símbolo.');
                $this->redirect(BASE_URL . '/auth/restablecer');
            }

            $usuarioModel = $this->modelo('Usuario');
            $usuario = $usuarioModel->obtenerPorCorreo($correo);

            if (!$usuario || $usuario['estado'] !== 'Activo') {
                Logger::warning('Intento de restablecer contraseña con cuenta no válida', ['correo' => $correo]);
                Flash::error('Código inválido o expirado. Solicita un nuevo código.');
                $this->redirect(BASE_URL . '/auth/restablecer');
            }

            $ahora = date('Y-m-d H:i:s');
            $registro = $usuarioModel->obtenerCodigoActivo($usuario['id_usuario'], $ahora);

            if (!$registro) {
                Flash::error('Código inválido o expirado. Solicita un nuevo código.');
                $this->redirect(BASE_URL . '/auth/restablecer');
            }

            // Máximo 5 intentos de verificación por código
            if ($registro['intentos'] >= 5) {
                $usuarioModel->marcarRecuperacionUsada($registro['id_recuperacion']);
                Flash::error('Demasiados intentos fallidos. Solicita un nuevo código.');
                $this->redirect(BASE_URL . '/auth/restablecer');
            }

            if (!password_verify($codigo, $registro['codigo_hash'])) {
                $usuarioModel->incrementarIntentosRecuperacion($registro['id_recuperacion']);
                Logger::warning('Código de recuperación incorrecto', ['usuario_id' => $usuario['id_usuario']]);
                Flash::error('El código ingresado es incorrecto.');
                $this->redirect(BASE_URL . '/auth/restablecer');
            }

            // Código correcto: actualizar contraseña e invalidar códigos pendientes
            $nuevoHash = password_hash($password, PASSWORD_DEFAULT);
            $usuarioModel->actualizarPassword($usuario['id_usuario'], $nuevoHash);
            $usuarioModel->marcarRecuperacionUsada($registro['id_recuperacion']);
            $usuarioModel->invalidarRecuperaciones($usuario['id_usuario']);

            Logger::info('Contraseña restablecida correctamente', ['usuario_id' => $usuario['id_usuario']]);
            Flash::success('Tu contraseña fue restablecida. Ya puedes iniciar sesión.');
            $this->redirect(BASE_URL . '/auth/login');
        }

        $this->vista('auth/restablecer');
    }

    // Criterios estrictos de seguridad de contraseña
    private function cumpleCriteriosPassword($password): bool {
        if (strlen($password) < 8) return false;
        if (!preg_match('/[A-Z]/', $password)) return false;
        if (!preg_match('/[a-z]/', $password)) return false;
        if (!preg_match('/[0-9]/', $password)) return false;
        if (!preg_match('/[^A-Za-z0-9]/', $password)) return false;
        return true;
    }
}
?>
