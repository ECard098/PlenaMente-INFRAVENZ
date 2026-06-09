<?php
session_start();

// Definimos la ruta base de todo el sistema
define('BASE_URL', 'http://localhost/plenamente-infravenz/public');

// Mostrar errores en modo desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Requerir archivos base del MVC
require_once '../app/Config/Database.php';
require_once '../app/Core/Controller.php';
require_once '../app/Core/App.php';

// Inicializar la Aplicación (El Enrutador)
$app = new \App\Core\App();
?>