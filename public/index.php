<?php
// Ubicación: public/index.php

// 1. Mostrar errores en modo desarrollo (Quitar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Autocarga manual temporal (Más adelante podemos usar Composer PSR-4)
require_once '../app/Config/Database.php';

use App\Config\Database;

// 3. Prueba rápida de conexión a la Base de Datos
$db = new Database();
$conexion = $db->getConnection();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlenaMente - INFRAVENZ</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; color: #333; text-align: center; padding: 50px; }
        .success { color: #28a745; font-weight: bold; padding: 20px; border: 2px solid #28a745; border-radius: 10px; display: inline-block; background-color: #eafaf1;}
    </style>
</head>
<body>
    <h1>Bienvenido a PlenaMente</h1>
    <h2>Sistema Institucional de Atención y Seguimiento Psicológico</h2>
    
    <?php if($conexion): ?>
        <div class="success">
            ✅ ¡Excelente! La conexión a la base de datos MariaDB (plenamente_bd) ha sido exitosa.
        </div>
    <?php else: ?>
        <div style="color:red;">
            ❌ Hubo un error al conectar con la base de datos.
        </div>
    <?php endif; ?>
    
    <p>El núcleo de la aplicación está listo para recibir el sistema de enrutamiento y autenticación.</p>
</body>
</html>