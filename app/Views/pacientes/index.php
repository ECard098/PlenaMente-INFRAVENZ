<?php require_once __DIR__ . '/../layouts/header.php'; ?> 
 
<?php if (!empty($flash)): ?> 
    <?php foreach ($flash as $tipo => $mensajes): ?> 
        <?php foreach ($mensajes as $mensaje): ?> 
            <div style="padding: 12px; background-color: <?php echo $tipo == 
'success' ? '#d4edda' : ($tipo == 'warning' ? '#fff3cd' : '#f8d7da'); ?>; 
color: <?php echo $tipo == 'success' ? '#155724' : ($tipo == 'warning' ? 
'#856404' : '#721c24'); ?>; border-radius: 4px; margin-bottom: 16px; 
font-weight: bold;"> 
                <?php echo htmlspecialchars($mensaje); ?> 
            </div> 
        <?php endforeach; ?> 
    <?php endforeach; ?> 
<?php endif; ?> 
 
<?php 
$busquedaActual = $busqueda ?? ''; 
$verInactivos = !empty($mostrarInactivos); 
 
$qsToggle = $verInactivos ? '' : '?inactivos=1'; 
if (!empty($busquedaActual)) { 
    $qsToggle = $verInactivos 
        ? '?buscar=' . urlencode($busquedaActual) 
        : '?buscar=' . urlencode($busquedaActual) . '&inactivos=1'; 
} 
?> 
 
<div style="display: flex; justify-content: space-between; align-items: 
center; margin-bottom: 24px;"> 
    <h2 style="color: var(--violeta-principal); margin: 0;">Directorio de 
Pacientes</h2> 
    <a href="<?php echo BASE_URL; ?>/paciente/crear" class="btn btn-primary">+ 
Nuevo Paciente</a> 
</div> 
 
<div class="card"> 
    <div style="margin-bottom: 20px; display: flex; justify-content: 
space-between; align-items: center; gap: 10px; flex-wrap: wrap;"> 
        <form action="<?php echo BASE_URL; ?>/paciente" method="GET" 
style="display: flex; gap: 10px; width: 100%; max-width: 500px;"> 
            <?php if ($verInactivos): ?> 
                <input type="hidden" name="inactivos" value="1"> 
            <?php endif; ?> 
            <input type="text" 
                name="buscar" 
                placeholder="Buscar por NIE/DUI, nombre o apellido..." 
                value="<?php echo htmlspecialchars($busquedaActual); ?>" 
                style="flex-grow: 1; padding: 8px 12px; border: 1px solid 
#ccc; border-radius: 4px; font-size: 14px;"> 
                
            <button type="submit" class="btn btn-primary" style="padding: 8px 
16px;">Buscar</button> 
            
            <?php if (!empty($busquedaActual)): ?> 
                <a href="<?php echo BASE_URL; ?>/paciente<?php echo 
$verInactivos ? '?inactivos=1' : ''; ?>" class="btn btn-secondary" 
style="padding: 8px 16px; text-decoration: none; display: inline-flex; 
align-items: center;">Limpiar</a> 
            <?php endif; ?> 
        </form> 
 
        <a href="<?php echo BASE_URL; ?>/paciente<?php echo $qsToggle; ?>" 
           class="btn btn-secondary" 
           style="padding: 8px 16px; text-decoration: none; display: 
inline-flex; align-items: center; white-space: nowrap;"> 
            <?php echo $verInactivos ? '
🙈
 Ocultar inactivos' : '
👁
 Mostrar 
inactivos'; ?> 
        </a> 
    </div> 
    <table class="table"> 
        <thead> 
            <tr> 
                <th>NIE / DUI</th> 
                <th>Nombre Completo</th> 
                <th>Tipo</th> 
                <th>Grado/Sección</th> 
                <th>Contacto</th> 
                <th>Estado</th> 
                <th style="text-align: center;">Acciones</th> 
            </tr> 
        </thead> 
        <tbody> 
            <?php foreach($pacientes as $p): ?> 
            <?php $activo = ($p['estado_paciente'] ?? 'Activo') === 'Activo'; 
