<?php

/**
 * MiniCRM Web Installer
 *
 * Entry point para el instalador web.
 * Este archivo debe ser accesible públicamente durante la instalación
 * y se desactiva automáticamente una vez completada.
 */

// Configuración de errores para instalación
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Definir path base
$basePath = dirname(__DIR__);

// Cargar el instalador
require_once $basePath . '/installer/InstallerApp.php';
require_once $basePath . '/installer/helpers/RequirementsChecker.php';
require_once $basePath . '/installer/helpers/DatabaseManager.php';
require_once $basePath . '/installer/helpers/EnvManager.php';
require_once $basePath . '/installer/helpers/InstallProcess.php';

// Manejar petición AJAX para probar conexión de BD
if (isset($_GET['action']) && $_GET['action'] === 'test' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $config = [
        'driver' => $_POST['db_driver'] ?? 'mysql',
        'host' => $_POST['db_host'] ?? 'localhost',
        'port' => $_POST['db_port'] ?? '3306',
        'database' => $_POST['db_database'] ?? '',
        'username' => $_POST['db_username'] ?? '',
        'password' => $_POST['db_password'] ?? '',
    ];

    $dbManager = new DatabaseManager();
    $result = $dbManager->testConnection($config);

    echo json_encode($result);
    exit;
}

// Iniciar instalador
$installer = new InstallerApp($basePath);
$installer->run();
