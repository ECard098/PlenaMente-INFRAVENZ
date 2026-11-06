<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h2 style="color: var(--violeta-principal); margin: 0;">✏️ Actualizar Datos del Paciente</h2>
    <a href="<?php echo BASE_URL; ?>/paciente" class="btn btn-secondary">Volver al Directorio</a>
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

<div class="card">
    <div style="padding: 12px; background-color: #e8f0fe; color: #1a3d6d; border-radius: 4px; margin-bottom: 20px;">
        ℹ️ Los datos con <span style="color:#dc3545; font-weight:bold;">*</span> son obligatorios. Al guardar se validará la información y se mostrará un mensaje de confirmación.
    </div>

    <form action="<?php echo BASE_URL; ?>/paciente/actualizar/<?php echo $paciente['id_paciente']; ?>" method="POST">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">NIE / DUI (Obligatorio): <span style="color:#dc3545;">*</span></label>
                    <input type="text" name="nie_dui" required maxlength="20"
                           value="<?php echo htmlspecialchars($paciente['nie_dui']); ?>"
                           style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Nombres: <span style="color:#dc3545;">*</span></label>
                    <input type="text" name="nombres" required maxlength="100"
                           value="<?php echo htmlspecialchars($paciente['nombres']); ?>"
                           style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Apellidos: <span style="color:#dc3545;">*</span></label>
                    <input type="text" name="apellidos" required maxlength="100"
                           value="<?php echo htmlspecialchars($paciente['apellidos']); ?>"
                           style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Fecha de Nacimiento: <span style="color:#dc3545;">*</span></label>
                    <input type="date" name="fecha_nacimiento" required
                           value="<?php echo htmlspecialchars($paciente['fecha_nacimiento']); ?>"
                           max="<?php echo date('Y-m-d'); ?>"
                           style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Género: <span style="color:#dc3545;">*</span></label>
                    <select name="genero" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="">Seleccione...</option>
                        <?php foreach (['Masculino', 'Femenino', 'Otro'] as $opcionGenero): ?>
                            <option value="<?php echo $opcionGenero; ?>" <?php echo ($paciente['genero'] === $opcionGenero) ? 'selected' : ''; ?>>
                                <?php echo $opcionGenero; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Tipo de Paciente: <span style="color:#dc3545;">*</span></label>
                    <select name="tipo_paciente" id="tipo_paciente" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="Estudiante" <?php echo $paciente['tipo_paciente'] === 'Estudiante' ? 'selected' : ''; ?>>Estudiante</option>
                        <option value="Empleado" <?php echo $paciente['tipo_paciente'] === 'Empleado' ? 'selected' : ''; ?>>Empleado</option>
                    </select>
                    <small style="color: #888;">Al seleccionar "Empleado" el grado/sección se guarda vacío.</small>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Grado y Sección (Opcional):</label>
                    <input type="text" name="grado_seccion" maxlength="50"
                           value="<?php echo htmlspecialchars($paciente['grado_seccion'] ?? ''); ?>"
                           placeholder="Ej. 7° Grado A"
                           style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Teléfono de Contacto (Opcional):</label>
                    <input type="text" name="telefono_contacto" maxlength="15"
                           value="<?php echo htmlspecialchars($paciente['telefono_contacto'] ?? ''); ?>"
                           placeholder="Ej. 7777-8888"
                           style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Correo Electrónico (Opcional):</label>
                    <input type="email" name="correo_paciente" maxlength="150"
                           value="<?php echo htmlspecialchars($paciente['correo_paciente'] ?? ''); ?>"
                           placeholder="ejemplo@correo.com"
                           style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Nombre del Responsable (Opcional):</label>
                    <input type="text" name="nombre_responsable" maxlength="150"
                           value="<?php echo htmlspecialchars($paciente['nombre_responsable'] ?? ''); ?>"
                           placeholder="Padre, madre o tutor"
                           style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px; background-color: var(--violeta-principal); border: none; color: white; font-size: 16px; border-radius: 4px; cursor: pointer;">
                💾 Guardar Cambios
            </button>
            <a href="<?php echo BASE_URL; ?>/paciente" class="btn btn-secondary" style="flex: 1; padding: 12px; text-align: center; text-decoration: none;">Cancelar</a>
        </div>
    </form>
</div>

<script>
    // Oculta el grado/sección cuando el paciente es empleado
    (function () {
        var selectTipo = document.getElementById('tipo_paciente');
        var inputGrado = document.querySelector('input[name="grado_seccion"]');
        if (!selectTipo || !inputGrado) { return; }

        function aplicar() {
            var esEmpleado = selectTipo.value === 'Empleado';
            inputGrado.disabled = esEmpleado;
            if (esEmpleado) { inputGrado.value = ''; }
        }
        selectTipo.addEventListener('change', aplicar);
        aplicar();
    })();
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
