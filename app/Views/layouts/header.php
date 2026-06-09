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
        <h1>🧠 Sistema Psicológico INFRAVENZ</h1>
        <div>Usuario: <?php echo $_SESSION['usuario_nombre'] ?? 'Invitado'; ?></div>
    </header>

    <div class="container">
        <nav class="sidebar">
            <a href="<?php echo BASE_URL; ?>/dashboard" class="menu-item active">📊 Dashboard</a> 
            <a href="<?php echo BASE_URL; ?>/paciente" class="menu-item">👥 Pacientes</a>
          <!--  <a href="<?php echo BASE_URL; ?>/citas" class="menu-item">📅 Citas</a> -->
          <!--  <a href="<?php echo BASE_URL; ?>/sesiones" class="menu-item">📋 Consultas</a> -->
          <!--  <a href="<?php echo BASE_URL; ?>/expedientes" class="menu-item">📁 Expedientes</a> -->
          <!--  <a href="<?php echo BASE_URL; ?>/reportes" class="menu-item">📄 Informes</a> -->
            <a href="<?php echo BASE_URL; ?>/usuario" class="menu-item">🔐 Usuarios</a>
          <!--  <a href="<?php echo BASE_URL; ?>/configuracion" class="menu-item">⚙️ Configuración</a> -->
            <a href="<?php echo BASE_URL; ?>/auth/logout" class="menu-item" style="color: #d32f2f; margin-top: 20px; border-top: 1px solid var(--gris-claro);">
                🚪 Cerrar Sesión
            </a>
        </nav>

        <main class="main-content">