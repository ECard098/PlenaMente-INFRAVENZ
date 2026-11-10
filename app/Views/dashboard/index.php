<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php if (!empty($flash)): ?>
    <?php foreach ($flash as $tipo => $mensajes): ?>
        <?php foreach ($mensajes as $mensaje): ?>
            <div style="padding: 12px; background-color: <?php echo $tipo == 'success' ? '#d4edda' : ($tipo == 'warning' ? '#fff3cd' : '#f8d7da'); ?>; color: <?php echo $tipo == 'success' ? '#155724' : ($tipo == 'warning' ? '#856404' : '#721c24'); ?>; border-radius: 4px; margin-bottom: 16px; font-weight: bold;">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
<?php endif; ?>

<h2 style="color: var(--violeta-principal); margin-bottom: 24px;">Dashboard Principal</h2>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Citas Hoy</div>
        <div class="stat-number"><?php echo htmlspecialchars($citas_hoy); ?></div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, var(--azul-claro), var(--azul-medio));">
        <div class="stat-label">Estudiantes Activos</div>
        <div class="stat-number"><?php echo htmlspecialchars($estudiantes_activos); ?></div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, var(--verde-menta), var(--verde-menta-claro));">
        <div class="stat-label">Consultas Mes</div>
        <div class="stat-number"><?php echo htmlspecialchars($consultas_mes); ?></div>
    </div>
</div>

<?php 
// Obtenemos el rol actual de la sesión
$id_rol = $_SESSION['id_rol'] ?? 0; 
?>

<div class="card">
    <div class="card-header">Próximas Citas (Hoy)</div>
    
    <?php if($id_rol == 1): ?>
        <a href="<?php echo BASE_URL; ?>/cita" class="btn btn-primary" style="margin-bottom: 16px; text-decoration: none; display: inline-block;">+ Nueva Cita / Gestionar</a>
    <?php endif; ?>
    
    <table class="table">
        <thead>
            <tr>
                <th>Hora</th>
                <th>Paciente</th>
                <th>Motivo</th>
                <th>Estado</th>
                <?php if($id_rol == 1): ?>
                    <th>Acciones</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($proximas_citas)): ?>
                <tr>
                    <td colspan="<?php echo ($id_rol == 1) ? '5' : '4'; ?>" style="text-align: center; color: #888; padding: 20px;">
                        No hay citas programadas para el día de hoy.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach($proximas_citas as $cita): ?>
                    <tr>
                        <td><?php echo date('h:i A', strtotime($cita['hora_cita'])); ?></td>
                        <td><?php echo htmlspecialchars($cita['nombres'] . ' ' . $cita['apellidos']); ?></td>
                        <td><?php echo htmlspecialchars($cita['motivo_cita'] ?? 'No especificado'); ?></td>
                        <td>
                            <?php if($cita['estado_cita'] == 'Programada'): ?>
                                <span class="badge badge-programada" style="background-color: #007bff; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Programada</span>
                            <?php elseif($cita['estado_cita'] == 'Completada'): ?>
                                <span class="badge badge-completada" style="background-color: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Completada</span>
                            <?php else: ?>
                                <span class="badge" style="background-color: #6c757d; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;"><?php echo htmlspecialchars($cita['estado_cita']); ?></span>
                            <?php endif; ?>
                        </td>
                        
                        <?php if($id_rol == 1): ?>
                            <td style="white-space: nowrap;">
                                <a href="<?php echo BASE_URL; ?>/cita/ver/<?php echo $cita['id_cita']; ?>" class="btn btn-primary" style="background-color: var(--violeta-principal); padding: 6px 12px; font-size: 12px; text-decoration: none; color: white; border: none; border-radius: 4px;">👁️ Detalle</a>
                                <?php if(!empty($cita['id_expediente'])): ?>
                                    <a href="<?php echo BASE_URL; ?>/expediente/ver/<?php echo $cita['id_expediente']; ?>" class="btn btn-success" style="padding: 6px 12px; font-size: 12px; text-decoration: none; color: white; background-color: #20c997; border: none; border-radius: 4px;">Expediente</a>
                                <?php else: ?>
                                    <a href="<?php echo BASE_URL; ?>/paciente" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px; text-decoration: none; color: white; background-color: #6c757d; border: none; border-radius: 4px;">Ver Paciente</a>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>