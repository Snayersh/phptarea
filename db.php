<?php
// Configuración de conexión a la base de datos MariaDB / MySQL
$host = '127.0.0.1';
$port = '3306';
$dbname = 'alumnos';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("Error al conectar con la base de datos: " . htmlspecialchars($e->getMessage()));
}
