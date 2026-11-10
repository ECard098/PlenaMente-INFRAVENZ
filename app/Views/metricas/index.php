<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../Libraries/Graficos.php';

use App\Libraries\Graficos;

$p = $metricas['pacientes'];
$e = $metricas['expedientes'];
$c = $metricas['citas'];
$s = $metricas['sesiones'];
$i = $metricas['informes'];

// Colores por categoria
$coloresEstadoCaso = ['Activo' => '#7E57C2', 'Seguimiento' => '#17A2B8', 'Cerrado' => '#9E9E9E'];
$coloresUrgencia   = ['Bajo' => '#28A745', 'Medio' => '#FFC107', 'Alto' => '#DC3545'];
$coloresTipo       = ['Estudiante' => '#42A5F5', 'Empleado' => '#26A69A'];

/**
 * Devuelve el color asociado a una etiqueta dentro de un mapa.
 */
function colorDe($mapa, $etiqueta, $porDefecto = '#7E57C2') {
    return $mapa[$etiqueta] ?? $porDefecto;
}

/**
 * Formatea un porcentaje con un signo explicito.
 */
function formatoPorcentaje($valor) {
    return number_format((float)$valor, 1) . '%';
}
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/metricas.css">

<?php if (!empty($flash)): ?>
    <?php foreach ($flash as $tipo => $mensajes): ?>
        <?php foreach ($mensajes as $mensaje): ?>
            <div style="padding: 12px; background-color: <?php echo $tipo == 'success' ? '#d4edda' : ($tipo == 'warning' ? '#fff3cd' : '#f8d7da'); ?>; color: <?php echo $tipo == 'success' ? '#155724' : ($tipo == 'warning' ? '#856404' : '#721c24'); ?>; border-radius: 4px; margin-bottom: 16px; font-weight: bold;">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Encabezado con filtro de periodo y control de actualizacion -->
<div class="metricas-encabezado">
    <div>
        <h2 style="color: var(--violeta-principal); margin: 0 0 4px 0;">Dashboard de Métricas</h2>
        <p style="margin: 0; color: #666; font-size: 14px;">
            Análisis general de la atención psicológica. Período: <strong><?php echo htmlspecialchars($etiquetaPeriodo); ?></strong>
            <?php if ($desde): ?>
                (<?php echo date('d/m/Y', strtotime($desde)); ?> - <?php echo date('d/m/Y', strtotime($hasta)); ?>)
            <?php endif; ?>
        </p>
    </div>

    <div class="metricas-controles">
        <form method="GET" action="<?php echo BASE_URL; ?>/metricas" class="metricas-periodo">
            <?php foreach ($periodos as $clave => $etiqueta): ?>
                <button type="submit" name="periodo" value="<?php echo $clave; ?>" class="metricas-chip <?php echo ($periodo === $clave) ? 'activo' : ''; ?>">
                    <?php echo htmlspecialchars($etiqueta); ?>
                </button>
            <?php endforeach; ?>
        </form>

        <div class="metricas-actualizacion">
            <span class="metricas-timestamp" id="metricas-timestamp">
                Actualizado: <?php echo htmlspecialchars($generado); ?>
            </span>
            <span class="metricas-cronometro" id="metricas-cronometro">Actualiza en 60 s</span>
            <label class="metricas-toggle" title="Actualiza los indicadores automáticamente cada 60 segundos">
                <input type="checkbox" id="metricas-auto" checked>
                <span>Auto</span>
            </label>
            <button type="button" class="btn btn-secondary metricas-refrescar" id="metricas-refrescar">Actualizar</button>
        </div>
    </div>
</div>

