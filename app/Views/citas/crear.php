<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div style="max-width: 600px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h2 style="color: var(--violeta-principal); margin: 0;">Programar Cita Médica/Psicológica</h2>
        <a href="<?php echo BASE_URL; ?>/cita" class="btn btn-secondary">Cancelar</a>
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

    <div class="card" style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <form action="<?php echo BASE_URL; ?>/cita/guardar" method="POST">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Seleccionar Paciente:</label>
                <select name="id_paciente" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;">
                    <option value="">-- Seleccione un paciente del directorio --</option>
                    <?php foreach ($pacientes as $paciente): ?>
                        <option value="<?php echo $paciente['id_paciente']; ?>">
                            <?php echo htmlspecialchars($paciente['nombres'] . ' ' . $paciente['apellidos'] . ' (' . $paciente['nie_dui'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">Fecha de la Cita:</label>
                    <input type="date" name="fecha_cita" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: bold;">Hora de la Cita:</label>
                    <input type="time" name="hora_cita" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Motivo de la Consulta:</label>
                <input type="text" name="motivo" required placeholder="Ej. Control de rendimiento, Evaluación emocional, Crisis..." style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; background-color: var(--violeta-principal); border: none; font-size: 16px;">
                Confirmar y Registrar Cita
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>