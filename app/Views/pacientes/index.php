<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h2 style="color: var(--violeta-principal); margin: 0;">Directorio de Pacientes</h2>
    <a href="<?php echo BASE_URL; ?>/paciente/crear" class="btn btn-primary">+ Nuevo Paciente</a>
</div>

<div class="card">
    <div style="margin-bottom: 20px; display: flex; justify-content: flex-start;">
        <form action="<?php echo BASE_URL; ?>/paciente" method="GET" style="display: flex; gap: 10px; width: 100%; max-width: 500px;">
            <input type="text" 
                name="buscar" 
                placeholder="Buscar por NIE/DUI, nombre o apellido..." 
                value="<?php echo isset($data['busqueda']) ? htmlspecialchars($data['busqueda']) : ''; ?>"
                style="flex-grow: 1; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;">
                
            <button type="submit" class="btn btn-primary" style="padding: 8px 16px;">Buscar</button>
            
            <?php if (!empty($data['busqueda'])): ?>
                <a href="<?php echo BASE_URL; ?>/paciente" class="btn btn-secondary" style="padding: 8px 16px; text-decoration: none; display: inline-flex; align-items: center;">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>NIE / DUI</th>
                <th>Nombre Completo</th>
                <th>Tipo</th>
                <th>Grado/Sección</th>
                <th>Contacto</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($pacientes as $p): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($p['nie_dui']); ?></strong></td>
                <td><?php echo htmlspecialchars($p['nombres'] . ' ' . $p['apellidos']); ?></td>
                <td>
                    <span class="badge" style="background-color: <?php echo $p['tipo_paciente'] == 'Estudiante' ? 'var(--azul-claro)' : 'var(--verde-menta)'; ?>; color: #333;">
                        <?php echo $p['tipo_paciente']; ?>
                    </span>
                </td>
                <td><?php echo $p['grado_seccion'] ? htmlspecialchars($p['grado_seccion']) : '<span style="color:#999;">N/A</span>'; ?></td>
                <td><?php echo $p['telefono_contacto'] ? htmlspecialchars($p['telefono_contacto']) : '<span style="color:#999;">Sin registrar</span>'; ?></td>
                <td>
                  <!--  <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">Ver Ficha</button> -->
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if(empty($pacientes)): ?>
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px; color: #666;">
                    No hay pacientes registrados en el sistema. Haz clic en "+ Nuevo Paciente" para comenzar.
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>