<!-- Tarjetas de indicadores -->
<div class="metricas-kpis">
    <div class="metrica-card">
        <div class="metrica-label">Pacientes activos</div>
        <div class="metrica-valor"><?php echo number_format($p['activos']); ?></div>
        <div class="metrica-detalle">
            <?php echo number_format($p['estudiantes']); ?> estudiantes · <?php echo number_format($p['empleados']); ?> empleados
            <?php if ($p['inactivos'] > 0): ?>
                <br><span class="metrica-nota"><?php echo number_format($p['inactivos']); ?> inactivos</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="metrica-card metrica-card-azul">
        <div class="metrica-label">Expedientes en atención</div>
        <div class="metrica-valor"><?php echo number_format($e['activos'] + $e['seguimiento']); ?></div>
        <div class="metrica-detalle">
            <?php echo number_format($e['seguimiento']); ?> en seguimiento · <?php echo number_format($e['cerrados']); ?> cerrados
            <?php if ($e['nuevos'] > 0): ?>
                <br><span class="metrica-nota"><?php echo number_format($e['nuevos']); ?> abiertos en el período</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="metrica-card metrica-card-verde">
        <div class="metrica-label">Citas del período</div>
        <div class="metrica-valor"><?php echo number_format($c['total']); ?></div>
        <div class="metrica-detalle">
            <?php echo number_format($c['completadas']); ?> completadas · <?php echo number_format($c['programadas']); ?> programadas
        </div>
    </div>

    <div class="metrica-card metrica-card-verde">
        <div class="metrica-label">Cumplimiento de citas</div>
        <div class="metrica-valor"><?php echo formatoPorcentaje($c['cumplimiento']); ?></div>
        <div class="metrica-detalle">
            <?php echo number_format($c['canceladas']); ?> canceladas · <?php echo number_format($c['inasistencias']); ?> inasistencias
        </div>
    </div>

    <div class="metrica-card metrica-card-ambar">
        <div class="metrica-label">Tasa de inasistencia</div>
        <div class="metrica-valor"><?php echo formatoPorcentaje($c['tasa_inasistencia']); ?></div>
        <div class="metrica-detalle">Sobre <?php echo number_format($c['total']); ?> citas del período</div>
    </div>

    <div class="metrica-card metrica-card-ambar">
        <div class="metrica-label">Sesiones clínicas</div>
        <div class="metrica-valor"><?php echo number_format($s['total']); ?></div>
        <div class="metrica-detalle">
            <?php echo number_format($s['expedientes']); ?> expedientes atendidos · promedio <?php echo $s['promedio']; ?> sesiones
        </div>
    </div>

    <div class="metrica-card metrica-card-rojo">
        <div class="metrica-label">Informes emitidos</div>
        <div class="metrica-valor"><?php echo number_format($i['total']); ?></div>
        <div class="metrica-detalle">
            <?php echo number_format($i['alto']); ?> de urgencia alta (<?php echo formatoPorcentaje($i['porcentaje_alto']); ?>)
        </div>
    </div>

    <div class="metrica-card metrica-card-rojo">
        <div class="metrica-label">Casos de alta urgencia</div>
        <div class="metrica-valor"><?php echo number_format($i['alto']); ?></div>
        <div class="metrica-detalle">
            <?php echo number_format($i['medio']); ?> medios · <?php echo number_format($i['bajo']); ?> bajos
        </div>
    </div>
</div>

<!-- Graficos -->
<div class="metricas-graficos">
    <div class="card metricas-panel metricas-panel-ancho">
        <div class="card-header">Evolución mensual · Citas y Sesiones (últimos 12 meses)</div>
        <?php
        $etiquetas = array_column($metricas['series_mensual'], 'etiqueta');
        $series = [
            ['nombre' => 'Citas', 'color' => '#7E57C2', 'valores' => array_column($metricas['series_mensual'], 'citas')],
            ['nombre' => 'Sesiones', 'color' => '#26A69A', 'valores' => array_column($metricas['series_mensual'], 'sesiones')]
        ];
        echo Graficos::barrasVerticales($etiquetas, $series, ['titulo' => 'Evolucion mensual de citas y sesiones']);
        ?>
        <div class="metricas-leyenda">
            <span><i style="background:#7E57C2"></i> Citas</span>
            <span><i style="background:#26A69A"></i> Sesiones</span>
        </div>
    </div>

    <div class="card metricas-panel">
        <div class="card-header">Citas por día de la semana</div>
        <?php
        $itemsDia = [];
        foreach ($metricas['por_dia_semana'] as $dia) {
            $itemsDia[] = ['etiqueta' => $dia['dia'], 'total' => $dia['total'], 'color' => '#42A5F5'];
        }
        echo Graficos::barrasHorizontales($itemsDia, ['titulo' => 'Citas por dia de la semana', 'margen_izq' => 84, 'alto' => 200]);
        ?>
    </div>

    <div class="card metricas-panel">
        <div class="card-header">Estado de los casos</div>
        <?php
        $itemsCaso = [];
        foreach ($metricas['por_estado_caso'] as $caso) {
            $itemsCaso[] = ['etiqueta' => $caso['etiqueta'], 'total' => $caso['total'], 'color' => colorDe($coloresEstadoCaso, $caso['etiqueta'])];
        }
        echo Graficos::barrasHorizontales($itemsCaso, ['titulo' => 'Expedientes por estado del caso', 'margen_izq' => 100, 'alto' => 130]);
        ?>
    </div>

    <div class="card metricas-panel">
        <div class="card-header">Nivel de urgencia de los informes</div>
        <?php
        $itemsUrgencia = [];
        foreach ($metricas['por_urgencia'] as $urgencia) {
            $itemsUrgencia[] = ['etiqueta' => $urgencia['etiqueta'], 'total' => $urgencia['total'], 'color' => colorDe($coloresUrgencia, $urgencia['etiqueta'])];
        }
        echo Graficos::barrasHorizontales($itemsUrgencia, ['titulo' => 'Informes por nivel de urgencia', 'margen_izq' => 100, 'alto' => 130]);
        ?>
    </div>

    <div class="card metricas-panel">
        <div class="card-header">Pacientes activos por tipo</div>
        <?php
        $itemsTipo = [];
        foreach ($metricas['por_tipo'] as $tipo) {
            $itemsTipo[] = ['etiqueta' => $tipo['etiqueta'], 'total' => $tipo['total'], 'color' => colorDe($coloresTipo, $tipo['etiqueta'], '#616161')];
        }
        echo Graficos::barrasHorizontales($itemsTipo, ['titulo' => 'Pacientes por tipo', 'margen_izq' => 100, 'alto' => 100]);
        ?>
    </div>
