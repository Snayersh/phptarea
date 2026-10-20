<?php
session_start();
require_once "db.php";

// Validación de parámetro id_alumno o id
$id_param = $_GET['id_alumno'] ?? $_GET['id'] ?? null;
if ($id_param === null || !is_numeric($id_param)) {
    $_SESSION['error'] = "No se especificó un identificador de alumno válido.";
    header('Location: index.php');
    exit();
}

$stmt = $pdo->prepare("SELECT id_alumno, nombres, apellidos, correo, aficiones FROM alumno WHERE id_alumno = :idx");
$stmt->execute([':idx' => $id_param]);
$alumno = $stmt->fetch();

if ($alumno === false) {
    $_SESSION['error'] = "El alumno solicitado no existe en el sistema.";
    header('Location: index.php');
    exit();
}

// Consulta de cursos asociados
$stmtCursos = $pdo->prepare("
    SELECT ac.anio, c.nombre 
    FROM alumno_cursos ac 
    INNER JOIN cursos c ON c.id_curso = ac.id_curso 
    WHERE ac.id_alumno = :idx 
    ORDER BY ac.anio DESC, c.nombre ASC
");
$stmtCursos->execute([':idx' => $id_param]);
$cursos = $stmtCursos->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once 'head.php'; ?>
    <title>Detalles del Estudiante - <?= htmlspecialchars($alumno['nombres'] . ' ' . $alumno['apellidos'], ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body>
    <nav class="navbar navbar-custom mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <div class="brand-icon">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <span>Control Académico</span>
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="index.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left"></i> Volver a la Lista
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="card-custom">
                    <div class="card-header-custom d-flex justify-content-between align-items-center">
                        <h2 class="h5 fw-bold mb-0 text-dark">
                            <i class="bi bi-person-badge text-primary me-2"></i> Expediente del Estudiante
                        </h2>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill font-monospace">
                            ID: #<?= (int)$alumno['id_alumno'] ?>
                        </span>
                    </div>

                    <div class="p-4 p-md-5">
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="text-muted small fw-semibold text-uppercase tracking-wider">Nombres</label>
                                <p class="fs-5 fw-bold mb-0 text-dark"><?= htmlspecialchars($alumno['nombres'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small fw-semibold text-uppercase tracking-wider">Apellidos</label>
                                <p class="fs-5 fw-bold mb-0 text-dark"><?= htmlspecialchars($alumno['apellidos'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small fw-semibold text-uppercase tracking-wider">Correo Electrónico</label>
                                <p class="fs-6 mb-0">
                                    <a href="mailto:<?= htmlspecialchars($alumno['correo'], ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none fw-medium">
                                        <i class="bi bi-envelope me-1 text-primary"></i><?= htmlspecialchars($alumno['correo'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small fw-semibold text-uppercase tracking-wider">Aficiones y Pasatiempos</label>
                                <p class="fs-6 mb-0 text-secondary"><?= nl2br(htmlspecialchars($alumno['aficiones'], ENT_QUOTES, 'UTF-8')) ?></p>
                            </div>
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        <h3 class="h6 fw-bold text-dark mb-3">
                            <i class="bi bi-award text-warning me-2"></i> Cursos y Certificaciones IT
                        </h3>

                        <?php if (count($cursos) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-custom table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width: 130px;">Año</th>
                                            <th>Nombre del Curso / Certificación</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cursos as $cur): 
                                            $anioDisplay = (int)$cur['anio'];
                                            if ($anioDisplay >= 0 && $anioDisplay <= 99) {
                                                $anioDisplay += 2000;
                                            }
                                        ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-1 rounded-pill">
                                                        <?= htmlspecialchars((string)$anioDisplay, ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                </td>
                                                <td class="fw-medium text-dark">
                                                    <?= htmlspecialchars($cur['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light border text-muted py-3">
                                <i class="bi bi-info-circle me-1"></i> Este estudiante no tiene cursos o certificaciones asociadas.
                            </div>
                        <?php endif; ?>

                        <div class="mt-4 pt-3 d-flex gap-2">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Regresar a la Lista
                            </a>
                            <?php if (isset($_SESSION['id_usuario'])): ?>
                                <a href="editar_db.php?id_alumno=<?= (int)$alumno['id_alumno'] ?>" class="btn btn-warning">
                                    <i class="bi bi-pencil-square me-1"></i> Editar Estudiante
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container text-center">
            <small>&copy; <?= date('Y') ?> Sistema de Registro de Estudiantes. Todos los derechos reservados.</small>
        </div>
    </footer>
</body>
</html>
