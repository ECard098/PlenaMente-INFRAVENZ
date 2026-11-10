<!DOCTYPE html> 
<html lang="es"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Plenamente - INFRAVENZ</title> 
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css"> 
</head> 
<body> 
    <header class="header"> 
        <h1>🧠 Aplicación Psicológica INFRAVENZ</h1> 
        <div>Usuario: <?php echo $_SESSION['usuario_nombre'] ?? 'Invitado'; ?></div> 
    </header> 


    <div class="container"> 
        <?php  
        // Detectar el módulo actual desde la URL (por ejemplo: /asistencia, /paciente, etc.) 
        $uri = $_SERVER['REQUEST_URI'];                  
        // Aseguramos que la sesión esté iniciada para poder leer el rol 
        if (session_status() == PHP_SESSION_NONE) { 
            session_start(); 
        } 


        // El menu se construye con la misma matriz de permisos que aplica los accesos 
        require_once __DIR__ . '/../../Core/Permisos.php'; 
        ?>          
        <nav class="sidebar"> 
            <a href="<?php echo BASE_URL; ?>/dashboard" class="menu-item">📊 
Dashboard</a>  


            <?php if (App\Core\Permisos::puede('pacientes')): ?> 
                <a href="<?php echo BASE_URL; ?>/paciente" class="menu-item">👥 
Pacientes</a> 
            <?php endif; ?> 


            <?php if (App\Core\Permisos::puede('citas')): ?> 
                <a href="<?php echo BASE_URL; ?>/cita" class="menu-item">📅 Citas</a> 
            <?php endif; ?> 


            <?php if (App\Core\Permisos::puede('recordatorios')): ?> 
                <a href="<?php echo BASE_URL; ?>/recordatorio" class="menu-item">🔔 
Recordatorios</a> 
            <?php endif; ?> 



            <?php if (App\Core\Permisos::puede('asistencias')): ?> 
                <a href="<?php echo BASE_URL; ?>/asistencia" class="menu-item">📝 
Asistencias</a> 
            <?php endif; ?> 



            <?php if (App\Core\Permisos::puede('expedientes')): ?> 
                <a href="<?php echo BASE_URL; ?>/expediente" class="menu-item">📁 
Expedientes</a> 
            <?php endif; ?> 


            <?php if (App\Core\Permisos::puede('reportes')): ?> 
                <a href="<?php echo BASE_URL; ?>/reportes" class="menu-item">📄 
Informes</a>  
            <?php endif; ?> 


            <?php if (App\Core\Permisos::puede('usuarios')): ?> 
                <a href="<?php echo BASE_URL; ?>/usuario" class="menu-item">🔐 
Usuarios</a> 
            <?php endif; ?> 


            <?php if (App\Core\Permisos::puede('roles')): ?> 
                <a href="<?php echo BASE_URL; ?>/rol" class="menu-item">🛡 Roles y 
Permisos</a> 
            <?php endif; ?>                 
            <a href="<?php echo BASE_URL; ?>/auth/logout" class="menu-item" style="color: 
#d32f2f; margin-top: 20px; border-top: 1px solid var(--gris-claro);"> 
                🚪 Cerrar Sesión 
            </a> 
        </nav> 
        <main class="main-content"> 
