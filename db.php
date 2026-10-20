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

    // Asegurar que la columna anio soporte 4 dígitos sin desbordamiento (Out of range 1264)
    try {
        $pdo->exec("ALTER TABLE alumno_cursos MODIFY anio INT NOT NULL");
    } catch (Exception $eIgnored) {
        // En caso de que no haya permisos de DDL o la tabla aún no exista
    }
} catch (PDOException $e) {
    die("Error al conectar con la base de datos: " . htmlspecialchars($e->getMessage()));
}
