<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h2 style="color: var(--violeta-principal); margin: 0;">Gestión de Usuarios</h2>
    <a href="<?php echo BASE_URL; ?>/usuario/crear" class="btn btn-primary">+ Nuevo Usuario</a>
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre Completo</th>
                <th>Correo</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($usuarios as $user): ?>
            <tr>
                <td><?php echo $user['id_usuario']; ?></td>
                <td><?php echo $user['nombre_completo']; ?></td>
                <td><?php echo $user['correo']; ?></td>
                <td>
                    <span class="badge" style="background-color: <?php echo $user['estado'] == 'Activo' ? 'var(--verde-menta)' : '#e0e0e0'; ?>; color: #333;">
                        <?php echo $user['estado']; ?>
                    </span>
                </td>
                <td>
                    <a href="<?php echo BASE_URL; ?>/usuario/editar/<?php echo $user['id_usuario']; ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px; text-decoration: none; display: inline-block;">Editar</a>
                    
                    <a href="<?php echo BASE_URL; ?>/usuario/eliminar/<?php echo $user['id_usuario']; ?>" 
                       style="background-color: #dc3545; color: white; padding: 6px 12px; font-size: 12px; text-decoration: none; display: inline-block; border-radius: 4px; margin-left: 5px;"
                       onclick="return confirm('¿Estás seguro de que deseas desactivar a este usuario? Ya no podrá acceder al sistema.');">
                       Eliminar
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>