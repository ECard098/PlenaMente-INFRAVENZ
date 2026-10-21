<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php 
$estadoCaso = $expediente['estado_caso'] ?? 'Activo'; 
$estadosPosibles = ['Activo', 'Seguimiento', 'Cerrado']; 
$coloresEstado = [ 
    'Activo'      => 'background: #e8f5e9; color: #1b5e20;', 
    'Seguimiento' => 'background: #fff3cd; color: #856404;', 
    'Cerrado'     => 'background: #e9ecef; color: #555;' 
]; 
$colorEstado = $coloresEstado[$estadoCaso] ?? $coloresEstado['Activo']; 
?> 

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;"> 
    <h2 style="color: var(--violeta-principal); margin: 0;"> 
        Expediente Clínico: EXP-<?php echo str_pad($expediente['id_expediente'], 5, "0", STR_PAD_LEFT); ?> 
    </h2> 
    <div style="display: flex; gap: 8px; align-items: center;"> 
        <?php if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1): ?> 
            <a href="<?php echo BASE_URL; ?>/reportes/tecnico/<?php echo $expediente['id_expediente']; ?>" class="btn btn-primary" style="background-color: var(--violeta-principal); border: none; text-decoration: none; color: white; padding: 8px 14px;"> 
                📄 Generar Informe 
            </a> 
        <?php endif; ?> 
        <a href="<?php echo BASE_URL; ?>/paciente" class="btn btn-secondary">Volver a Pacientes</a> 
    </div> 
</div> 

<?php if (isset($_GET['success']) && $_GET['success'] == 'sesion_guardada'): ?> 
    <div style="padding: 12px; background-color: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 20px; font-weight: bold;"> 
        ✅ Sesión clínica registrada en el historial correctamente. 
    </div> 
<?php endif; ?> 

<?php if (!empty($flash)): ?> 
    <?php foreach ($flash as $tipo => $mensajes): ?> 
        <?php foreach ($mensajes as $mensaje): ?> 
            <div style="padding: 12px; background-color: <?php echo $tipo == 'success' ? '#d4edda' : ($tipo == 'warning' ? '#fff3cd' : '#f8d7da'); ?>; color: <?php echo $tipo == 'success' ? '#155724' : ($tipo == 'warning' ? '#856404' : '#721c24'); ?>; border-radius: 4px; margin-bottom: 16px; font-weight: bold;"> 
                <?php echo htmlspecialchars($mensaje); ?> 
            </div> 
        <?php endforeach; ?> 
    <?php endforeach; ?> 
<?php endif; ?> 

<style> 
    .fichas-sesiones { 
        display: flex; 
        flex-direction: column; 
        gap: 12px; 
    } 
    .ficha-sesion { 
        overflow: hidden; 
    } 
    .ficha-sesion > summary { 
        list-style: none; 
        user-select: none; 
    } 
    .ficha-sesion > summary::-webkit-details-marker { 
        display: none; 
    } 
    .ficha-sesion .flecha { 
        display: inline-block; 
        font-size: 14px; 
        font-weight: bold; 
        color: #999; 
        transition: transform 0.25s ease; 
        line-height: 1; 
    } 
    .ficha-sesion[open] .flecha { 
        transform: rotate(180deg); 
        color: var(--violeta-principal); 
    } 
    .ficha-sesion[open] { 
        border-color: var(--violeta-principal); 
        box-shadow: 0 2px 8px rgba(124, 58, 237, 0.12); 
    } 

    /* Pestañas: ambos paneles comparten el mismo espacio */ 
    .tab-bar { 
        display: flex; 
        gap: 6px; 
        border-bottom: 2px solid #eee; 
        margin-bottom: 18px; 
        flex-wrap: wrap; 
    } 
    .tab-btn { 
        background: #f3f4f6; 
        border: 1px solid #e5e7eb; 
        border-bottom: none; 
        padding: 10px 18px; 
        font-size: 13px; 
        font-weight: bold; 
        color: #6b7280; 
        border-radius: 8px 8px 0 0; 
        cursor: pointer; 
        transition: all 0.2s ease; 
    } 
    .tab-btn:hover { 
        background: #e9e7f7; 
        color: var(--violeta-principal); 
    } 
    .tab-btn.active { 
        background: var(--violeta-principal); 
        color: white; 
        border-color: var(--violeta-principal); 
    } 
</style> 

