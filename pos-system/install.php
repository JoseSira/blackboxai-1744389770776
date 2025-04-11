<?php
require_once __DIR__ . '/config/config.php';

try {
    // Crear la base de datos si no existe
    $pdo = new PDO(
        "mysql:host=" . DB_HOST,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE " . DB_NAME);

    // Leer y ejecutar el schema.sql
    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    $pdo->exec($sql);

    echo json_encode([
        'success' => true,
        'message' => 'Base de datos instalada correctamente'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al instalar la base de datos: ' . $e->getMessage()
    ]);
}
?>
