<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * MetricasController: alias historico del panel de metricas.
 *
 * El panel de metricas ES el dashboard de la Direccion (rol 2) y del
 * Administrador (rol 3), por lo que la URL canonica es /dashboard.
 * Esta ruta se conserva para que los enlaces y marcadores anteriores sigan
 * funcionando: redirige al dashboard conservando el periodo seleccionado.
 */
class MetricasController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requiere('metricas');
    }

    public function index() {
        $periodo = $_GET['periodo'] ?? '';
        $url = BASE_URL . '/dashboard';

        if ($periodo !== '') {
            $url .= '?periodo=' . urlencode($periodo);
        }

        $this->redirect($url);
    }
}