<div style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 2fr); gap: 25px; align-items: start;"> 
    
    <div class="card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-top: 4px solid var(--violeta-principal);"> 
        <div style="text-align: center; margin-bottom: 20px;"> 
            <div style="width: 70px; height: 70px; background: #f0f0f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px auto; color: #666; font-size: 28px; font-weight: bold;"> 
                <?php echo strtoupper(substr($expediente['nombres'], 0, 1) . substr($expediente['apellidos'], 0, 1)); ?> 
            </div> 
            <h3 style="margin: 0; color: #333; font-size: 18px;"><?php echo htmlspecialchars($expediente['nombres'] . ' ' . $expediente['apellidos']); ?></h3> 
            <span style="display: inline-block; margin-top: 5px; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; background: <?php echo $expediente['tipo_paciente'] == 'Estudiante' ? '#e3f2fd; color: #0d47a1;' : '#e8f5e9; color: #1b5e20;'; ?>"> 
                <?php echo $expediente['tipo_paciente']; ?> 
            </span> 
        </div> 

        <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;"> 

        <div style="font-size: 14px; color: #555; line-height: 1.6;"> 
            <p><strong>NIE / DUI:</strong> <?php echo htmlspecialchars($expediente['nie_dui']); ?></p> 
            <p><strong>Género:</strong> <?php echo htmlspecialchars($expediente['genero'] ?? 'No especificado'); ?></p> 
            <p><strong>Edad:</strong> 
                <?php 
                    $cumpleanos = new DateTime($expediente['fecha_nacimiento']); 
                    $hoy = new DateTime(); 
                    $edad = $hoy->diff($cumpleanos); 
                    echo $edad->y . " años"; 
                ?> 
            </p> 
            <?php if($expediente['tipo_paciente'] == 'Estudiante'): ?> 
                <p><strong>Grado/Sección:</strong> <?php echo htmlspecialchars($expediente['grado_seccion'] ?? 'N/A'); ?></p> 
                <p><strong>Responsable:</strong> <?php echo htmlspecialchars($expediente['nombre_responsable'] ?? 'N/A'); ?></p> 
            <?php endif; ?> 
            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($expediente['telefono_contacto'] ?? 'N/A'); ?></p> 
            <p><strong>Correo:</strong> <?php echo htmlspecialchars($expediente['correo_paciente'] ?? 'N/A'); ?></p> 
            <p><strong>Apertura:</strong> <?php echo date('d/m/Y', strtotime($expediente['fecha_apertura'])); ?></p> 
            <p><strong>Estado del caso:</strong> 
                <span style="display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; <?php echo $colorEstado; ?>"> 
                    <?php echo htmlspecialchars($estadoCaso); ?> 
                </span> 
            </p> 
        </div> 

        <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;"> 

        <h4 style="margin: 0 0 5px 0; color: var(--violeta-principal); font-size: 14px;">Antecedentes Familiares</h4> 
        <p style="font-size: 13px; color: #666; background: #f9f9f9; padding: 8px; border-radius: 4px; margin: 0 0 15px 0; max-height: 100px; overflow-y: auto;"> 
            <?php echo !empty($expediente['antecedentes_familiares']) ? nl2br(htmlspecialchars($expediente['antecedentes_familiares'])) : '<i>Ninguno registrado</i>'; ?> 
        </p> 

        <h4 style="margin: 0 0 5px 0; color: var(--violeta-principal); font-size: 14px;">Antecedentes Médicos / Personales</h4> 
        <p style="font-size: 13px; color: #666; background: #f9f9f9; padding: 8px; border-radius: 4px; margin: 0 0 15px 0; max-height: 100px; overflow-y: auto;"> 
            <?php echo !empty($expediente['antecedentes_medicos']) ? nl2br(htmlspecialchars($expediente['antecedentes_medicos'])) : '<i>Ninguno registrado</i>'; ?> 
        </p> 

        <a href="<?php echo BASE_URL; ?>/expediente/editar/<?php echo $expediente['id_expediente']; ?>" class="btn" style="width: 100%; padding: 8px 12px; font-size: 13px; border: 1px solid var(--violeta-principal); color: var(--violeta-principal); background: white; text-decoration: none; border-radius: 4px; text-align: center;"> 
            ✏️ Editar Antecedentes 
        </a> 

        <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;"> 

        <h4 style="margin: 0 0 8px 0; color: var(--violeta-principal); font-size: 14px;">Estado del Caso</h4> 
        <form method="POST" action="<?php echo BASE_URL; ?>/expediente/cambiar_estado" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;"> 
            <input type="hidden" name="id_expediente" value="<?php echo $expediente['id_expediente']; ?>"> 
            <select name="estado_caso" style="flex: 1; min-width: 120px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px;"> 
                <?php foreach ($estadosPosibles as $opcion): ?> 
                    <option value="<?php echo $opcion; ?>" <?php echo ($estadoCaso === $opcion) ? 'selected' : ''; ?>><?php echo $opcion; ?></option> 
                <?php endforeach; ?> 
            </select> 
            <button type="submit" class="btn" style="background-color: var(--violeta-principal); border: none; color: white; padding: 8px 14px; border-radius: 4px; cursor: pointer; font-size: 13px;"> 
                Actualizar 
            </button> 
        </form> 
    </div> 

    <div class="card" style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);"> 
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; gap: 10px; flex-wrap: wrap;"> 
            <div> 
                <h3 style="margin: 0; color: #333;">Panel Clínico</h3> 
                <span style="font-size: 12px; color: #888;"><?php echo $total_sesiones; ?> sesión(es) registrada(s)</span> 
            </div> 
        </div> 

        <!-- Barra de pestañas --> 
        <div class="tab-bar"> 
            <button type="button" class="tab-btn active" data-tab="panel-sesiones">📅 Sesiones Clínicas</button> 
            <button type="button" class="tab-btn" data-tab="panel-historial">🕓 Historial de Cambios (<?php echo count($historial); ?>)</button> 
        </div> 

        <!-- Panel: Sesiones Clínicas --> 
        <div id="panel-sesiones" class="tab-panel"> 
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin-bottom: 18px;"> 
                <a href="<?php echo BASE_URL; ?>/expediente/ver/<?php echo $expediente['id_expediente']; ?>?orden=desc" class="btn" style="<?php echo ($orden === 'DESC') ? 'background-color: var(--violeta-principal); color: white; border: none;' : 'background-color: #e9ecef; color: #333; text-decoration: none;'; ?> padding: 8px 12px; font-size: 13px;"> 
                    ⬇️ Más recientes 
                </a> 
                <a href="<?php echo BASE_URL; ?>/expediente/ver/<?php echo $expediente['id_expediente']; ?>?orden=asc" class="btn" style="<?php echo ($orden === 'ASC') ? 'background-color: var(--violeta-principal); color: white; border: none;' : 'background-color: #e9ecef; color: #333; text-decoration: none;'; ?> padding: 8px 12px; font-size: 13px;"> 
                    ⬆️ Más antiguas 
                </a> 
                <a href="<?php echo BASE_URL; ?>/expediente/nueva_sesion/<?php echo $expediente['id_expediente']; ?>" class="btn btn-primary" style="padding: 8px 16px; font-size: 14px; background-color: var(--violeta-principal); border: none; border-radius: 4px; text-decoration: none; color: white;"> 
                    ➕ Registrar Sesión 
                </a> 
            </div> 

            <?php if (empty($sesiones)): ?> 
                <div style="text-align: center; padding: 40px; color: #888; background: #fafafa; border: 2px dashed #eee; border-radius: 6px;"> 
                    <p style="margin: 0 0 10px 0; font-size: 16px;">Este expediente aún no cuenta con sesiones clínicas.</p> 
                    <p style="margin: 0; font-size: 13px; color: #aaa;">Presione el botón "Registrar Sesión" para documentar la primera intervención.</p> 
                </div> 
            <?php else: ?> 
                <div class="fichas-sesiones"> 
                    <?php foreach ($sesiones as $sesion): ?> 
                        <?php $titulo_sesion = date('d/m/Y h:i A', strtotime($sesion['fecha_registro'])); ?> 
                        <details class="ficha-sesion" style="background: #fdfdfd; border: 1px solid #eaeaea; border-radius: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);"> 
                            <summary style="padding: 14px 16px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap;"> 
                                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; min-width: 0;"> 
                                    <span style="font-weight: bold; color: #333; word-break: break-word;">📅 Sesión <?php echo $titulo_sesion; ?></span> 
                                    <span style="background: #e3f2fd; color: #0d47a1; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; border: 1px solid #bbdefb; white-space: nowrap;"> 
                                        🏷️ <?php echo htmlspecialchars($sesion['tipo_consulta'] ?? 'Seguimiento'); ?> 
                                    </span> 
                                </div> 
                                <div style="display: flex; align-items: center; gap: 10px;"> 
                                    <a href="<?php echo BASE_URL; ?>/expediente/editar_sesion/<?php echo $sesion['id_sesion']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px; text-decoration: none; color: white; background-color: var(--violeta-principal); border: none; border-radius: 4px; white-space: nowrap;"> 
                                        ✏️ Editar 
                                    </a> 
                                    <span class="flecha">▾</span> 
                                </div> 
                            </summary> 

                            <div style="padding: 10px 15px 15px 15px; font-size: 14px; color: #444; display: grid; gap: 10px; border-top: 1px dashed #eee; min-width: 0;"> 
                                <p style="margin: 0; word-break: break-word; overflow-wrap: anywhere;"><strong>Observaciones Generales:</strong><br> 
                                    <span style="color: #666;"><?php echo nl2br(htmlspecialchars($sesion['observaciones_generales'])); ?></span> 
                                </p> 
                                <p style="margin: 0; word-break: break-word; overflow-wrap: anywhere;"><strong>Intervención Realizada:</strong><br> 
                                    <span style="color: #666;"><?php echo nl2br(htmlspecialchars($sesion['intervencion_realizada'])); ?></span> 
                                </p> 
                                <p style="margin: 0; word-break: break-word; overflow-wrap: anywhere;"><strong>Notas de Evolución:</strong><br> 
                                    <span style="color: #666;"><?php echo nl2br(htmlspecialchars($sesion['notas_evolucion'])); ?></span> 
                                </p> 

                                <?php $histSesion = isset($historial_sesiones) ? ($historial_sesiones[$sesion['id_sesion']] ?? []) : []; ?> 
                                <?php if (!empty($histSesion)): ?> 
                                    <details style="margin-top: 10px; font-size: 13px;"> 
                                        <summary style="cursor: pointer; color: var(--violeta-principal); font-weight: bold;"> 
                                            📋 Historial de cambios de esta sesión (<?php echo count($histSesion); ?>) 
                                        </summary> 
                                        <div style="overflow-x: auto; margin-top: 10px; max-width: 100%;"> 
                                            <table style="width: 100%; border-collapse: collapse; table-layout: fixed;"> 
                                                <thead> 
                                                    <tr style="border-bottom: 2px solid #eee; color: #555; font-size: 12px;"> 
                                                        <th style="padding: 8px; width: 22%;">Fecha</th> 
                                                        <th style="padding: 8px; width: 18%;">Campo</th> 
                                                        <th style="padding: 8px; width: 20%;">Psicólogo</th> 
                                                        <th style="padding: 8px; width: 20%;">Anterior</th> 
                                                        <th style="padding: 8px; width: 20%;">Nuevo</th> 
                                                    </tr> 
                                                </thead> 
                                                <tbody> 
                                                    <?php foreach ($histSesion as $h): ?> 
                                                        <tr style="border-bottom: 1px solid #eee;"> 
                                                            <td style="padding: 8px; white-space: nowrap;"><?php echo date('d/m/Y h:i A', strtotime($h['fecha_modificacion'])); ?></td> 
                                                            <td style="padding: 8px; font-weight: bold; word-break: break-word;"><?php echo htmlspecialchars($h['campo']); ?></td> 
                                                            <td style="padding: 8px; word-break: break-word;"><?php echo htmlspecialchars($h['nombre_completo']); ?></td> 
                                                            <td style="padding: 8px; color: #856404; word-break: break-word; overflow-wrap: anywhere;"><?php echo $h['valor_anterior'] ? nl2br(htmlspecialchars($h['valor_anterior'])) : '<i style="color:#aaa;">Vacío</i>'; ?></td> 
                                                            <td style="padding: 8px; color: #155724; word-break: break-word; overflow-wrap: anywhere;"><?php echo $h['valor_nuevo'] ? nl2br(htmlspecialchars($h['valor_nuevo'])) : '<i style="color:#aaa;">Vacío</i>'; ?></td> 
                                                        </tr> 
                                                    <?php endforeach; ?> 
                                                </tbody> 
                                            </table> 
                                        </div> 
                                    </details> 
                                <?php endif; ?> 
                            </div> 
                        </details> 
                    <?php endforeach; ?> 
                </div> 
            <?php endif; ?> 
        </div><!-- /panel-sesiones --> 

        <!-- Panel: Historial de Cambios de Antecedentes --> 
        <div id="panel-historial" class="tab-panel" style="display: none;"> 
            <?php if (empty($historial)): ?> 
                <div style="text-align: center; padding: 40px; color: #888; background: #fafafa; border: 2px dashed #eee; border-radius: 6px;"> 
                    <p style="margin: 0 0 10px 0; font-size: 16px;">Aún no hay cambios registrados en los antecedentes.</p> 
                    <p style="margin: 0; font-size: 13px; color: #aaa;">Cuando edite los antecedentes, los cambios aparecerán aquí.</p> 
                </div> 
            <?php else: ?> 
                <div style="overflow-x: auto; max-width: 100%;"> 
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; table-layout: fixed;"> 
                        <thead> 
                            <tr style="border-bottom: 2px solid #eee; color: #555;"> 
                                <th style="padding: 10px; width: 20%;">Fecha</th> 
                                <th style="padding: 10px; width: 16%;">Campo</th> 
                                <th style="padding: 10px; width: 18%;">Psicólogo</th> 
                                <th style="padding: 10px; width: 23%;">Valor Anterior</th> 
                                <th style="padding: 10px; width: 23%;">Valor Nuevo</th> 
                            </tr> 
                        </thead> 
                        <tbody> 
                            <?php foreach ($historial as $h): ?> 
                                <tr style="border-bottom: 1px solid #eee;"> 
                                    <td style="padding: 10px; white-space: nowrap;"><?php echo date('d/m/Y h:i A', strtotime($h['fecha_modificacion'])); ?></td> 
                                    <td style="padding: 10px; font-weight: bold; word-break: break-word;"><?php echo htmlspecialchars($h['tipo_cambio']); ?></td> 
                                    <td style="padding: 10px; word-break: break-word;"><?php echo htmlspecialchars($h['nombre_completo']); ?></td> 
                                    <td style="padding: 10px; color: #856404; word-break: break-word; overflow-wrap: anywhere;"><?php echo $h['valor_anterior'] ? nl2br(htmlspecialchars($h['valor_anterior'])) : '<i style="color:#aaa;">Vacío</i>'; ?></td> 
                                    <td style="padding: 10px; color: #155724; word-break: break-word; overflow-wrap: anywhere;"><?php echo $h['valor_nuevo'] ? nl2br(htmlspecialchars($h['valor_nuevo'])) : '<i style="color:#aaa;">Vacío</i>'; ?></td> 
                                </tr> 
                            <?php endforeach; ?> 
                        </tbody> 
                    </table> 
                </div> 
            <?php endif; ?> 
        </div><!-- /panel-historial --> 
    </div> 
