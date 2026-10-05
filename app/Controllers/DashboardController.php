<?php
namespace App\Controllers;

use App\Core\Controller;

class DashboardController extends Controller {

    public function __construct() {
        if (!isset($_SESSION['usuario_id'])) {
            // Usamos la nueva constante
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
    }
    
    public function index() {
        // Si llegó aquí, es porque pasó el constructor con éxito
        $this->vista('dashboard/index');
    }
}