<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php
$colorAccion = [
    'CREAR'      => '#28a745', 'ACTUALIZAR' => '#17a2b8', 'ELIMINAR' => '#dc3545',
    'DESACTIVAR' => '#fd7e14', 'REACTIVAR'  => '#6f42c1', 'RESTAURAR' => '#0d6efd',
    'REVERTIR'   => '#6c757d'
];
$color = $colorAccion[$registro['accion']] ?? '#6c757d';
?>
<div style="max-width: 860px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; gap: 12px; flex-wrap: wrap;">
        <h2 style="color: var(--violeta-principal); margin: 0;">📋 Detalle de la Acción Auditada</h2>
        <a href="<?php echo BASE_URL; ?>/auditoria" class="btn btn-secondary">← Volver al registro</a>
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

    <div style="background: #fff; border-top: 5px solid <?php echo $color; ?>; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap; margin-bottom: 20px;">
            <div>
                <span style="display: inline-block; padding: 6px 14px; border-radius: 14px; font-size: 13px; font-weight: bold; background: <?php echo $color; ?>; color: #fff;">
                    <?php echo htmlspecialchars($registro['accion']); ?>
                </span>
                <span style="display: inline-block; padding: 5px 12px; border-radius: 4px; font-size: 12px; margin-left: 6px; background: #f3e5f5; color: #5e35b1; border: 1px solid #e1bee7;">
                    <?php echo htmlspecialchars($registro['entidad']); ?>
                    <?php if (!empty($registro['id_entidad'])): ?> #<?php echo (int)$registro['id_entidad']; ?><?php endif; ?>
                </span>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: .5px;">Registro N°</div>
                <div style="font-size: 18px; font-weight: 700; color: #333;">#<?php echo str_pad((int)$registro['id_auditoria'], 5, '0', STR_PAD_LEFT); ?></div>
            </div>
        </div>

        <table class="table" style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 9px 0; color: #777; width: 30%; border-bottom: 1px solid #f0f0f0;">Fecha y hora</td>
                <td style="padding: 9px 0; border-bottom: 1px solid #f0f0f0;">
                    <strong><?php echo date('d/m/Y', strtotime($registro['fecha'])); ?></strong>
                    <?php echo date(' \a\ \l\a\s H:i:s', strtotime($registro['fecha'])); ?>
                </td>
            </tr>
            <tr>
                <td style="padding: 9px 0; color: #777; border-bottom: 1px solid #f0f0f0;">Usuario responsable</td>
                <td style="padding: 9px 0; border-bottom: 1px solid #f0f0f0;">
                    <?php if (!empty($registro['nombre_completo'])): ?>
                        <strong><?php echo htmlspecialchars($registro['nombre_completo']); ?></strong>
                        <?php if (!empty($registro['nombre_rol'])): ?>
                            <span style="color: #888;"> - <?php echo htmlspecialchars($registro['nombre_rol']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($registro['correo'])): ?>
                            <br><small style="color: #888;"><?php echo htmlspecialchars($registro['correo']); ?></small>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="color: #999; font-style: italic;">Usuario eliminado o no identificado (ID <?php echo (int)$registro['id_usuario']; ?>)</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td style="padding: 9px 0; color: #777; border-bottom: 1px solid #f0f0f0;">Tipo de acción</td>
                <td style="padding: 9px 0; border-bottom: 1px solid #f0f0f0;">
                    <strong><?php echo htmlspecialchars($registro['accion']); ?></strong>
                </td>
            </tr>
            <tr>
                <td style="padding: 9px 0; color: #777; border-bottom: 1px solid #f0f0f0;">Entidad afectada</td>
                <td style="padding: 9px 0; border-bottom: 1px solid #f0f0f0;">
                    <?php echo htmlspecialchars($registro['entidad']); ?>
                    <?php if (!empty($registro['id_entidad'])): ?>
                        (ID <?php echo (int)$registro['id_entidad']; ?>)
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td style="padding: 9px 0; color: #777;">Descripción</td>
                <td style="padding: 9px 0; line-height: 1.65;">
                    <?php echo nl2br(htmlspecialchars($registro['descripcion'])); ?>
                </td>
            </tr>
        </table>
    </div>

    <div style="padding: 12px; background-color: #e8f0fe; color: #1a3d6d; border-radius: 4px; font-size: 13px;">
        Este registro es de <strong>solo lectura</strong>: forma parte de la trazabilidad del sistema y no puede modificarse ni eliminarse.
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>