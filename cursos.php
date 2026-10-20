<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Acceso denegado']);
    exit();
}

if (!isset($_GET['term']) || trim($_GET['term']) === '') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([]);
    exit();
}

require_once "db.php";

header('Content-Type: application/json; charset=utf-8');

$stmt = $pdo->prepare('SELECT nombre FROM cursos WHERE nombre LIKE :texto ORDER BY nombre ASC LIMIT 10');
$stmt->execute([':texto' => '%' . trim($_GET['term']) . '%']);

$cursos = [];
while ($row = $stmt->fetch()) {
    $cursos[] = $row['nombre'];
}

echo json_encode($cursos);
exit();
