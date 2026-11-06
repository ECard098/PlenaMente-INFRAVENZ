<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h2 style="color: var(--violeta-principal); margin: 0;">Agenda de Citas</h2>
    <a href="<?php echo BASE_URL; ?>/cita/crear" class="btn btn-primary" style="background-color: var(--violeta-principal); border: none;">📅 Programar Nueva Cita</a>
</div>

<?php if (isset($_GET['success']) && $_GET['success'] == 'creada'): ?>
    <div style="padding: 12px; background-color: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 20px; font-weight: bold;">
        ✅ Cita agendada correctamente en el calendario.
    </div>
<?php endif; ?>

<!-- Tabs de vista: Hoy / Todas -->
<div style="display: flex; gap: 8px; margin-bottom: 16px;">
    <a href="<?php echo BASE_URL; ?>/cita?vista=hoy" class="btn" style="<?php echo ($vista === 'hoy') ? 'background-color: var(--violeta-principal); color: white; border: none;' : 'background-color: #e9ecef; color: #333; text-decoration: none;'; ?>">
        📅 Citas de Hoy
    </a>
    <a href="<?php echo BASE_URL; ?>/cita?vista=todas" class="btn" style="<?php echo ($vista === 'todas') ? 'background-color: var(--violeta-principal); color: white; border: none;' : 'background-color: #e9ecef; color: #333; text-decoration: none;'; ?>">
        🗓️ Todas las Citas
    </a>
</div>

<?php if ($vista === 'hoy'): ?>
    <p style="color: #555; margin-bottom: 16px;">Citas programadas para <strong><?php echo date('l, d/m/Y'); ?></strong>. Se muestran en orden cronológico.</p>
<?php else: ?>
    <!-- Filtro por rango de fechas (solo en "Todas las Citas") -->
    <form method="GET" action="<?php echo BASE_URL; ?>/cita" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px; background: #f8f9fa; border: 1px solid #eee; padding: 16px; border-radius: 8px; margin-bottom: 16px;">
        <input type="hidden" name="vista" value="todas">
        <div>
            <label style="display: block; margin-bottom: 6px; font-weight: bold; font-size: 13px; color: #555;">Desde</label>
            <input type="date" name="desde" value="<?php echo htmlspecialchars($desde); ?>" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        </div>
        <div>
            <label style="display: block; margin-bottom: 6px; font-weight: bold; font-size: 13px; color: #555;">Hasta</label>
            <input type="date" name="hasta" value="<?php echo htmlspecialchars($hasta); ?>" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        </div>
        <button type="submit" class="btn" style="background-color: var(--violeta-principal); border: none; color: white; padding: 9px 16px; border-radius: 4px; cursor: pointer;">
            🔎 Filtrar
        </button>
        <?php if ($desde !== '' || $hasta !== ''): ?>
            <a href="<?php echo BASE_URL; ?>/cita?vista=todas" class="btn btn-secondary" style="padding: 9px 16px; text-decoration: none;">✖ Limpiar filtro</a>
        <?php endif; ?>
        <span style="color: #888; font-size: 12px; margin-left: auto;">Deja las fechas vacías para ver todas.</span>
    </form>
<?php endif; ?>