</div>

<!-- Tabla resumen de la agenda -->
<div class="card">
    <div class="card-header">Resumen de la agenda en el período</div>
    <table class="table">
        <thead>
            <tr>
                <th>Estado de la cita</th>
                <th style="text-align:center;">Cantidad</th>
                <th style="text-align:center;">Participación</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $estadosCita = [
                ['Programada', $c['programadas'], '#007bff'],
                ['Completada', $c['completadas'], '#28a745'],
                ['Cancelada', $c['canceladas'], '#dc3545'],
                ['Inasistencia', $c['inasistencias'], '#fd7e14']
            ];
            foreach ($estadosCita as $estado):
                $porcentaje = $c['total'] > 0 ? round(($estado[1] / $c['total']) * 100, 1) : 0;
            ?>
                <tr>
                    <td><span class="badge" style="background-color: <?php echo $estado[2]; ?>; color: white; padding: 4px 10px; border-radius: 12px; font-size: 12px;"><?php echo $estado[0]; ?></span></td>
                    <td style="text-align:center;"><strong><?php echo number_format($estado[1]); ?></strong></td>
                    <td style="text-align:center;">
                        <div style="display: flex; align-items:center; gap:8px; justify-content:flex-end;">
                            <div style="flex: 1; max-width: 180px; height:8px; background:#eee; border-radius:4px; overflow:hidden;">
                                <div style="width: <?php echo $porcentaje; ?>%; height:100%; background:<?php echo $estado[2]; ?>;"></div>
                            </div>
                            <span style="min-width:60px; text-align:right;"><?php echo formatoPorcentaje($porcentaje); ?></span>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<p style="text-align:center; font-size:12px; color:#888; margin-top:20px;">
    Las métricas son agregadas y no exponen información clínica individual.
    Datos generados el <?php echo htmlspecialchars($generado); ?>.
</p>

<script>
(function () {
    var intervalo = 60000; // 60 segundos
    var contador = intervalo / 1000;
    var cajaCronometro = document.getElementById('metricas-cronometro');
    var marcaTiempo = document.getElementById('metricas-timestamp');
    var interruptor = document.getElementById('metricas-auto');
    var boton = document.getElementById('metricas-refrescar');
    var parametros = new URLSearchParams(window.location.search);
    var url = window.location.pathname + (parametros.toString() ? '?' + parametros.toString() : '');

    function recargar() {
        marcaTiempo.textContent = 'Actualizando...';
        window.location.reload();
    }

    if (boton) {
        boton.addEventListener('click', recargar);
    }

    window.setInterval(function () {
        if (!interruptor || !interruptor.checked) {
            return;
        }
        contador--;
        if (cajaCronometro) {
            cajaCronometro.textContent = 'Actualiza en ' + contador + 's';
        }
        if (contador <= 0) {
            recargar();
        }
    }, 1000);

    var enPausa = false;
    document.addEventListener('mouseover', function () { enPausa = true; });
    document.addEventListener('mouseout', function () { enPausa = false; });

    window.setInterval(function () {
        if (enPausa && cajaCronometro) {
            cajaCronometro.textContent = 'Actualización en pausa';
            contador = intervalo / 1000;
        }
    }, 1000);

    var preferencia = localStorage.getItem('metricas_auto');
    if (preferencia === '0' && interruptor) {
        interruptor.checked = false;
    }
    if (interruptor) {
        interruptor.addEventListener('change', function () {
            localStorage.setItem('metricas_auto', interruptor.checked ? '1' : '0');
            contador = intervalo / 1000;
        });
    }
})();
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>