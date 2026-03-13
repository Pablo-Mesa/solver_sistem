<?php
// Configuración de la conexión (Datos por defecto de WAMP)
$host    = 'localhost';
$db      = 'solver_16022026'; // <-- CAMBIA ESTO por el nombre que creaste en phpMyAdmin
$user    = 'root';
$pass    = ''; // En WAMP, por defecto la contraseña de root está vacía
$charset = 'utf8mb4';

// El DSN (Data Source Name) define el tipo de base de datos y la ubicación
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Opciones adicionales para mayor seguridad y control de errores
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza errores si algo falla
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve los datos como arreglos asociativos
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Desactiva la emulación para mayor seguridad SQL
];

try {
    // Creamos la conexión global $pdo
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Si la conexión falla, detiene todo y muestra el error
    die("❌ Error de conexión a la base de datos: " . $e->getMessage());
}