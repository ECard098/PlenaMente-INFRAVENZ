<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php
// Colores por tipo de accion (segun el riesgo de la operacion)
$colorAccion = [
    'CREAR'      => ['#28a745', '#ffffff'],
    'ACTUALIZAR' => ['#17a2b8', '#ffffff'],
    'ELIMINAR'   => ['#dc3545', '#ffffff'],
    'DESACTIVAR' => ['#fd7e14', '#ffffff'],
    'REACTIVAR'  => ['#6f42c1', '#ffffff'],
    'RESTAURAR'  => ['#0d6efd', '#ffffff'],
    'REVERTIR'   => ['#6c757d', '#ffffff']
];
$iconoAccion = [
    'CREAR' => '➕', 'ACTUALIZAR' => '✏', 'ELIMINAR' => '🗑', 'DESACTIVAR' => '⛔',
    'REACTIVAR' => '♻', 'RESTAURAR' => '♻', 'REVERTIR' => '↩'
];
$colorEntidad = [
    'paciente'   => '#42a5f5', 'cita' => '#9575cd', 'expediente' => '#26a69a',
    'sesion'     => '#ffa726', 'informe' => '#ec407a', 'usuario' => '#7e57c2',
    'respaldo'   => '#5c6bc0', 'asistencia' => '#8d6e63'
];
?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; gap: 12px; flex-wrap: wrap;">
    <div>
        <h2 style="color: var(--violeta-principal); margin: 0 0 4px 0;">📋 Registro de Auditoría</h2>
        <p style="margin: 0; color: #666; font-size: 14px;">Acciones críticas realizadas en el sistema (creaciones, modificaciones y eliminaciones).</p>
    </div>
</div>

<?php if (!empty($flash)): ?>
    <?php foreach ($flash as $tipo => $mensajes): ?>
        <?php foreach ($mensajes as $mensaje): ?>
            <div style="padding: 12px; background-color: <?php echo $tipo == 'success' ? '#d4edda' : ($tipo == 'warning' ? '#fff3cd' : '#f8d7da'); ?>; color: <?php echo $tipo == 'success' ? '#155724' : ($tipo == 'warning' ? '#856404' : '#721c24'); ?>; border-radius: 4px; margin-bottom: 16px; font-weight: bold;">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Resumen -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));">
    <div class="stat-card">
        <div class="stat-label">Acciones registradas</div>
        <div class="stat-number"><?php echo number_format($estadisticas['total']); ?></div>
        <small style="opacity:.9; font-size: 12px;">
            <?php echo $estadisticas['ultima'] ? 'Última: ' . date('d/m/Y H:i', strtotime($estadisticas['ultima'])) : 'Sin registros'; ?>
        </small>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, var(--azul-claro), var(--azul-medio));">
        <div class="stat-label">Tipos de acción</div>
        <div class="stat-number"><?php echo count($estadisticas['por_accion']); ?></div>
        <small style="opacity: .9; font-size: 12px;"><?php echo htmlspecialchars(implode(', ', array_slice(array_column($estadisticas['por_accion'], 'accion'), 0, 4))); ?></small>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, var(--verde-menta), var(--verde-menta-claro));">
        <div class="stat-label">Usuarios con registro</div>
        <div class="stat-number"><?php echo count($usuarios); ?></div>
        <small style="opacity: .9; font-size: 12px;">Usuarios distintos en el periodo</small>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, #FFB300, #FFCA28); color: #4E3A00;">
        <div class="stat-label">Acciones de eliminación</div>
        <div class="stat-number">
            <?php
            $eliminaciones = 0;
            foreach ($estadisticas['por_accion'] as $fila) {
                if (in_array($fila['accion'], ['ELIMINAR', 'DESACTIVAR'])) {
                    $eliminaciones += (int)$fila['total'];
                }
            }
            echo number_format($eliminaciones);
            ?>
        </div>
        <small style="opacity: .9; font-size: 12px;">Eliminaciones y desactivaciones</small>
    </div>
</div>

