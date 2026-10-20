<?php
session_start();
require_once "db.php";

// Verificación de autenticación
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = "Debe iniciar sesión para agregar nuevos estudiantes.";
    header("Location: login.php");
    exit();
}

// Procesar formulario de alta
if (isset($_POST['nombres']) && isset($_POST['apellidos']) && isset($_POST['correo']) && isset($_POST['aficiones'])) {
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $correo = trim($_POST['correo']);
    $aficiones = trim($_POST['aficiones']);

    if (strlen($nombres) < 1 || strlen($apellidos) < 1 || strlen($correo) < 1 || strlen($aficiones) < 1) {
        $_SESSION['error'] = 'Todos los campos son requeridos.';
        header("Location: nuevo.php");
        exit();
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'El formato de correo electrónico no es válido.';
        header("Location: nuevo.php");
        exit();
    }

    // Validación de cursos agregados dinámicamente
    for ($i = 1; $i <= 9; $i++) {
        if (!isset($_POST['cur_anio' . $i]) || !isset($_POST['cur_nombre' . $i])) {
            continue;
        }

        $anio = trim($_POST['cur_anio' . $i]);
        $curso = trim($_POST['cur_nombre' . $i]);

        if (strlen($anio) === 0 || strlen($curso) === 0) {
            $_SESSION['error'] = 'Todos los campos de cursos son requeridos.';
            header("Location: nuevo.php");
            exit();
        }

        if (!is_numeric($anio) || (int)$anio < 1900 || (int)$anio > 2100) {
            $_SESSION['error'] = 'El año del curso debe ser un valor numérico válido.';
            header("Location: nuevo.php");
            exit();
        }
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('INSERT INTO alumno (id_usuario, nombres, apellidos, correo, aficiones) VALUES (:ide, :nom, :ape, :cor, :afi)');
        $stmt->execute([
            ':ide' => $_SESSION['id_usuario'],
            ':nom' => $nombres,
            ':ape' => $apellidos,
            ':cor' => $correo,
            ':afi' => $aficiones
        ]);

        $id_alumno = $pdo->lastInsertId();

        // Inserción de cursos asociados
        for ($i = 1; $i <= 9; $i++) {
            if (!isset($_POST['cur_anio' . $i]) || !isset($_POST['cur_nombre' . $i])) {
                continue;
            }

            $anio = (int)$_POST['cur_anio' . $i];
            $curso = trim($_POST['cur_nombre' . $i]);

            // Buscar si el curso ya existe
            $stmtC = $pdo->prepare('SELECT id_curso FROM cursos WHERE nombre = :nom');
            $stmtC->execute([':nom' => $curso]);
            $rowC = $stmtC->fetch();

            if ($rowC !== false) {
                $id_curso = $rowC['id_curso'];
            } else {
                $stmtNewC = $pdo->prepare('INSERT INTO cursos (nombre) VALUES (:nom)');
                $stmtNewC->execute([':nom' => $curso]);
                $id_curso = $pdo->lastInsertId();
            }

            $stmtAC = $pdo->prepare('INSERT INTO alumno_cursos (id_alumno, anio, id_curso) VALUES (:ide, :anio, :idc)');
            $stmtAC->execute([
                ':ide'  => $id_alumno,
                ':anio' => $anio,
                ':idc'  => $id_curso
            ]);
        }

        $pdo->commit();
        $_SESSION['success'] = '¡Estudiante registrado exitosamente!';
        header('Location: index.php');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Error al registrar estudiante: ' . $e->getMessage();
        header("Location: nuevo.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once 'head.php'; ?>
    <title>Nuevo Estudiante - Registro Académico</title>
</head>
<body>
    <nav class="navbar navbar-custom mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-mortarboard-fill text-primary"></i> Control Académico
            </a>
            <a href="index.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left"></i> Volver a la Lista
            </a>
        </div>
    </nav>

    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="card-custom">
                    <div class="card-header-custom">
                        <h2 class="h5 fw-bold mb-0 text-dark">
                            <i class="bi bi-person-plus-fill text-primary me-2"></i> Ingreso de Nuevo Estudiante
                        </h2>
                    </div>

                    <div class="p-4">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-custom alert-dismissible fade show mb-4" role="alert">
                                <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                                <div><?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?></div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>

                        <form method="post" id="formNuevo" onsubmit="return validateForm();">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="fn" class="form-label">Nombres <span class="text-danger">*</span></label>
                                    <input type="text" name="nombres" id="fn" class="form-control" placeholder="Ej. Juan Carlos" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="ln" class="form-label">Apellidos <span class="text-danger">*</span></label>
                                    <input type="text" name="apellidos" id="ln" class="form-control" placeholder="Ej. Pérez Gómez" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="em" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                                <input type="email" name="correo" id="em" class="form-control" placeholder="ejemplo@correo.com" required>
                            </div>

                            <div class="mb-4">
                                <label for="afi" class="form-label">Aficiones y Pasatiempos <span class="text-danger">*</span></label>
                                <textarea name="aficiones" id="afi" rows="3" class="form-control" placeholder="Describa intereses, pasatiempos o aficiones..." required></textarea>
                            </div>

                            <div class="border-top pt-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h3 class="h6 fw-bold mb-0 text-dark">Cursos y Certificaciones IT</h3>
                                        <small class="text-muted">Añada cursos realizados con sugerencia automática</small>
                                    </div>
                                    <button type="button" id="addCurso" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Agregar Curso
                                    </button>
                                </div>

                                <div id="curso_fields"></div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 border-top pt-4">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary fw-semibold">
                                    <i class="bi bi-save me-1"></i> Grabar Estudiante
                                </button>
                            </div>
                        </form>
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

    <script>
    var cuentaCur = 0;

    function attachAutocomplete() {
        $('.cursos-autocomplete').autocomplete({
            source: 'cursos.php',
            minLength: 1
        });
    }

    $(document).ready(function() {
        $('#addCurso').click(function(event) {
            event.preventDefault();
            if (cuentaCur >= 9) {
                alert('Número máximo de cursos alcanzado (9).');
                return;
            }
            cuentaCur++;
            var html = 
                '<div class="course-row" id="curso' + cuentaCur + '">' +
                    '<div class="row g-2 align-items-center">' +
                        '<div class="col-3 col-sm-2">' +
                            '<label class="form-label small text-muted mb-1">Año</label>' +
                            '<input type="number" name="cur_anio' + cuentaCur + '" class="form-control form-control-sm" placeholder="2023" min="1900" max="2100" required>' +
                        '</div>' +
                        '<div class="col-7 col-sm-8">' +
                            '<label class="form-label small text-muted mb-1">Nombre del Curso</label>' +
                            '<input type="text" name="cur_nombre' + cuentaCur + '" class="form-control form-control-sm cursos-autocomplete" placeholder="Escriba para buscar o registrar..." required>' +
                        '</div>' +
                        '<div class="col-2 col-sm-2 text-end">' +
                            '<label class="form-label small text-muted mb-1 d-block">&nbsp;</label>' +
                            '<button type="button" class="btn btn-outline-danger btn-sm" onclick="$(\'#curso' + cuentaCur + '\').remove();">' +
                                '<i class="bi bi-trash"></i>' +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                '</div>';

            $('#curso_fields').append(html);
            attachAutocomplete();
        });
    });

    function validateForm() {
        var fn = document.getElementById('fn').value.trim();
        var ln = document.getElementById('ln').value.trim();
        var em = document.getElementById('em').value.trim();
        var afi = document.getElementById('afi').value.trim();

        if (fn === '' || ln === '' || em === '' || afi === '') {
            alert('Todos los campos son requeridos.');
            return false;
        }

        if (em.indexOf('@') === -1) {
            alert('Ingrese una dirección de correo válida.');
            return false;
        }

        return true;
    }
    </script>
</body>
</html>