</div> 

<!-- Navegación entre expedientes (anterior / siguiente) --> 
<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; padding-top: 16px; border-top: 1px solid #eee;"> 
    <?php if (!empty($vecinos['anterior'])): ?> 
        <a href="<?php echo BASE_URL; ?>/expediente/ver/<?php echo $vecinos['anterior']; ?><?php echo ($orden === 'ASC') ? '?orden=asc' : ''; ?>" class="btn btn-secondary" style="text-decoration: none;"> 
            ← Anterior (EXP-<?php echo str_pad($vecinos['anterior'], 5, '0', STR_PAD_LEFT); ?>) 
        </a> 
    <?php else: ?> 
        <span></span> 
    <?php endif; ?> 

    <?php if (!empty($vecinos['siguiente'])): ?> 
        <a href="<?php echo BASE_URL; ?>/expediente/ver/<?php echo $vecinos['siguiente']; ?><?php echo ($orden === 'ASC') ? '?orden=asc' : ''; ?>" class="btn btn-secondary" style="text-decoration: none;"> 
            Siguiente (EXP-<?php echo str_pad($vecinos['siguiente'], 5, '0', STR_PAD_LEFT); ?>) → 
        </a> 
    <?php endif; ?> 
</div> 

<script> 
// Pestañas: alternar entre Sesiones y Historial de Cambios
document.querySelectorAll('.tab-btn').forEach(function (btn) { 
    btn.addEventListener('click', function () { 
        var destino = btn.getAttribute('data-tab'); 
        document.querySelectorAll('.tab-btn').forEach(function (b) { b.classList.remove('active'); }); 
        document.querySelectorAll('.tab-panel').forEach(function (p) { p.style.display = 'none'; }); 
        btn.classList.add('active'); 
        var panel = document.getElementById(destino); 
        if (panel) { panel.style.display = 'block'; } 
    }); 
}); 

// Modo fichas: al abrir una sesión se cierran las demás
document.querySelectorAll('.fichas-sesiones').forEach(function (contenedor) { 
    contenedor.querySelectorAll(':scope > .ficha-sesion').forEach(function (ficha) { 
        ficha.addEventListener('toggle', function () { 
            if (ficha.open) { 
                contenedor.querySelectorAll(':scope > .ficha-sesion[open]').forEach(function (otra) { 
                    if (otra !== ficha) { 
                        otra.open = false; 
                    } 
                }); 
            } 
        }); 
    }); 
}); 
</script> 

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>