<!-- Filtros -->
<div class="card" style="padding: 16px; margin-bottom: 20px;">
    <form method="GET" action="<?php echo BASE_URL; ?>/auditoria" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px;">
        <div style="flex: 1; min-width: 190px;">
            <label for="buscar" style="font-size: 13px; font-weight: bold; display: block; margin-bottom: 4px;">Buscar en la descripción</label>
            <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Ej. Paciente, Juan Pérez..." value="<?php echo htmlspecialchars($filtros['busqueda']); ?>" style="width: 100%; padding: 8px;">
        </div>
        <div style="min-width: 190px;">
            <label for="usuario" style="font-size: 13px; font-weight: bold; display: block; margin-bottom: 4px;">Usuario</label>
            <select id="usuario" name="usuario" class="form-control" style="width: 100%; padding: 8px;">
                <option value="">Todos los usuarios</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?php echo (int)$usuario['id_usuario']; ?>" <?php echo ($filtros['id_usuario'] === (int)$usuario['id_usuario']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($usuario['nombre_completo']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="min-width: 140px;">
            <label for="accion" style="font-size: 13px; font-weight: bold; display: block; margin-bottom: 4px;">Acción</label>
            <select id="accion" name="accion" class="form-control" style="width: 100%; padding: 8px;">
                <option value="">Todas</option>
                <?php foreach ($acciones as $accion): ?>
                    <option value="<?php echo htmlspecialchars($accion); ?>" <?php echo ($filtros['accion'] === $accion) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($accion); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="min-width: 140px;">
            <label for="entidad" style="font-size: 13px; font-weight: bold; display: block; margin-bottom: 4px;">Entidad</label>
            <select id="entidad" name="entidad" class="form-control" style="width: 100%; padding: 8px;">
                <option value="">Todas</option>
                <?php foreach ($entidades as $entidad): ?>
                    <option value="<?php echo htmlspecialchars($entidad); ?>" <?php echo ($filtros['entidad'] === $entidad) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($entidad); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="desde" style="font-size: 13px; font-weight: bold; display: block; margin-bottom: 4px;">Desde</label>
            <input type="date" id="desde" name="desde" class="form-control" value="<?php echo htmlspecialchars($filtros['desde']); ?>" style="padding: 8px;">
        </div>
        <div>
            <label for="hasta" style="font-size: 13px; font-weight: bold; display: block; margin-bottom: 4px;">Hasta</label>
            <input type="date" id="hasta" name="hasta" class="form-control" value="<?php echo htmlspecialchars($filtros['hasta']); ?>" style="padding: 8px;">
        </div>
        <button type="submit" class="btn btn-primary" style="background-color: var(--violeta-principal); border: none; color: white; padding: 9px 16px; border-radius: 4px; cursor: pointer;">
            Filtrar
        </button>
        <a href="<?php echo BASE_URL; ?>/auditoria" class="btn btn-secondary" style="padding: 9px 16px; text-decoration: none;">Limpiar</a>
    </form>
</div>

