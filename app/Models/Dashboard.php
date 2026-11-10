<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Dashboard {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    // Contar las citas del día actual
    public function obtenerCitasHoy() {
        // Usamos DATE(fecha_hora) para ignorar la hora y comparar solo el día
        $sql = "SELECT COUNT(*) as total FROM citas WHERE DATE(fecha_hora) = CURDATE()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Contar solo a los pacientes que son Estudiantes
    public function obtenerEstudiantesActivos() {
        $sql = "SELECT COUNT(*) as total FROM pacientes WHERE tipo_paciente = 'Estudiante'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Contar las sesiones registradas en el mes actual
    public function obtenerConsultasMes() {
        $sql = "SELECT COUNT(*) as total FROM sesiones_clinicas WHERE MONTH(fecha_registro) = MONTH(CURDATE()) AND YEAR(fecha_registro) = YEAR(CURDATE())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Obtener la lista de citas de hoy con sus datos vinculados
    public function obtenerProximasCitas() {
        // Extraemos fecha_hora pero le ponemos el alias 'hora_cita' para que la vista lo reconozca
        $sql = "SELECT c.id_cita, c.fecha_hora as hora_cita, c.motivo_cita, c.estado_cita,
                       p.nombres, p.apellidos, e.id_expediente
                FROM citas c
                INNER JOIN pacientes p ON c.id_paciente = p.id_paciente
                LEFT JOIN expedientes e ON p.id_paciente = e.id_paciente
                WHERE DATE(c.fecha_hora) = CURDATE()
                ORDER BY c.fecha_hora ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //
    // METRICAS INSTITUCIONALES (Panel de analisis general para la Direccion)
    // Todas las consultas son agregadas: no exponen datos clinicos individuales.
    //

    /**
     * Calcula el conjunto completo de indicadores y series del panel de metricas.
     *
     * @param string|null $desde Fecha inicial (Y-m-d) o null para sin limite
     * @param string|null $hasta Fecha final (Y-m-d) o null para sin limite
     */
    public function obtenerMetricas($desde = null, $hasta = null) {
        return [
            'pacientes'       => $this->metricaPacientes($desde),
            'expedientes'     => $this->metricaExpedientes($desde),
            'citas'           => $this->metricaCitas($desde, $hasta),
            'sesiones'        => $this->metricaSesiones($desde, $hasta),
            'informes'        => $this->metricaInformes($desde, $hasta),
            'series_mensual'  => $this->serieMensual(),
            'por_dia_semana'  => $this->citasPorDiaSemana($desde, $hasta),
            'por_tipo'        => $this->distribucionPorTipoPaciente(),
            'por_estado_caso' => $this->distribucionPorEstadoCaso(),
            'por_urgencia'    => $this->distribucionPorUrgencia($desde, $hasta)
        ];
    }

    // Indicadores de pacientes (acumulados)
    private function metricaPacientes($desde) {
        $sql = "SELECT
                    SUM(CASE WHEN estado_paciente = 'Activo' THEN 1 ELSE 0 END) AS activos,
                    SUM(CASE WHEN estado_paciente = 'Inactivo' THEN 1 ELSE 0 END) AS inactivos,
                    SUM(CASE WHEN tipo_paciente = 'Estudiante' THEN 1 ELSE 0 END) AS estudiantes,
                    SUM(CASE WHEN tipo_paciente = 'Empleado' THEN 1 ELSE 0 END) AS empleados,
                    COUNT(*) AS total
                FROM pacientes";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $base = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        return [
            'activos'     => (int)($base['activos'] ?? 0),
            'inactivos'   => (int)($base['inactivos'] ?? 0),
            'total'       => (int)($base['total'] ?? 0),
            'estudiantes' => (int)($base['estudiantes'] ?? 0),
            'empleados'   => (int)($base['empleados'] ?? 0)
        ];
    }

    // Indicadores de expedientes clinicos por estado
    private function metricaExpedientes($desde) {
        $sql = "SELECT
                    SUM(CASE WHEN estado_caso = 'Activo' THEN 1 ELSE 0 END) AS activos,
                    SUM(CASE WHEN estado_caso = 'Seguimiento' THEN 1 ELSE 0 END) AS seguimiento,
                    SUM(CASE WHEN estado_caso = 'Cerrado' THEN 1 ELSE 0 END) AS cerrados,
                    COUNT(*) AS total
                FROM expedientes";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $base = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $nuevos = 0;
        if ($desde) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM expedientes WHERE fecha_apertura >= :desde");
            $stmt->bindValue(':desde', $desde);
            $stmt->execute();
            $nuevos = (int)$stmt->fetchColumn();
        }

        return [
            'activos'     => (int)($base['activos'] ?? 0),
            'seguimiento' => (int)($base['seguimiento'] ?? 0),
            'cerrados'    => (int)($base['cerrados'] ?? 0),
            'total'       => (int)($base['total'] ?? 0),
            'nuevos'      => $nuevos
        ];
    }

    // Indicadores de la agenda de citas y tasas de cumplimiento
    private function metricaCitas($desde, $hasta) {
        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN estado_cita = 'Programada' THEN 1 ELSE 0 END) AS programadas,
                    SUM(CASE WHEN estado_cita = 'Completada' THEN 1 ELSE 0 END) AS completadas,
                    SUM(CASE WHEN estado_cita = 'Cancelada' THEN 1 ELSE 0 END) AS canceladas,
                    SUM(CASE WHEN estado_cita = 'Inasistencia' THEN 1 ELSE 0 END) AS inasistencias
                FROM citas WHERE 1=1";
        $params = [];
        if ($desde) {
            $sql .= " AND DATE(fecha_hora) >= :desde";
            $params[':desde'] = $desde;
        }
        if ($hasta) {
            $sql .= " AND DATE(fecha_hora) <= :hasta";
            $params[':hasta'] = $hasta;
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $clave => $valor) {
            $stmt->bindValue($clave, $valor);
        }
        $stmt->execute();
        $base = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $total = (int)($base['total'] ?? 0);
        $completadas = (int)($base['completadas'] ?? 0);
        $inasistencias = (int)($base['inasistencias'] ?? 0);

        return [
            'total'             => $total,
            'programadas'       => (int)($base['programadas'] ?? 0),
            'completadas'       => $completadas,
            'canceladas'        => (int)($base['canceladas'] ?? 0),
            'inasistencias'     => $inasistencias,
            'cumplimiento'      => $total > 0 ? round(($completadas / $total) * 100, 1) : 0,
            'tasa_inasistencia' => $total > 0 ? round(($inasistencias / $total) * 100, 1) : 0
        ];
    }

    // Indicadores de atencion clinica (sesiones)
    private function metricaSesiones($desde, $hasta) {
        $sql = "SELECT
                    COUNT(*) AS total,
                    COUNT(DISTINCT id_expediente) AS expedientes_atendidos,
                    SUM(CASE WHEN tipo_consulta = 'Seguimiento' THEN 1 ELSE 0 END) AS seguimiento,
                    SUM(CASE WHEN tipo_consulta <> 'Seguimiento' THEN 1 ELSE 0 END) AS primera_evaluacion
                FROM sesiones_clinicas WHERE 1=1";
        $params = [];
        if ($desde) {
            $sql .= " AND DATE(fecha_registro) >= :desde";
            $params[':desde'] = $desde;
        }
        if ($hasta) {
            $sql .= " AND DATE(fecha_registro) <= :hasta";
            $params[':hasta'] = $hasta;
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $clave => $valor) {
            $stmt->bindValue($clave, $valor);
        }
        $stmt->execute();
        $base = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $total = (int)($base['total'] ?? 0);
        $atendidos = (int)($base['expedientes_atendidos'] ?? 0);

        return [
            'total'              => $total,
            'expedientes'        => $atendidos,
            'seguimiento'        => (int)($base['seguimiento'] ?? 0),
            'primera_evaluacion' => (int)($base['primera_evaluacion'] ?? 0),
            'promedio'           => $atendidos > 0 ? round($total / $atendidos, 1) : 0
        ];
    }

    // Indicadores de informes tecnicos emitidos y nivel de urgencia
    private function metricaInformes($desde, $hasta) {
        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN nivel_urgencia = 'Bajo' THEN 1 ELSE 0 END) AS bajo,
                    SUM(CASE WHEN nivel_urgencia = 'Medio' THEN 1 ELSE 0 END) AS medio,
                    SUM(CASE WHEN nivel_urgencia = 'Alto' THEN 1 ELSE 0 END) AS alto
                FROM reportes WHERE 1=1";
        $params = [];
        if ($desde) {
            $sql .= " AND fecha_generacion >= :desde";
            $params[':desde'] = $desde;
        }
        if ($hasta) {
            $sql .= " AND fecha_generacion <= :hasta";
            $params[':hasta'] = $hasta;
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $clave => $valor) {
            $stmt->bindValue($clave, $valor);
        }
        $stmt->execute();
        $base = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $total = (int)($base['total'] ?? 0);
        $alto = (int)($base['alto'] ?? 0);

        return [
            'total'           => $total,
            'bajo'            => (int)($base['bajo'] ?? 0),
            'medio'           => (int)($base['medio'] ?? 0),
            'alto'            => $alto,
            'porcentaje_alto' => $total > 0 ? round(($alto / $total) * 100, 1) : 0
        ];
    }

    // Serie mensual de citas y sesiones (ultimos 12 meses) para el grafico de evolucion
    private function serieMensual($meses = 12) {
        $sql = "SELECT
                    DATE_FORMAT(fecha_hora, '%Y-%m') AS periodo,
                    COUNT(*) AS total
                FROM citas
                WHERE fecha_hora >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY periodo";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $citas = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $citas[$fila['periodo']] = (int)$fila['total'];
        }

        $stmt = $this->db->prepare("SELECT
                                        DATE_FORMAT(fecha_registro, '%Y-%m') AS periodo,
                                        COUNT(*) AS total
                                    FROM sesiones_clinicas
                                    WHERE fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                                    GROUP BY periodo");
        $stmt->execute();
        $sesiones = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $sesiones[$fila['periodo']] = (int)$fila['total'];
        }

        $serie = [];
        for ($i = $meses - 1; $i >= 0; $i--) {
            $periodo = date('Y-m', strtotime("-{$i} months"));
            $serie[] = [
                'periodo'  => $periodo,
                'etiqueta' => date('M y', strtotime($periodo . '-01')),
                'citas'    => $citas[$periodo] ?? 0,
                'sesiones' => $sesiones[$periodo] ?? 0
            ];
        }
        return $serie;
    }

    // Distribucion de citas por dia de la semana (demanda de atencion)
    private function citasPorDiaSemana($desde, $hasta) {
        $sql = "SELECT
                    DAYNAME(fecha_hora) AS dia,
                    COUNT(*) AS total
                FROM citas WHERE 1=1";
        $params = [];
        if ($desde) {
            $sql .= " AND DATE(fecha_hora) >= :desde";
            $params[':desde'] = $desde;
        }
        if ($hasta) {
            $sql .= " AND DATE(fecha_hora) <= :hasta";
            $params[':hasta'] = $hasta;
        }
        $sql .= " GROUP BY dia";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $clave => $valor) {
            $stmt->bindValue($clave, $valor);
        }
        $stmt->execute();
        $obtenido = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $obtenido[$fila['dia']] = (int)$fila['total'];
        }

        $dias = [
            'Monday'    => 'Lunes',
            'Tuesday'   => 'Martes',
            'Wednesday' => 'Miercoles',
            'Thursday'  => 'Jueves',
            'Friday'    => 'Viernes',
            'Saturday'  => 'Sabado',
            'Sunday'    => 'Domingo'
        ];
        $distribucion = [];
        foreach ($dias as $clave => $etiqueta) {
            $distribucion[] = [
                'dia'   => $etiqueta,
                'total' => $obtenido[$clave] ?? 0
            ];
        }
        return $distribucion;
    }

    // Distribucion de pacientes por tipo
    private function distribucionPorTipoPaciente() {
        $sql = "SELECT tipo_paciente AS etiqueta, COUNT(*) AS total
                FROM pacientes
                WHERE estado_paciente = 'Activo'
                GROUP BY tipo_paciente
                ORDER BY total DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($filas)) {
            return [
                ['etiqueta' => 'Estudiante', 'total' => 0],
                ['etiqueta' => 'Empleado', 'total' => 0]
            ];
        }
        return $filas;
    }

    // Distribucion de expedientes por estado del caso
    private function distribucionPorEstadoCaso() {
        $sql = "SELECT estado_caso AS etiqueta, COUNT(*) AS total
                FROM expedientes
                GROUP BY estado_caso
                ORDER BY total DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($filas)) {
            return [
                ['etiqueta' => 'Activo', 'total' => 0],
                ['etiqueta' => 'Seguimiento', 'total' => 0],
                ['etiqueta' => 'Cerrado', 'total' => 0]
            ];
        }
        return $filas;
    }

    // Distribucion de informes por nivel de urgencia
    private function distribucionPorUrgencia($desde, $hasta) {
        $sql = "SELECT nivel_urgencia AS etiqueta, COUNT(*) AS total
                FROM reportes WHERE 1=1";
        $params = [];
        if ($desde) {
            $sql .= " AND fecha_generacion >= :desde";
            $params[':desde'] = $desde;
        }
        if ($hasta) {
            $sql .= " AND fecha_generacion <= :hasta";
            $params[':hasta'] = $hasta;
        }
        $sql .= " GROUP BY nivel_urgencia";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $clave => $valor) {
            $stmt->bindValue($clave, $valor);
        }
        $stmt->execute();
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $mapa = ['Bajo' => 0, 'Medio' => 0, 'Alto' => 0];
        foreach ($filas as $fila) {
            if (isset($mapa[$fila['etiqueta']])) {
                $mapa[$fila['etiqueta']] = (int)$fila['total'];
            }
        }

        $distribucion = [];
        foreach ($mapa as $etiqueta => $total) {
            $distribucion[] = ['etiqueta' => $etiqueta, 'total' => $total];
        }
        return $distribucion;
    }
}