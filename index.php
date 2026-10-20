<?php
session_start();
require_once "db.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once 'head.php'; ?>
    <title>Sistema de Gestión de Estudiantes - Control Académico</title>
</head>
<body>
    <!-- Barra de Navegación Superior -->
    <nav class="navbar navbar-custom mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-mortarboard-fill text-primary"></i> Control Académico
            </a>
            <div class="d-flex align-items-center gap-3">
                <?php if (isset($_SESSION['id_usuario'])): ?>
                    <span class="text-light small d-none d-md-inline">
                        <i class="bi bi-person-circle me-1 text-primary"></i>
                        Usuario: <strong><?= htmlspecialchars($_SESSION['nombre'] ?? 'Administrador', ENT_QUOTES, 'UTF-8') ?></strong>
                    </span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary btn-sm fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión (Log In)
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <!-- Requerimiento Obligatorio: Sección de Integrantes del Grupo -->
        <div class="team-card p-3 p-md-4 mb-4">
            <div class="row align-items-center">
                <div class="col-12 col-lg-3 mb-3 mb-lg-0">
                    <h2 class="h6 text-uppercase fw-bold text-primary mb-1">
                        <i class="bi bi-people-fill me-1"></i> Integrantes del grupo
                    </h2>
                    <p class="text-muted small mb-0">Desarrollo Web - PHP & MySQL</p>
                </div>
                <div class="col-12 col-lg-9">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <span class="member-badge">
                            <i class="bi bi-person-fill"></i>
                            <strong>Martin Tecun</strong> &bull; Carné: 5190-22-17145
                        </span>
                        <span class="member-badge">
                            <i class="bi bi-person-fill"></i>
                            <strong>Daniel Suarez Gil</strong> &bull; Carné: 5190-23-3629
                        </span>
                        <span class="member-badge">
                            <i class="bi bi-person-fill"></i>
                            <strong>Seily Herrera</strong> &bull; Carné: 5190-22-2228
                        </span>
                        <span class="member-badge">
                            <i class="bi bi-person-fill"></i>
                            <strong>Juan Garcia</strong> &bull; Carné: 5190-23-6107
                        </span>
                        <span class="member-badge">
                            <i class="bi bi-person-fill"></i>
                            <strong>Eber Rivera</strong> &bull; Carné: 5190-23-9331
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mensajes Flash de Sesión -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-custom alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0"></i>
                <div class="flex-grow-1"><?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-custom alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill fs-5 flex-shrink-0"></i>
                <div class="flex-grow-1"><?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Contenedor Principal de Registros -->
        <div class="card-custom">
            <div class="card-header-custom d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <div>
                    <h1 class="h5 fw-bold mb-0 text-dark">
                        <i class="bi bi-card-checklist text-primary me-2"></i> Lista General de Estudiantes
                    </h1>
                    <small class="text-muted">Consulta y administración de expedientes académicos</small>
                </div>
                <?php if (isset($_SESSION['id_usuario'])): ?>
                    <a href="nuevo.php" class="btn btn-success btn-sm fw-semibold">
                        <i class="bi bi-plus-lg me-1"></i> Nuevo Estudiante
                    </a>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="table table-custom table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>Correo Electrónico</th>
                            <th>Aficiones</th>
                            <th class="text-center" style="min-width: 190px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT id_alumno, nombres, apellidos, correo, aficiones FROM alumno ORDER BY id_alumno DESC");
                        $hayRegistros = false;

                        while ($row = $stmt->fetch()):
                            $hayRegistros = true;
                            $idSeguro = (int)$row['id_alumno'];
                        ?>
                            <tr>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars($row['nombres'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['apellidos'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <a href="mailto:<?= htmlspecialchars($row['correo'], ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($row['correo'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 250px;">
                                        <?= htmlspecialchars($row['aficiones'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="action-btn-group justify-content-center">
                                        <!-- Enlace directo seguro pasando el identificador único por parámetro sin entrada manual -->
                                        <a href="ver.php?id_alumno=<?= $idSeguro ?>" class="btn btn-outline-info btn-sm-action" title="Ver detalles">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>

                                        <?php if (isset($_SESSION['id_usuario'])): ?>
                                            <a href="editar_db.php?id_alumno=<?= $idSeguro ?>" class="btn btn-outline-warning btn-sm-action text-dark" title="Editar registro">
                                                <i class="bi bi-pencil-square"></i> Editar
                                            </a>
                                            <a href="delete.php?id_alumno=<?= $idSeguro ?>" class="btn btn-outline-danger btn-sm-action" title="Eliminar registro">
                                                <i class="bi bi-trash3"></i> Eliminar
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                        <?php if (!$hayRegistros): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No hay estudiantes registrados en la base de datos.
                                    <?php if (isset($_SESSION['id_usuario'])): ?>
                                        <br>
                                        <a href="nuevo.php" class="btn btn-primary btn-sm mt-2">
                                            <i class="bi bi-plus-lg me-1"></i> Agregar el primer estudiante
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pie de Página -->
    <footer>
        <div class="container text-center">
            <small>&copy; <?= date('Y') ?> Sistema de Gestión de Estudiantes &bull; Entrega Académica &bull; Bootstrap 5 & PHP</small>
        </div>
    </footer>
</body>
</html>
