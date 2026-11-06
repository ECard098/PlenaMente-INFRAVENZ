<?php
require_once __DIR__ . '/../layouts/header.php';

$estado = $cita['estado_cita'];
$badge_color = $estado == 'Programada' ? '#ffc107' : ($estado == 'Completada' ? '#28a745' : '#dc3545');
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h2 style="color: var(--violeta-principal); margin: 0;">👁️ Detalle de la Cita</h2>
    <a href="<?php echo BASE_URL; ?>/cita" class="btn btn-secondary">⬅ Volver a Citas</a>
</div>

<?php if (!empty($flash)): ?>
    <?php foreach ($flash as $tipo => $mensajes): ?>
        <?php foreach ($mensajes as $mensaje): ?>
            <div style="padding: 12px; background-color: <?php echo $tipo == 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $tipo == 'success' ? '#155724' : '#721c24'; ?>; border-radius: 4px; margin-bottom: 16px; font-weight: bold;">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
    <!-- Datos de la cita -->
    <div class="card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <h3 style="margin-top: 0; margin-bottom: 16px; color: #333;">📅 Datos de la Cita</h3>
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #777; width: 40%;">Fecha</td>
                <td style="padding: 8px 0;"><strong><?php echo date('d/m/Y', strtotime($cita['fecha_hora'])); ?></strong></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #777;">Hora</td>
                <td style="padding: 8px 0;"><strong>⏰ <?php echo date('g:i A', strtotime($cita['fecha_hora'])); ?></strong></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #777;">Motivo de Consulta</td>
                <td style="padding: 8px 0;"><strong><?php echo htmlspecialchars($cita['motivo_cita']); ?></strong></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #777;">Estado</td>
                <td style="padding: 8px 0;">
                    <span style="padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold; background: <?php echo $badge_color; ?>; color: white;">
                        <?php echo $estado; ?>
                    </span>
                </td>
            </tr>
        </table>

        <div style="margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
            <?php if ($estado == 'Programada'): ?>
                <a href="<?php echo BASE_URL; ?>/expediente/atender_cita/<?php echo $cita['id_cita']; ?>" class="btn btn-success" style="background-color: #28a745; border: none; text-decoration: none; color: white;">
                    ▶️ Atender Cita
                </a>
                <a href="<?php echo BASE_URL; ?>/cita/editar/<?php echo $cita['id_cita']; ?>" class="btn" style="background-color: #17a2b8; border: none; text-decoration: none; color: white;">
                    ✏️ Reprogramar
                </a>
                <a href="<?php echo BASE_URL; ?>/cita/cancelar/<?php echo $cita['id_cita']; ?>" class="btn" style="background-color: #dc3545; border: none; text-decoration: none; color: white;">
                    🚫 Cancelar Cita
                </a>
                <form method="POST" action="<?php echo BASE_URL; ?>/recordatorio/enviar/<?php echo $cita['id_cita']; ?>" style="display: inline;">
                    <button type="submit" class="btn" style="background-color: #6f42c1; border: none; text-decoration: none; color: white; cursor: pointer;">
                        🔔 Enviar Recordatorio
                    </button>
                </form>
            <?php endif; ?>
            <?php if (!empty($cita['id_expediente'])): ?>
                <a href="<?php echo BASE_URL; ?>/expediente/ver/<?php echo $cita['id_expediente']; ?>" class="btn btn-primary" style="background-color: var(--violeta-principal); border: none; text-decoration: none; color: white;">
                    📁 Ver Expediente
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Datos del paciente -->
    <div class="card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <h3 style="margin-top: 0; margin-bottom: 16px; color: #333;">🧑‍🎓 Paciente</h3>
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #777; width: 40%;">Nombre Completo</td>
                <td style="padding: 8px 0;"><strong><?php echo htmlspecialchars($cita['nombres'] . ' ' . $cita['apellidos']); ?></strong></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #777;">NIE / DUI</td>
                <td style="padding: 8px 0;"><?php echo htmlspecialchars($cita['nie_dui']); ?></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #777;">Tipo</td>
                <td style="padding: 8px 0;">
                    <span class="badge" style="background-color: <?php echo $cita['tipo_paciente'] == 'Estudiante' ? 'var(--azul-claro)' : 'var(--verde-menta)'; ?>; color: #333;">
                        <?php echo htmlspecialchars($cita['tipo_paciente']); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #777;">Grado / Sección</td>
                <td style="padding: 8px 0;"><?php echo $cita['grado_seccion'] ? htmlspecialchars($cita['grado_seccion']) : '<span style="color:#999;">N/A</span>'; ?></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #777;">Teléfono</td>
                <td style="padding: 8px 0;"><?php echo $cita['telefono_contacto'] ? htmlspecialchars($cita['telefono_contacto']) : '<span style="color:#999;">Sin registrar</span>'; ?></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #777;">Correo</td>
                <td style="padding: 8px 0;"><?php echo $cita['correo_paciente'] ? htmlspecialchars($cita['correo_paciente']) : '<span style="color:#999;">Sin registrar</span>'; ?></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #777;">Responsable</td>
                <td style="padding: 8px 0;"><?php echo $cita['nombre_responsable'] ? htmlspecialchars($cita['nombre_responsable']) : '<span style="color:#999;">Sin registrar</span>'; ?></td>
            </tr>
        </table>
    </div>
</div>

<?php if ($estado == 'Cancelada'): ?>
    <div class="card" style="background: #f8d7da; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-left: 5px solid #dc3545;">
        <h3 style="margin-top: 0; margin-bottom: 16px; color: #721c24;">🚫 Información de la Cancelación</h3>
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #721c24; width: 40%;">Motivo</td>
                <td style="padding: 8px 0; color: #721c24;"><strong><?php echo htmlspecialchars($cita['motivo_cancelacion'] ?? 'No registrado'); ?></strong></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #721c24;">Fecha de cancelación</td>
                <td style="padding: 8px 0; color: #721c24;">
                    <?php echo !empty($cita['fecha_cancelacion']) ? date('d/m/Y g:i A', strtotime($cita['fecha_cancelacion'])) : 'No registrada'; ?>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #721c24;">Cancelada por</td>
                <td style="padding: 8px 0; color: #721c24;"><?php echo !empty($cita['nombre_cancela']) ? htmlspecialchars($cita['nombre_cancela']) : 'No registrado'; ?></td>
            </tr>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>