<div class="card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
    <?php if (empty($citas)): ?>
        <p style="text-align: center; color: #666; margin: 20px 0;">
            <?php
                if ($vista === 'hoy') {
                    echo 'No hay citas programadas para el día de hoy.';
                } elseif ($desde !== '' || $hasta !== '') {
                    echo 'No hay citas en el rango de fechas seleccionado.';
                } else {
                    echo 'No hay citas pendientes programadas en la agenda.';
                }
            ?>
        </p>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid #eee; color: #555;">
                    <th style="padding: 12px;">Fecha y Hora</th>
                    <th style="padding: 12px;">Paciente</th>
                    <th style="padding: 12px;">Motivo de Consulta</th>
                    <th style="padding: 12px;">Estado</th>
                    <th style="padding: 12px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($citas as $cita): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px;">
                            <strong><?php echo date('d/m/Y', strtotime($cita['fecha_hora'])); ?></strong><br>
                            <span style="color: #777; font-size: 13px;">⏰ <?php echo date('g:i A', strtotime($cita['fecha_hora'])); ?></span>
                        </td>
                        <td style="padding: 12px;">
                            <?php echo htmlspecialchars($cita['nombres'] . ' ' . $cita['apellidos']); ?><br>
                            <small style="color: #888;">DUI/NIE: <?php echo htmlspecialchars($cita['nie_dui']); ?></small>
                        </td>
                        <td style="padding: 12px; color: #555; font-size: 14px;"><?php echo htmlspecialchars($cita['motivo_cita']); ?></td>
                        <td style="padding: 12px;">
                            <span title="<?php echo ($cita['estado_cita'] == 'Cancelada' && !empty($cita['motivo_cancelacion'])) ? htmlspecialchars('Motivo: ' . $cita['motivo_cancelacion']) : ''; ?>" style="padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; background: <?php echo $cita['estado_cita'] == 'Programada' ? '#fff3cd; color: #856404;' : ($cita['estado_cita'] == 'Completada' ? '#d4edda; color: #155724;' : '#f8d7da; color: #721c24;'); ?>">
                                <?php echo $cita['estado_cita']; ?>
                            </span>
                        </td>
                        <td style="padding: 12px; text-align: center; white-space: nowrap;">
                            <a href="<?php echo BASE_URL; ?>/cita/ver/<?php echo $cita['id_cita']; ?>" class="btn btn-primary" style="background-color: var(--violeta-principal); border: none; padding: 6px 12px; font-size: 13px; text-decoration: none; color: white; border-radius: 4px; display: inline-block;">
                                👁️ Ver Detalles
                            </a>
                            <?php if ($cita['estado_cita'] == 'Programada'): ?>
                                <a href="<?php echo BASE_URL; ?>/expediente/atender_cita/<?php echo $cita['id_cita']; ?>" class="btn btn-success" style="background-color: #28a745; border: none; padding: 6px 12px; font-size: 13px; text-decoration: none; color: white; border-radius: 4px; display: inline-block;">
                                    ▶️ Atender
                                </a>
                                <a href="<?php echo BASE_URL; ?>/cita/editar/<?php echo $cita['id_cita']; ?>" class="btn" style="background-color: #17a2b8; border: none; padding: 6px 12px; font-size: 13px; text-decoration: none; color: white; border-radius: 4px; display: inline-block;">
                                    ✏️ Editar
                                </a>
                                <a href="<?php echo BASE_URL; ?>/cita/cancelar/<?php echo $cita['id_cita']; ?>" class="btn" style="background-color: #dc3545; border: none; padding: 6px 12px; font-size: 13px; text-decoration: none; color: white; border-radius: 4px; display: inline-block;">
                                    🚫 Cancelar
                                </a>
                                <form method="POST" action="<?php echo BASE_URL; ?>/recordatorio/enviar/<?php echo $cita['id_cita']; ?>" style="display: inline;">
                                    <button type="submit" class="btn" style="background-color: #6f42c1; border: none; padding: 6px 12px; font-size: 13px; color: white; border-radius: 4px; cursor: pointer; display: inline-block;">
                                        🔔 Recordatorio
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php if (!empty($total)): ?>
    <?php
        $rangoDesde = $offset + 1;
        $rangoHasta = min($offset + $porPagina, $total);
        $qs = 'vista=' . urlencode($vista);
        if ($vista === 'todas') {
            if ($desde !== '') {
                $qs .= '&desde=' . urlencode($desde);
            }
            if ($hasta !== '') {
                $qs .= '&hasta=' . urlencode($hasta);
            }
        }
        $urlBase = BASE_URL . '/cita?' . $qs . '&pagina=';
    ?>
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-top: 16px;">
        <div style="color: #555; font-size: 14px;">
            Mostrando <strong><?php echo $rangoDesde; ?></strong>–<strong><?php echo $rangoHasta; ?></strong> de <strong><?php echo $total; ?></strong> citas
            (página <?php echo $pagina; ?> de <?php echo $totalPaginas; ?>).
        </div>

        <?php if ($totalPaginas > 1): ?>
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                <a href="<?php echo $urlBase . max(1, $pagina - 1); ?>" class="btn" style="padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 13px; <?php echo ($pagina <= 1) ? 'background-color: #e9ecef; color: #aaa; pointer-events: none;' : 'background-color: var(--violeta-principal); color: white;'; ?>">
                    ⬅ Anterior
                </a>

                <?php
                    $inicio = max(1, $pagina - 2);
                    $fin = min($totalPaginas, $pagina + 2);
                    if ($inicio > 1) {
                        echo '<a href="' . $urlBase . '1" class="btn" style="padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 13px; background-color: #e9ecef; color: #333;">1</a>';
                        if ($inicio > 2) {
                            echo '<span style="padding: 6px 4px; color: #999;">…</span>';
                        }
                    }
                    for ($i = $inicio; $i <= $fin; $i++) {
                        $activo = ($i === $pagina);
                        echo '<a href="' . $urlBase . $i . '" class="btn" style="padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 13px; ' . ($activo ? 'background-color: var(--violeta-principal); color: white; font-weight: bold;' : 'background-color: #e9ecef; color: #333;') . '">' . $i . '</a>';
                    }
                    if ($fin < $totalPaginas) {
                        if ($fin < $totalPaginas - 1) {
                            echo '<span style="padding: 6px 4px; color: #999;">…</span>';
                        }
                        echo '<a href="' . $urlBase . $totalPaginas . '" class="btn" style="padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 13px; background-color: #e9ecef; color: #333;">' . $totalPaginas . '</a>';
                    }
                ?>

                <a href="<?php echo $urlBase . min($totalPaginas, $pagina + 1); ?>" class="btn" style="padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 13px; <?php echo ($pagina >= $totalPaginas) ? 'background-color: #e9ecef; color: #aaa; pointer-events: none;' : 'background-color: var(--violeta-principal); color: white;'; ?>">
                    Siguiente ➡
                </a>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>