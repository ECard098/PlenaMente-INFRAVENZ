<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php
$fechaActual = date('Y-m-d', strtotime($cita['fecha_hora']));
$horaActual  = date('H:i', strtotime($cita['fecha_hora']));
?>

<div style="max-width: 640px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h2 style="color: var(--violeta-principal); margin: 0;">✏️ Reprogramar Cita</h2>
        <a href="<?php echo BASE_URL; ?>/cita/ver/<?php echo $cita['id_cita']; ?>" class="btn btn-secondary">⬅ Volver al Detalle</a>
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

    <!-- Resumen de la cita a reprogramar -->
    <div class="card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px;">
        <h3 style="margin-top: 0; margin-bottom: 16px; color: #333;">📅 Cita a reprogramar</h3>
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #777; width: 40%;">Paciente</td>
                <td style="padding: 8px 0;"><strong><?php echo htmlspecialchars($cita['nombres'] . ' ' . $cita['apellidos']); ?></strong></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #777;">NIE / DUI</td>
                <td style="padding: 8px 0;"><?php echo htmlspecialchars($cita['nie_dui']); ?></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #777;">Motivo de Consulta</td>
                <td style="padding: 8px 0;"><?php echo htmlspecialchars($cita['motivo_cita']); ?></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #777;">Fecha y Hora Actual</td>
                <td style="padding: 8px 0;"><strong>📅 <?php echo date('d/m/Y', strtotime($cita['fecha_hora'])); ?> — ⏰ <?php echo date('g:i A', strtotime($cita['fecha_hora'])); ?></strong></td>
            </tr>
        </table>
    </div>

    <!-- Formulario de reprogramación -->
    <div class="card" style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <div style="padding: 12px; background-color: #fff3cd; color: #856404; border-radius: 4px; margin-bottom: 20px;">
            ⚠️ Antes de guardar se validará la disponibilidad. No se permiten dos citas del mismo psicólogo dentro de la misma franja de 1 hora.
        </div>

        <form action="<?php echo BASE_URL; ?>/cita/editar/<?php echo $cita['id_cita']; ?>" method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">Nueva Fecha:</label>
                    <input type="date" name="fecha_cita" required value="<?php echo htmlspecialchars($fechaActual); ?>" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">Nueva Hora:</label>
                    <input type="time" name="hora_cita" required value="<?php echo htmlspecialchars($horaActual); ?>" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px; background-color: var(--violeta-principal); border: none; color: white; font-size: 16px; border-radius: 4px; cursor: pointer;">
                    ✅ Validar y Guardar Cambios
                </button>
                <a href="<?php echo BASE_URL; ?>/cita/ver/<?php echo $cita['id_cita']; ?>" class="btn btn-secondary" style="flex: 1; padding: 12px; text-align: center; text-decoration: none;">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
