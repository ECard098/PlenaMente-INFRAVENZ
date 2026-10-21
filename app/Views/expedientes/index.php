<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php
$coloresEstadoExpediente = [
    'Activo'      => 'background: #e8f5e9; color: #1b5e20;',
    'Seguimiento' => 'background: #fff3cd; color: #856404;',
    'Cerrado'     => 'background: #e9ecef; color: #555;'
];
?>

<div style="display: flex; justify-content: space-between; align-items: 
center; margin-bottom: 24px;">
    <h2 style="color: var(--violeta-principal); margin: 0;">Expedientes
        Clínicos Abiertos</h2>
</div>

<?php if (isset($_GET['error']) && $_GET['error'] == 'ya_existe'): ?>
    <div style="padding: 12px; background-color: #f8d7da; color: 
#721c24; border-radius: 4px; margin-bottom: 20px;">

        ⚠
        El paciente seleccionado ya cuenta con un expediente clínico
        activo.
    </div>
<?php endif; ?>

<?php if (isset($_GET['success']) && $_GET['success'] == 'creado'): ?>
    <div style="padding: 12px; background-color: #d4edda; color: 
#155724; border-radius: 4px; margin-bottom: 20px;">

        ✅
        Expediente clínico creado y asociado correctamente.
    </div>
<?php endif; ?>

<div class="card" style="background: white; padding: 20px; 
border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
    <?php if (empty($expedientes)): ?>
        <p style="text-align: center; color: #666; margin: 20px 0;">No
            hay expedientes clínicos abiertos en este momento.</p>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse; 
text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid #eee; color: 
#555;">
                    <th style="padding: 12px;">N° Expediente</th>
                    <th style="padding: 12px;">NIE / DUI</th>
                    <th style="padding: 12px;">Paciente</th>
                    <th style="padding: 12px;">Tipo</th>
                    <th style="padding: 12px;">Fecha Apertura</th>
                    <th style="padding: 12px;">Estado</th>
                    <th style="padding: 12px; text-align: 
center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expedientes as $expediente): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px; font-weight: bold; 
color: var(--violeta-principal);">
                            EXP-<?php echo
                                str_pad($expediente['id_expediente'], 5, "0", STR_PAD_LEFT); ?>
                        </td>
                        <td style="padding: 12px;"><?php echo
                                                    htmlspecialchars($expediente['nie_dui']); ?></td>
                        <td style="padding: 12px;"><?php echo
                                                    htmlspecialchars($expediente['nombres'] . ' ' .
                                                        $expediente['apellidos']); ?></td>
                        <td style="padding: 12px;">
                            <span style="padding: 4px 8px; 
border-radius: 12px; font-size: 12px; background: <?php echo
                                                    $expediente['tipo_paciente'] == 'Estudiante' ? '#e3f2fd; color: 
#0d47a1;' : '#e8f5e9; color: #1b5e20;'; ?>">
                                <?php echo
                                $expediente['tipo_paciente']; ?>
                            </span>
                        </td>
                        <td style="padding: 12px;"><?php echo
                                                    date('d/m/Y', strtotime($expediente['fecha_apertura'])); ?></td>
                        <td style="padding: 12px;">
                            <?php $estadoCaso =
                                $expediente['estado_caso'] ?? 'Activo'; ?>
                            <span style="padding: 4px 8px; 
border-radius: 12px; font-size: 12px; font-weight: bold; <?php echo
                                                            $coloresEstadoExpediente[$estadoCaso] ??
                                                                $coloresEstadoExpediente['Activo']; ?>">
                                <?php echo
                                htmlspecialchars($estadoCaso); ?>
                            </span>
                        </td>
                        <td style="padding: 12px; text-align: center;">
                            <a href="<?php echo BASE_URL;
                                        ?>/expediente/ver/<?php echo $expediente['id_expediente']; ?>"
                                class="btn btn-primary" style="padding: 6px 12px; font-size: 13px; 
text-decoration: none; border-radius: 4px; background-color: 
var(--violeta-principal); border: none;">Ver Historial</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>