<?php
require_once __DIR__ . '/config/config.php';

try {
    // Crear conexión sin seleccionar base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS);

    if (!$conn) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }

    // Crear la base de datos si no existe
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if (!mysqli_query($conn, $sql)) {
        throw new Exception("Error creating database: " . mysqli_error($conn));
    }

    // Seleccionar la base de datos
    if (!mysqli_select_db($conn, DB_NAME)) {
        throw new Exception("Error selecting database: " . mysqli_error($conn));
    }

    // Leer y ejecutar el schema.sql
    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    
    // Dividir el SQL en declaraciones individuales
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    // Ejecutar cada declaración
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            if (!mysqli_query($conn, $statement)) {
                throw new Exception("Error executing SQL: " . mysqli_error($conn) . "\nStatement: " . $statement);
            }
        }
    }

    // Cerrar la conexión
    mysqli_close($conn);

    echo json_encode([
        'success' => true,
        'message' => 'Base de datos instalada correctamente. Ahora puedes:
1. Configurar el archivo .env con tus credenciales de MySQL
2. Registrar un nuevo usuario administrador
3. Iniciar sesión en el sistema'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al instalar la base de datos: ' . $e->getMessage()
    ]);
}
?>