?> 
            <tr style="<?php echo $activo ? '' : 'opacity: 0.6;'; ?>"> 
                <td><strong><?php echo htmlspecialchars($p['nie_dui']); 
?></strong></td> 
                <td><?php echo htmlspecialchars($p['nombres'] . ' ' . 
$p['apellidos']); ?></td> 
                <td> 
                    <span class="badge" style="background-color: <?php echo 
$p['tipo_paciente'] == 'Estudiante' ? 'var(--azul-claro)' : 
'var(--verde-menta)'; ?>; color: #333;"> 
                        <?php echo $p['tipo_paciente']; ?> 
                    </span> 
                </td> 
                <td><?php echo $p['grado_seccion'] ? 
htmlspecialchars($p['grado_seccion']) : '<span 
style="color:#999;">N/A</span>'; ?></td> 
                <td><?php echo $p['telefono_contacto'] ? 
htmlspecialchars($p['telefono_contacto']) : '<span style="color:#999;">Sin 
registrar</span>'; ?></td> 
                <td> 
                    <?php if ($activo): ?> 
                        <span class="badge" style="background-color: #d4edda; 
color: #155724;">Activo</span> 
                    <?php else: ?> 
                        <span class="badge" style="background-color: #f8d7da; 
color: #721c24;">Inactivo</span> 
                    <?php endif; ?> 
                </td> 
                <td style="text-align: center;"> 
                    <?php if ($activo): ?> 
                        <?php if (!empty($p['id_expediente'])): ?> 
                            <a href="<?php echo BASE_URL; 
?>/expediente/ver/<?php echo $p['id_expediente']; ?>" 
                               title="Ver Expediente Clínico" 
                               class="btn btn-primary" 
                               style="background-color: 
var(--violeta-principal); color: white; padding: 6px 12px; font-size: 12px; 
text-decoration: none; border-radius: 4px; display: inline-block;"> 
                               
�
�
 Ver Exp 
                            </a> 
                        <?php else: ?> 
                            <a href="<?php echo BASE_URL; 
?>/expediente/crear/<?php echo $p['id_paciente']; ?>" 
                               title="Abrir Expediente Clínico" 
                               class="btn btn-primary" 
                               style="padding: 6px 12px; font-size: 12px; 
text-decoration: none; border-radius: 4px; display: inline-block;"> 
                               
➕
 Abrir Exp 
                            </a> 
                        <?php endif; ?> 
                        <a href="<?php echo BASE_URL; 
?>/paciente/eliminar/<?php echo $p['id_paciente']; ?>" 
                           title="Eliminar Paciente" 
                           class="btn" 
                           style="background-color: #dc3545; color: white; 
padding: 6px 12px; font-size: 12px; text-decoration: none; border-radius: 4px; 
display: inline-block;"> 
                           
�
�
 Eliminar 
                        </a> 
                    <?php else: ?> 
                        <form method="POST" action="<?php echo BASE_URL; 
?>/paciente/reactivar/<?php echo $p['id_paciente']; ?>" style="display: 
inline;"> 
                            <button type="submit" 
                                    title="Reactivar Paciente" 
                                    class="btn" 
                                    style="background-color: #28a745; color: 
white; padding: 6px 12px; font-size: 12px; border: none; border-radius: 4px; 
cursor: pointer;"> 
                                
♻
 Reactivar 
                            </button> 
                        </form> 
                    <?php endif; ?> 
                </td> 
            </tr> 
            <?php endforeach; ?> 
            
            <?php if(empty($pacientes)): ?> 
            <tr> 
                <td colspan="7" style="text-align: center; padding: 20px; 
color: #666;"> 
                    <?php if (!empty($busquedaActual)): ?> 
                        No se encontraron pacientes que coincidan con la 
búsqueda. 
                    <?php else: ?> 
                        No hay pacientes <?php echo $verInactivos ? '' : 
'activos '; ?>registrados en el sistema. Haz clic en "+ Nuevo Paciente" para 
comenzar. 
                    <?php endif; ?> 
                </td> 
            </tr> 
            <?php endif; ?> 
        </tbody> 
    </table> 
</div> 
 
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
