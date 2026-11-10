<?php
namespace App\Core;

/**
 * Metricas: construccion del panel de metricas institucionales.
 * Logica compartida por el dashboard de la Direccion (rol 2) y del
 * Administrador (rol 3). Para el Psicologo (rol 1) el dashboard sigue siendo
 * la vista operativa (agenda del dia).
 */
class Metricas {

    // Periodos disponibles para el filtro (clave => etiqueta)
    public static function periodos() {
        return [
            'mes'     => 'Este mes',
            '3meses'  => 'Ultimos 3 meses',
            '6meses'  => 'Ultimos 6 meses',
            '12meses' => 'Ultimos 12 meses',
            'todo'    => 'Todo el historial'
        ];
    }

    /**
     * Resuelve el rango de fechas del periodo seleccionado.
     * Devuelve [desde, hasta, etiqueta].
     */
    public static function resolverPeriodo($periodo) {
        $periodos = self::periodos();
        if (!isset($periodos[$periodo])) {
            $periodo = '6meses';
        }

        $hoy = date('Y-m-d');
        switch ($periodo) {
            case 'mes':
                $desde = date('Y-m-01');
                break;
            case '3meses':
                $desde = date('Y-m-d', strtotime('-3 months'));
                break;
            case '12meses':
                $desde = date('Y-m-d', strtotime('-12 months'));
                break;
            case 'todo':
                $desde = null;
                break;
            case '6meses':
            default:
                $desde = date('Y-m-d', strtotime('-6 months'));
                break;
        }

        return [$desde, $hoy, $periodos[$periodo]];
    }

    /**
     * Construye todos los datos que necesita la vista del panel de metricas.
     *
     * @param object $modeloDashboard Instancia de App\Models\Dashboard
     * @param string $periodo Clave del periodo seleccionado
     */
    public static function construir($modeloDashboard, $periodo) {
        list($desde, $hasta, $etiqueta) = self::resolverPeriodo($periodo);

        return [
            'metricas'         => $modeloDashboard->obtenerMetricas($desde, $hasta),
            'periodo'          => $periodo,
            'periodos'         => self::periodos(),
            'etiquetaPeriodo'  => $etiqueta,
            'desde'            => $desde,
            'hasta'            => $hasta,
            'generado'         => date('d/m/Y H:i')
        ];
    }
}