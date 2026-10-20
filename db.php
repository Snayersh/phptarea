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
        // 1. Crear la tabla alumno_cursos si no existe
        $pdo->exec("CREATE TABLE IF NOT EXISTS alumno_cursos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            alumno_id INT NOT NULL,
            curso_id INT NOT NULL,
            anio INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_alumno FOREIGN KEY (alumno_id) REFERENCES alumnos(id) ON DELETE CASCADE,
            CONSTRAINT fk_curso FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // 2. Verificar el tipo de datos de 'anio' y corregirlo si es necesario
        $stmt = $pdo->query("DESCRIBE alumno_cursos anio");
        $col = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si 'anio' no es 'int' o es 'tinyint' (que da error de overflow con 2026)
        if ($col && $col['Type'] !== 'int') {
            $pdo->exec("ALTER TABLE alumno_cursos MODIFY anio INT NOT NULL");
        }
    } catch (Exception $e) {
        // En caso de que no haya permisos de DDL
        error_log("Error al verificar/crear tabla alumno_cursos: " . $e->getMessage());
    }
} catch (PDOException $e) {
    die("Error al conectar con la base de datos: " . htmlspecialchars($e->getMessage()));
}
