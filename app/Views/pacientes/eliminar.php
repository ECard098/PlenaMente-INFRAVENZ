<?php require_once __DIR__ . '/../layouts/header.php'; ?> 
 
<?php 
$totalDependencias = array_sum(array_map('intval', $dependencias)); 
$etiquetasDep = [ 
    'expedientes' => 'Expediente(s) clínico(s)', 
    'sesiones'    => 'Sesiones clínicas', 
    'citas'       => 'Citas registradas', 
    'reportes'    => 'Reportes / informes', 
    'asistencias' => 'Registros de asistencia' 
]; 
?> 
 
<div style="max-width: 720px; margin: 0 auto;"> 
    <div style="display: flex; justify-content: space-between; align-items: 
center; margin-bottom: 24px;"> 
        <h2 style="color: var(--violeta-principal); margin: 0;">
🗑
 Eliminar 
Paciente</h2> 
        <a href="<?php echo BASE_URL; ?>/paciente" class="btn 
btn-secondary">⬅ Volver al Directorio</a> 
    </div> 
 
    <?php if (!empty($flash)): ?> 
        <?php foreach ($flash as $tipo => $mensajes): ?> 
            <?php foreach ($mensajes as $mensaje): ?> 
                <div style="padding: 12px; background-color: <?php echo $tipo 
== 'success' ? '#d4edda' : ($tipo == 'warning' ? '#fff3cd' : '#f8d7da'); ?>; 
color: <?php echo $tipo == 'success' ? '#155724' : ($tipo == 'warning' ? 
'#856404' : '#721c24'); ?>; border-radius: 4px; margin-bottom: 16px; 
font-weight: bold;"> 
                    <?php echo htmlspecialchars($mensaje); ?> 
                </div> 
            <?php endforeach; ?> 
        <?php endforeach; ?> 
    <?php endif; ?> 
 
    <!-- Resumen del paciente --> 
    <div class="card" style="background: white; padding: 20px; border-radius: 
8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px;"> 
        <h3 style="margin-top: 0; margin-bottom: 16px; color: #333;">
🧑
 
Paciente a eliminar</h3> 
        <table class="table" style="width: 100%; border-collapse: collapse;"> 
            <tr> 
                <td style="padding: 8px 0; color: #777; width: 
35%;">Nombre</td> 
                <td style="padding: 8px 0;"><strong><?php echo 
htmlspecialchars($paciente['nombres'] . ' ' . $paciente['apellidos']); 
?></strong></td> 
            </tr> 
            <tr> 
                <td style="padding: 8px 0; color: #777;">NIE / DUI</td> 
                <td style="padding: 8px 0;"><strong><?php echo 
htmlspecialchars($paciente['nie_dui']); ?></strong></td> 
            </tr> 
            <tr> 
                <td style="padding: 8px 0; color: #777;">Tipo</td> 
                <td style="padding: 8px 0;"><?php echo 
htmlspecialchars($paciente['tipo_paciente']); ?><?php echo 
!empty($paciente['grado_seccion']) ? ' — ' . 
htmlspecialchars($paciente['grado_seccion']) : ''; ?></td> 
            </tr> 
            <tr> 
                <td style="padding: 8px 0; color: #777;">Contacto</td> 
                <td style="padding: 8px 0;"><?php echo 
!empty($paciente['telefono_contacto']) ? 
htmlspecialchars($paciente['telefono_contacto']) : '<span 
style="color:#999;">Sin registrar</span>'; ?></td> 
            </tr> 
        </table> 
    </div> 
 
    <?php if ($critico): ?> 
        <!-- Información crítica: se exige validación reforzada --> 
        <div style="background: #f8d7da; border-left: 5px solid #dc3545; 
padding: 16px 20px; border-radius: 8px; margin-bottom: 20px;"> 
            <h3 style="margin: 0 0 8px 0; color: #721c24;">
⚠
 Este paciente 
tiene información crítica</h3> 
            <p style="margin: 0 0 12px 0; color: #721c24;"> 
                El paciente posee <strong><?php echo $totalDependencias; 