<!-- Listado -->
<div class="card">
    <div class="card-header">Acciones registradas (<?php echo number_format($total); ?>)</div>
    <?php if (empty($registros)): ?>
        <p style="text-align: center; color: #666; margin: 24px 0;">
            No hay registros de auditoría que coincidan con los filtros aplicados.
        </p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha y hora</th>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Entidad</th>
                    <th>Descripción</th>
                    <th style="text-align: center;">Detalle</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $registro): ?>
                    <?php
                    $color = $colorAccion[$registro['accion']] ?? ['#6c757d', '#ffffff'];
                    $colorEnt = $colorEntidad[$registro['entidad']] ?? '#9e9e9e';
                    $icono = $iconoAccion[$registro['accion']] ?? '';
                    ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="white-space: nowrap;">
                            <strong><?php echo date('d/m/Y', strtotime($registro['fecha'])); ?></strong><br>
                            <small style="color: #777;"><?php echo date('H:i:s', strtotime($registro['fecha'])); ?></small>
                        </td>
                        <td>
                            <?php if (!empty($registro['nombre_completo'])): ?>
                                <strong><?php echo htmlspecialchars($registro['nombre_completo']); ?></strong><br>
                                <small style="color: #888;"><?php echo htmlspecialchars($registro['nombre_rol'] ?? ''); ?></small>
                            <?php else: ?>
                                <span style="color: #999; font-style: italic;">Usuario eliminado</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; background: <?php echo $color[0]; ?>; color: <?php echo $color[1]; ?>;">
                                <?php echo $icono; ?> <?php echo htmlspecialchars($registro['accion']); ?>
                            </span>
                        </td>
                        <td>
                            <span style="display: inline-block; padding: 3px 9px; border-radius: 4px; font-size: 12px; background: <?php echo $colorEnt; ?>22; color: <?php echo $colorEnt; ?>; border: 1px solid <?php echo $colorEnt; ?>55;">
                                <?php echo htmlspecialchars($registro['entidad']); ?>
                                <?php if (!empty($registro['id_entidad'])): ?>
                                    #<?php echo (int)$registro['id_entidad']; ?>
                                <?php endif; ?>
                            </span>
                        </td>
                        <td style="font-size: 13px; color: #555; max-width: 380px;"><?php echo htmlspecialchars($registro['descripcion']); ?></td>
                        <td style="text-align: center;">
                            <a href="<?php echo BASE_URL; ?>/auditoria/ver/<?php echo (int)$registro['id_auditoria']; ?>" class="btn btn-primary" style="background-color: var(--violeta-principal); border: none; padding: 6px 12px; font-size: 12px; text-decoration: none; color: white; border-radius: 4px;">
                                Ver
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Paginacion -->
<?php if ($total > 0): ?>
    <?php
    $rangoDesde = $offset + 1;
    $rangoHasta = min($offset + $porPagina, $total);
    $qs = [];
    if ($filtros['busqueda'] !== '') { $qs[] = 'buscar=' . urlencode($filtros['busqueda']); }
    if ($filtros['id_usuario']) { $qs[] = 'usuario=' . (int)$filtros['id_usuario']; }
    if ($filtros['accion'] !== '') { $qs[] = 'accion=' . urlencode($filtros['accion']); }
    if ($filtros['entidad'] !== '') { $qs[] = 'entidad=' . urlencode($filtros['entidad']); }
    if ($filtros['desde'] !== '') { $qs[] = 'desde=' . urlencode($filtros['desde']); }
    if ($filtros['hasta'] !== '') { $qs[] = 'hasta=' . urlencode($filtros['hasta']); }
    $urlBase = BASE_URL . '/auditoria' . (empty($qs) ? '?' : '?' . implode('&', $qs) . '&') . 'pagina=';
    ?>
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-top: 16px;">
        <div style="color: #555; font-size: 14px;">
            Mostrando <strong><?php echo $rangoDesde; ?></strong>-<strong><?php echo $rangoHasta; ?></strong> de <strong><?php echo number_format($total); ?></strong> registros (página <?php echo $pagina; ?> de <?php echo $totalPaginas; ?>).
        </div>
        <?php if ($totalPaginas > 1): ?>
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                <a href="<?php echo $urlBase . max(1, $pagina - 1); ?>" class="btn" style="padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 13px; <?php echo ($pagina <= 1) ? 'background-color: #e9ecef; color: #aaa; pointer-events: none;' : 'background-color: var(--violeta-principal); color: white;'; ?>">
                    « Anterior
                </a>
                <?php
                $inicio = max(1, $pagina - 2);
                $fin = min($totalPaginas, $pagina + 2);
                for ($i = $inicio; $i <= $fin; $i++):
                    $activo = ($i === $pagina);
                    echo '<a href="' . $urlBase . $i . '" class="btn" style="padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 13px;' . ($activo ? 'background-color: var(--violeta-principal); color: white; font-weight: bold;' : 'background-color: #e9ecef; color: #333;') . '">' . $i . '</a>';
                endfor;
                ?>
                <a href="<?php echo $urlBase . min($totalPaginas, $pagina + 1); ?>" class="btn" style="padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 13px; <?php echo ($pagina >= $totalPaginas) ? 'background-color: #e9ecef; color: #aaa; pointer-events: none;' : 'background-color: var(--violeta-principal); color: white;'; ?>">
                    Siguiente »
                </a>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<p style="text-align:center; font-size:12px; color:#888; margin-top:20px;">
    El registro de auditoría es de solo lectura: las acciones registradas no pueden modificarse ni eliminarse desde el sistema.
</p>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>