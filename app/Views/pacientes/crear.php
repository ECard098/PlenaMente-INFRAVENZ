<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h2 style="color: var(--violeta-principal); margin: 0;">Registrar Nuevo Paciente</h2>
    <a href="<?php echo BASE_URL; ?>/paciente" class="btn btn-secondary">Volver al Directorio</a>
</div>

<div class="card">
    <form action="<?php echo BASE_URL; ?>/paciente/guardar" method="POST">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">NIE / DUI (Obligatorio):</label>
                    <input type="text" name="nie_dui" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Nombres:</label>
                    <input type="text" name="nombres" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Apellidos:</label>
                    <input type="text" name="apellidos" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Fecha de Nacimiento:</label>
                    <input type="date" name="fecha_nacimiento" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Género:</label>
                    <select name="genero" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="">Seleccione...</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Femenino">Femenino</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
            </div>

            <div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Tipo de Paciente:</label>
                    <select name="tipo_paciente" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="Estudiante">Estudiante</option>
                        <option value="Empleado">Empleado</option>
                    </select>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Grado y Sección (Opcional):</label>
                    <input type="text" name="grado_seccion" placeholder="Ej. 7° Grado A" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Teléfono de Contacto (Opcional):</label>
                    <input type="text" name="telefono_contacto" placeholder="Ej. 7777-8888" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Correo Electrónico (Opcional):</label>
                    <input type="email" name="correo_paciente" placeholder="ejemplo@correo.com" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Nombre del Responsable (Opcional):</label>
                    <input type="text" name="nombre_responsable" placeholder="Padre, madre o tutor" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 10px; font-size: 16px;">Guardar Paciente</button>
    </form>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>