?></strong> registro(s) asociado(s) que no deben perderse. Por seguridad, el 
sistema <strong>no elimina</strong> estos datos: se realizará una 
<strong>eliminación lógica (desactivación)</strong> y el historial clínico se 
conservará. 
            </p> 
            <ul style="margin: 0; color: #721c24;"> 
                <?php foreach ($etiquetasDep as $clave => $etiqueta): ?> 
                    <?php if (!empty($dependencias[$clave])): ?> 
                        <li><strong><?php echo (int)$dependencias[$clave]; 
?></strong> <?php echo $etiqueta; ?></li> 
                    <?php endif; ?> 
                <?php endforeach; ?> 
            </ul> 
        </div> 
 
        <div class="card" style="background: white; padding: 25px; 
border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);"> 
            <form method="POST" action="<?php echo BASE_URL; 
?>/paciente/eliminar/<?php echo $paciente['id_paciente']; ?>"> 
                <div style="margin-bottom: 18px;"> 
                    <label style="display: block; margin-bottom: 8px; 
font-weight: bold;"> 
                        Para confirmar, escribe el NIE/DUI del paciente 
(<code><?php echo htmlspecialchars($paciente['nie_dui']); ?></code>): 
                    </label> 
                    <input type="text" name="confirmacion_nie" required 
autocomplete="off" 
                           placeholder="Escribe el NIE/DUI exactamente" 
                           style="width: 100%; padding: 10px; border: 1px 
solid #ccc; border-radius: 4px; font-size: 14px;"> 
                </div> 
 
                <div style="margin-bottom: 22px;"> 
                    <label style="display: flex; align-items: flex-start; gap: 
8px; font-size: 14px; color: #721c24;"> 
                        <input type="checkbox" name="aceptar" value="1" 
required style="margin-top: 3px;"> 
                        <span>Entiendo que el paciente será 
<strong>desactivado</strong> y que su información clínica se conservará en el 
sistema.</span> 
                    </label> 
                </div> 
 
                <div style="display: flex; gap: 10px;"> 
                    <button type="submit" class="btn" style="flex: 1; padding: 
12px; background-color: #dc3545; border: none; color: white; font-size: 16px; 
border-radius: 4px; cursor: pointer;"> 
                        
�
�
 Confirmar Eliminación 
                    </button> 
                    <a href="<?php echo BASE_URL; ?>/paciente" class="btn 
btn-secondary" style="flex: 1; padding: 12px; text-align: center; 
text-decoration: none;">Cancelar</a> 
                </div> 
            </form> 
        </div> 
    <?php else: ?> 
        <!-- Sin información crítica: confirmación simple --> 
        <div style="background: #fff3cd; border-left: 5px solid #ffc107; 
padding: 16px 20px; border-radius: 8px; margin-bottom: 20px;"> 
            <h3 style="margin: 0 0 8px 0; color: #856404;">
⚠
 Confirmar 
acción</h3> 
            <p style="margin: 0; color: #856404;"> 
                Este paciente no tiene expediente, citas, sesiones, reportes 
ni asistencias registradas. Se realizará una <strong>eliminación 
lógica</strong> (el registro se conserva, pero dejará de aparecer en el 
directorio activo). 
            </p> 
        </div> 
 
        <div class="card" style="background: white; padding: 25px; 
border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);"> 
            <form method="POST" action="<?php echo BASE_URL; 
?>/paciente/eliminar/<?php echo $paciente['id_paciente']; ?>"> 
                <div style="display: flex; gap: 10px;"> 
                    <button type="submit" class="btn" style="flex: 1; padding: 
12px; background-color: #dc3545; border: none; color: white; font-size: 16px; 
border-radius: 4px; cursor: pointer;"> 
                        
�
�
 Confirmar Eliminación 
                    </button> 
                    <a href="<?php echo BASE_URL; ?>/paciente" class="btn 
btn-secondary" style="flex: 1; padding: 12px; text-align: center; 
text-decoration: none;">Cancelar</a> 
                </div> 
            </form> 
        </div> 
    <?php endif; ?> 
</div> 
 
<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 