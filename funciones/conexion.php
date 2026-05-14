<?php
/**
 * Lucina - Comparador Energético (v1.0 Demo)
 * Módulo: Conexión a Base de Datos
 */

// Intentar cargar configuración externa
$config = require __DIR__ . '/../config.php';

// Configuración de la base de datos
$host     = $config['host'];
$dbname   = $config['dbname'];
$username = $config['username'];
$password = $config['password'];

// Intentar conectar con la base de datos
try {
    // Crear conexión PDO (más seguro que mysqli)
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Configurar PDO para que lance excepciones en caso de error
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
    // Si hay error, mostrar mensaje
    die("Error de conexión: " . $e->getMessage());
}
?>
