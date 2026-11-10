<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Core\Logger;

/**
 * AuditoriaController: consulta del registro de acciones criticas del sistema.
 *
 * Solo el Administrador (rol 3) tiene acceso, ya que el registro puede
 * contener referencias a informacion clinica.
 */
class AuditoriaController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requiere('auditoria');
    }

    // Listado de acciones con filtros por usuario, tipo de accion, entidad y fecha
    public function index() {
        $modelo = $this->modelo('Auditoria');

        $filtros = [
            'id_usuario' => (int)($_GET['usuario'] ?? 0),
            'accion'     => trim($_GET['accion'] ?? ''),
            'entidad'    => trim($_GET['entidad'] ?? ''),
            'desde'      => trim($_GET['desde'] ?? ''),
            'hasta'      => trim($_GET['hasta'] ?? ''),
            'busqueda'   => trim($_GET['buscar'] ?? '')
        ];

        // Se ignoran los rangos de fecha con formato invalido
        $patronFecha = '/^\d{4}-\d{2}-\d{2}$/';
        if (!preg_match($patronFecha, $filtros['desde'])) {
            $filtros['desde'] = '';
        }
        if (!preg_match($patronFecha, $filtros['hasta'])) {
            $filtros['hasta'] = '';
        }
        if ($filtros['desde'] !== '' && $filtros['hasta'] !== '' && $filtros['desde'] > $filtros['hasta']) {
            $tmp = $filtros['desde'];
            $filtros['desde'] = $filtros['hasta'];
            $filtros['hasta'] = $tmp;
        }

        $porPagina = 20;
        $pagina = max(1, (int)($_GET['pagina'] ?? 1));
        $total = $modelo->contarRegistros($filtros);
        $totalPaginas = max(1, (int)ceil($total / $porPagina));

        if ($pagina > $totalPaginas) {
            $pagina = $totalPaginas;
        }

        $offset = ($pagina - 1) * $porPagina;
        $registros = $modelo->obtenerRegistros($filtros, $porPagina, $offset);

        Logger::info('Registro de auditoria consultado', [
            'usuario' => $_SESSION['usuario_id'],
            'filtros' => array_filter($filtros)
        ]);

        $this->vista('auditoria/index', [
            'registros'    => $registros,
            'estadisticas' => $modelo->obtenerEstadisticas($filtros['desde'] ?: null, $filtros['hasta'] ?: null),
            'usuarios'     => $modelo->obtenerUsuarios(),
            'acciones'     => $modelo->obtenerAcciones(),
            'entidades'    => $modelo->obtenerEntidades(),
            'filtros'      => $filtros,
            'pagina'       => $pagina,
            'totalPaginas' => $totalPaginas,
            'total'        => $total,
            'porPagina'    => $porPagina,
            'offset'       => $offset,
            'flash'        => Flash::all()
        ]);
    }

    // Detalle de una accion registrada
    public function ver($id_auditoria) {
        $modelo = $this->modelo('Auditoria');
        $registro = $modelo->obtenerPorId((int)$id_auditoria);

        if (!$registro) {
            Flash::error('Registro de auditoría no encontrado.');
            $this->redirect(BASE_URL . '/auditoria');
        }

        Logger::info('Detalle de auditoria visualizado', [
            'id_auditoria' => $id_auditoria,
            'usuario'      => $_SESSION['usuario_id']
        ]);

        $this->vista('auditoria/ver', [
            'registro' => $registro,
            'flash'    => Flash::all()
        ]);
    }
}