<?php
namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * Auditoria: registro y consulta de las acciones criticas del sistema.
 * Las acciones se registran en la tabla 'auditoria_acciones' (ver Controller::auditar).
 */
class Auditoria {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    // Registrar una acción en la auditoría del sistema
    public function registrar($id_usuario, $accion, $entidad, $id_entidad, $descripcion) {
        $sql = "INSERT INTO auditoria_acciones (id_usuario, accion, entidad, id_entidad, descripcion, fecha)
                VALUES (:id_usuario, :accion, :entidad, :id_entidad, :descripcion, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_usuario', $id_usuario ?: null, $id_usuario ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':accion', $accion);
        $stmt->bindValue(':entidad', $entidad);
        $stmt->bindValue(':id_entidad', $id_entidad ?: null, $id_entidad ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':descripcion', $descripcion);
        return $stmt->execute();
    }

    // Últimas acciones registradas (para consulta)
    public function obtenerRecientes($limite = 50) {
        $limite = (int)$limite;
        $sql = "SELECT a.*, u.nombre_completo
                FROM auditoria_acciones a
                LEFT JOIN usuarios u ON a.id_usuario = u.id_usuario
                ORDER BY a.fecha DESC, a.id_auditoria DESC
                LIMIT {$limite}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Construye las condiciones de filtrado y sus parametros.
     *
     * @param array $filtros [id_usuario, accion, entidad, desde, hasta, busqueda]
     * @return array [sql, params]
     */
    private function construirFiltros($filtros) {
        $cond = [];
        $params = [];

        if (!empty($filtros['id_usuario'])) {
            $cond[] = "a.id_usuario = :id_usuario";
            $params[':id_usuario'] = (int)$filtros['id_usuario'];
        }

        if (!empty($filtros['accion'])) {
            $cond[] = "a.accion = :accion";
            $params[':accion'] = $filtros['accion'];
        }

        if (!empty($filtros['entidad'])) {
            $cond[] = "a.entidad = :entidad";
            $params[':entidad'] = $filtros['entidad'];
        }

        if (!empty($filtros['desde'])) {
            $cond[] = "a.fecha >= :desde";
            $params[':desde'] = $filtros['desde'] . ' 00:00:00';
        }

        if (!empty($filtros['hasta'])) {
            $cond[] = "a.fecha <= :hasta";
            $params[':hasta'] = $filtros['hasta'] . ' 23:59:59';
        }

        if (!empty($filtros['busqueda'])) {
            $cond[] = "(a.descripcion LIKE :busqueda OR u.nombre_completo LIKE :busqueda2)";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
            $params[':busqueda2'] = '%' . $filtros['busqueda'] . '%';
        }

        $sql = empty($cond) ? '' : ' WHERE ' . implode(' AND ', $cond);
        return [$sql, $params];
    }

    /**
     * Obtiene los registros de auditoria que cumplen los filtros, paginados.
     */
    public function obtenerRegistros($filtros = [], $limite = 20, $offset = 0) {
        list($where, $params) = $this->construirFiltros($filtros);

        $sql = "SELECT a.*, u.nombre_completo, u.correo, r.nombre_rol
                FROM auditoria_acciones a
                LEFT JOIN usuarios u ON a.id_usuario = u.id_usuario
                LEFT JOIN roles r ON u.id_rol = r.id_rol" . $where . "
                ORDER BY a.fecha DESC, a.id_auditoria DESC
                LIMIT " . (int)$limite . " OFFSET " . (int)$offset;

        $stmt = $this->db->prepare($sql);
        foreach ($params as $clave => $valor) {
            $stmt->bindValue($clave, $valor);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Total de registros que cumplen los filtros (para la paginacion)
    public function contarRegistros($filtros = []) {
        list($where, $params) = $this->construirFiltros($filtros);

        $sql = "SELECT COUNT(*) FROM auditoria_acciones a
                LEFT JOIN usuarios u ON a.id_usuario = u.id_usuario" . $where;

        $stmt = $this->db->prepare($sql);
        foreach ($params as $clave => $valor) {
            $stmt->bindValue($clave, $valor);
        }
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    // Un registro de auditoria especifico, con los datos de su autor
    public function obtenerPorId($id_auditoria) {
        $sql = "SELECT a.*, u.nombre_completo, u.correo, r.nombre_rol
                FROM auditoria_acciones a
                LEFT JOIN usuarios u ON a.id_usuario = u.id_usuario
                LEFT JOIN roles r ON u.id_rol = r.id_rol
                WHERE a.id_auditoria = :id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', (int)$id_auditoria, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Usuarios que han registrado acciones (para el filtro por usuario)
    public function obtenerUsuarios() {
        $sql = "SELECT DISTINCT u.id_usuario, u.nombre_completo, u.correo, r.nombre_rol
                FROM auditoria_acciones a
                INNER JOIN usuarios u ON a.id_usuario = u.id_usuario
                LEFT JOIN roles r ON u.id_rol = r.id_rol
                ORDER BY u.nombre_completo ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Acciones disponibles en el registro (para el filtro por tipo de accion)
    public function obtenerAcciones() {
        $stmt = $this->db->query("SELECT DISTINCT accion FROM auditoria_acciones ORDER BY accion ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Entidades registradas (para el filtro por tipo de entidad)
    public function obtenerEntidades() {
        $stmt = $this->db->query("SELECT DISTINCT entidad FROM auditoria_acciones ORDER BY entidad ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Resumen estadistico de la auditoria: total, reparto por accion y por usuario.
     */
    public function obtenerEstadisticas($desde = null, $hasta = null) {
        $cond = [];
        $params = [];

        if ($desde) {
            $cond[] = "fecha >= :desde";
            $params[':desde'] = $desde . ' 00:00:00';
        }

        if ($hasta) {
            $cond[] = "fecha <= :hasta";
            $params[':hasta'] = $hasta . ' 23:59:59';
        }

        $where = empty($cond) ? '' : ' WHERE ' . implode(' AND ', $cond);

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM auditoria_acciones" . $where);
        foreach ($params as $clave => $valor) {
            $stmt->bindValue($clave, $valor);
        }
        $stmt->execute();
        $total = (int)$stmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT accion, COUNT(*) AS total FROM auditoria_acciones" . $where . " GROUP BY accion ORDER BY total DESC");
        foreach ($params as $clave => $valor) {
            $stmt->bindValue($clave, $valor);
        }
        $stmt->execute();
        $porAccion = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("SELECT COALESCE(u.nombre_completo, 'Usuario eliminado') AS nombre, COUNT(*) AS total
                                    FROM auditoria_acciones a
                                    LEFT JOIN usuarios u ON a.id_usuario = u.id_usuario" . $where . "
                                    GROUP BY a.id_usuario, u.nombre_completo
                                    ORDER BY total DESC LIMIT 5");
        foreach ($params as $clave => $valor) {
            $stmt->bindValue($clave, $valor);
        }
        $stmt->execute();
        $porUsuario = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("SELECT MAX(fecha) FROM auditoria_acciones" . $where);
        foreach ($params as $clave => $valor) {
            $stmt->bindValue($clave, $valor);
        }
        $stmt->execute();
        $ultima = $stmt->fetchColumn();

        return [
            'total'       => $total,
            'por_accion'  => $porAccion,
            'por_usuario' => $porUsuario,
            'ultima'      => $ultima
        ];
    }
}