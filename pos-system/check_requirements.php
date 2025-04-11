<?php
require_once __DIR__ . '/config/config.php';

echo "Verificando requisitos del sistema:\n\n";

// Verificar versión de PHP
echo "PHP Version: " . PHP_VERSION . "\n";

// Verificar extensiones requeridas
$required_extensions = [
    'pdo',
    'pdo_mysql',
    'mbstring',
    'json'
];

echo "\nExtensiones PHP requeridas:\n";
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✓ {$ext} está instalada\n";
    } else {
        echo "✗ {$ext} NO está instalada\n";
    }
}

// Verificar configuración de PHP
echo "\nConfiguración PHP:\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "error_reporting: " . ini_get('error_reporting') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";

// Crear directorio uploads si no existe
$uploadsDir = __DIR__ . '/uploads';
if (!file_exists($uploadsDir)) {
    mkdir($uploadsDir, 0775, true);
    echo "\nDirectorio uploads creado.\n";
}

// Verificar permisos de directorio
echo "\nPermisos de directorios:\n";
$directories = [
    __DIR__,
    __DIR__ . '/config',
    __DIR__ . '/uploads'
];

foreach ($directories as $dir) {
    if (file_exists($dir)) {
        echo "{$dir}: " . substr(sprintf('%o', fileperms($dir)), -4) . "\n";
    } else {
        echo "{$dir}: No existe\n";
    }
}

// Verificar conexión a la base de datos
echo "\nProbando conexión a la base de datos:\n";
try {
    $dsn = "mysql:host=" . DB_HOST;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    echo "✓ Conexión exitosa a MySQL\n";

    // Intentar crear la base de datos si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    echo "✓ Base de datos " . DB_NAME . " verificada/creada\n";
} catch (PDOException $e) {
    echo "✗ Error de conexión: " . $e->getMessage() . "\n";
}

// Mostrar resumen de problemas encontrados
$problems = [];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $problems[] = "Falta la extensión PHP: {$ext}";
    }
}

if (!empty($problems)) {
    echo "\nProblemas encontrados que necesitan ser resueltos:\n";
    foreach ($problems as $problem) {
        echo "- {$problem}\n";
    }
    echo "\nPor favor, instale las extensiones faltantes antes de continuar.\n";
    echo "Para instalar pdo_mysql: sudo apt-get install php-mysql\n";
}
?>
