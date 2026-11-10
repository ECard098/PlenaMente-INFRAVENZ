<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Logger;
use App\Core\Metricas;
use App\Core\Permisos;

// No hay autoloader: el constructor del panel se carga de forma explicita
require_once __DIR__ . '/../Core/Metricas.php';

class DashboardController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requiere('dashboard');
    }

    /**
     * El dashboard se adapta al rol:
     * - Psicologo (rol 1): vista operativa (citas de hoy y agenda).
     * - Director (rol 2) y Administrador (rol 3): panel de metricas
     *   institucionales para el analisis general.
     */
    public function index() {
        if (Permisos::puede('metricas')) {
            return $this->dashboardDeMetricas();
        }

        $dashboardModel = $this->modelo('Dashboard');
        $citasHoy = $dashboardModel->obtenerCitasHoy();
        $estudiantesActivos = $dashboardModel->obtenerEstudiantesActivos();
        $consultasMes = $dashboardModel->obtenerConsultasMes();
        $proximasCitas = $dashboardModel->obtenerProximasCitas();

        Logger::debug('Dashboard accessed', ['usuario_id' => $_SESSION['usuario_id']]);

        $this->vista('dashboard/index', [
            'citas_hoy' => $citasHoy,
            'estudiantes_activos' => $estudiantesActivos,
            'consultas_mes' => $consultasMes,
            'proximas_citas' => $proximasCitas
        ]);
    }

    // Panel de metricas institucionales (Direccion y Administrador)
    private function dashboardDeMetricas() {
        $periodo = $_GET['periodo'] ?? '6meses';
        $datos = Metricas::construir($this->modelo('Dashboard'), $periodo);
        $datos['flash'] = \App\Core\Flash::all();

        Logger::info('Dashboard de metricas consultado', [
            'periodo' => $periodo,
            'usuario' => $_SESSION['usuario_id']
        ]);

        $this->vista('metricas/index', $datos);
    }
}