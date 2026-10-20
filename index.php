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
    <!-- Barra de Navegación Glassmorphic -->
    <nav class="navbar navbar-custom mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <div class="brand-icon">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <span>Control Académico</span>
            </a>
            <div class="d-flex align-items-center gap-3">
                <?php if (isset($_SESSION['id_usuario'])): ?>
                    <div class="user-pill d-none d-md-inline-flex">
                        <span class="status-dot"></span>
                        <span>Usuario: <strong><?= htmlspecialchars($_SESSION['nombre'] ?? 'Administrador', ENT_QUOTES, 'UTF-8') ?></strong></span>
                    </div>
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
        <div class="team-card mb-4">
            <div class="row align-items-center">
                <div class="col-12 col-xl-3 mb-3 mb-xl-0">
                    <div class="team-badge-header mb-2">
                        <i class="bi bi-code-slash"></i> Desarrollo Web - PHP & MySQL
                    </div>
                    <h2 class="h5 fw-bold text-dark mb-0">
                        Integrantes del grupo
                    </h2>
                </div>
                <div class="col-12 col-xl-9">
                    <div class="d-flex flex-wrap gap-2 justify-content-xl-end">
                        <span class="member-badge">
                            <span class="member-avatar">MT</span>
                            <strong>Martin Tecun</strong>
                            <span class="badge-carne">&bull; 5190-22-17145</span>
                        </span>
                        <span class="member-badge">
                            <span class="member-avatar">DS</span>
                            <strong>Daniel Suarez Gil</strong>
                            <span class="badge-carne">&bull; 5190-23-3629</span>
                        </span>
                        <span class="member-badge">
                            <span class="member-avatar">SH</span>
                            <strong>Seily Herrera</strong>
                            <span class="badge-carne">&bull; 5190-22-2228</span>
                        </span>
                        <span class="member-badge">
                            <span class="member-avatar">JG</span>
                            <strong>Juan Garcia</strong>
                            <span class="badge-carne">&bull; 5190-23-6107</span>
                        </span>
                        <span class="member-badge">
                            <span class="member-avatar">ER</span>
                            <strong>Eber Rivera</strong>
                            <span class="badge-carne">&bull; 5190-23-9331</span>
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
            <div class="card-header-custom d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                <div>
                    <h1 class="h5 fw-bold mb-1 text-dark">
                        <i class="bi bi-card-checklist text-primary me-2"></i> Lista General de Estudiantes
                    </h1>
                    <small class="text-muted">Consulta, registro y administración de expedientes académicos</small>
                </div>
                <?php if (isset($_SESSION['id_usuario'])): ?>
                    <a href="nuevo.php" class="btn btn-success fw-semibold">
                        <i class="bi bi-plus-lg me-1"></i> Nuevo Estudiante
                    </a>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="table table-custom table-hover">
                    <thead>
                        <tr>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>Correo Electrónico</th>
                            <th>Aficiones</th>
                            <th class="text-center" style="min-width: 200px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT id_alumno, nombres, apellidos, correo, aficiones FROM alumno ORDER BY id_alumno DESC");
                        $hayRegistros = false;

                        while ($row = $stmt->fetch()):
                            $hayRegistros = true;
                            $idSeguro = (int)$row['id_alumno'];
                            $iniciales = mb_strtoupper(mb_substr(trim($row['nombres']), 0, 1) . mb_substr(trim($row['apellidos']), 0, 1));
                        ?>
                            <tr>
                                <td>
                                    <div class="student-name-chip">
                                        <span class="student-initials"><?= htmlspecialchars($iniciales, ENT_QUOTES, 'UTF-8') ?></span>
                                        <span><?= htmlspecialchars($row['nombres'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </td>
                                <td class="fw-medium text-dark"><?= htmlspecialchars($row['apellidos'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <a href="mailto:<?= htmlspecialchars($row['correo'], ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none text-muted">
                                        <i class="bi bi-envelope me-1 text-primary opacity-75"></i><?= htmlspecialchars($row['correo'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block text-secondary" style="max-width: 260px;" title="<?= htmlspecialchars($row['aficiones'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($row['aficiones'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="action-btn-group justify-content-center">
                                        <!-- Enlace directo seguro pasando el identificador único por parámetro sin entrada manual -->
                                        <a href="ver.php?id_alumno=<?= $idSeguro ?>" class="btn-sm-action btn-action-view" title="Ver expediente">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>

                                        <?php if (isset($_SESSION['id_usuario'])): ?>
                                            <a href="editar_db.php?id_alumno=<?= $idSeguro ?>" class="btn-sm-action btn-action-edit" title="Editar registro">
                                                <i class="bi bi-pencil-square"></i> Editar
                                            </a>
                                            <a href="delete.php?id_alumno=<?= $idSeguro ?>" class="btn-sm-action btn-action-delete" title="Eliminar registro">
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
                                    <div class="d-inline-flex p-3 rounded-circle bg-light mb-3">
                                        <i class="bi bi-inbox fs-2 text-muted"></i>
                                    </div>
                                    <p class="mb-2">No hay estudiantes registrados en la base de datos.</p>
                                    <?php if (isset($_SESSION['id_usuario'])): ?>
                                        <a href="nuevo.php" class="btn btn-primary btn-sm">
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
