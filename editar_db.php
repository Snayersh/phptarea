<?php
session_start();
require_once "db.php";

// Verificación de autenticación
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = "Debe iniciar sesión para editar registros.";
    header("Location: login.php");
    exit();
}

// Procesar actualización (POST)
$id_post = $_POST['id_alumno'] ?? $_POST['id'] ?? null;
if ($id_post !== null && is_numeric($id_post) && isset($_POST['nombres']) && isset($_POST['apellidos']) && isset($_POST['correo']) && isset($_POST['aficiones'])) {
    $id_alumno = (int)$id_post;
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $correo = trim($_POST['correo']);
    $aficiones = trim($_POST['aficiones']);

    if (strlen($nombres) < 1 || strlen($apellidos) < 1 || strlen($correo) < 1 || strlen($aficiones) < 1) {
        $_SESSION['error'] = 'Todos los campos son obligatorios.';
        header("Location: editar_db.php?id_alumno=" . $id_alumno);
        exit();
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'El correo electrónico ingresado no tiene un formato válido.';
        header("Location: editar_db.php?id_alumno=" . $id_alumno);
        exit();
    }

    // Validación de cursos
    $cursosAProcesar = [];
    for ($i = 1; $i <= 9; $i++) {
        if (!isset($_POST['cur_anio' . $i]) || !isset($_POST['cur_nombre' . $i])) {
            continue;
        }

        $anioRaw = trim($_POST['cur_anio' . $i]);
        $curso = trim($_POST['cur_nombre' . $i]);

        if (strlen($anioRaw) === 0 || strlen($curso) === 0) {
            $_SESSION['error'] = 'Todos los campos de cursos son requeridos.';
            header("Location: editar_db.php?id_alumno=" . $id_alumno);
            exit();
        }

        if (!is_numeric($anioRaw)) {
            $_SESSION['error'] = 'El año del curso debe ser numérico.';
            header("Location: editar_db.php?id_alumno=" . $id_alumno);
            exit();
        }

        $anioNum = (int)$anioRaw;
        // Si ingresaron 2 dígitos (ej. 24), convertir a 4 dígitos (ej. 2024)
        if ($anioNum >= 0 && $anioNum <= 99) {
            $anioNum += 2000;
        }

        if ($anioNum < 1900 || $anioNum > 2100) {
            $_SESSION['error'] = 'El año del curso debe ser un año válido (ej. 2024).';
            header("Location: editar_db.php?id_alumno=" . $id_alumno);
            exit();
        }

        $cursosAProcesar[] = [
            'anio'   => $anioNum,
            'nombre' => $curso
        ];
    }

    try {
        $pdo->beginTransaction();

        // 1. Actualización preparada de datos del alumno
        $stmtUpdate = $pdo->prepare('
            UPDATE alumno 
            SET nombres = :nom, apellidos = :ape, correo = :cor, aficiones = :afi 
            WHERE id_alumno = :id
        ');
        $stmtUpdate->execute([
            ':nom' => $nombres,
            ':ape' => $apellidos,
            ':cor' => $correo,
            ':afi' => $aficiones,
            ':id'  => $id_alumno
        ]);

        // 2. Limpieza de cursos anteriores para sincronización
        $stmtDelCursos = $pdo->prepare('DELETE FROM alumno_cursos WHERE id_alumno = :id');
        $stmtDelCursos->execute([':id' => $id_alumno]);

        // 3. Inserción de cursos actualizados
        foreach ($cursosAProcesar as $curItem) {
            $anioVal = $curItem['anio'];
            $curso = $curItem['nombre'];

            // Buscar si el curso ya existe en el catálogo
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

            $stmtAC = $pdo->prepare('INSERT INTO alumno_cursos (id_alumno, anio, id_curso) VALUES (:ida, :anio, :idc)');
            try {
                $stmtAC->execute([
                    ':ida'  => $id_alumno,
                    ':anio' => $anioVal,
                    ':idc'  => $id_curso
                ]);
            } catch (PDOException $ex) {
                // Si la columna en la BD fuera TINYINT (max 255), guardar formato de 2 dígitos (ej. 24) para evitar overflow 1264
                if ($ex->getCode() == '22003' || strpos($ex->getMessage(), '1264') !== false) {
                    $stmtAC->execute([
                        ':ida'  => $id_alumno,
                        ':anio' => $anioVal % 100,
                        ':idc'  => $id_curso
                    ]);
                } else {
                    throw $ex;
                }
            }
        }

        $pdo->commit();
        $_SESSION['success'] = '¡Registro de estudiante actualizado exitosamente!';
        header('Location: index.php');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Error al actualizar el registro: ' . $e->getMessage();
        header("Location: editar_db.php?id_alumno=" . $id_alumno);
        exit();
    }
}

// Carga inicial de datos vía GET
$id_get = $_GET['id_alumno'] ?? $_GET['id'] ?? null;
if ($id_get === null || !is_numeric($id_get)) {
    $_SESSION['error'] = "No se especificó un estudiante para editar.";
    header('Location: index.php');
    exit();
}

$id_alumno = (int)$id_get;

$stmt = $pdo->prepare('SELECT id_alumno, nombres, apellidos, correo, aficiones FROM alumno WHERE id_alumno = :id');
$stmt->execute([':id' => $id_alumno]);
$alumno = $stmt->fetch();

if ($alumno === false) {
    $_SESSION['error'] = 'El estudiante solicitado no existe en la base de datos.';
    header('Location: index.php');
    exit();
}

// Cursos actuales del alumno
$stmtCursos = $pdo->prepare('
    SELECT ac.anio, c.nombre 
    FROM alumno_cursos ac 
    INNER JOIN cursos c ON c.id_curso = ac.id_curso 
    WHERE ac.id_alumno = :id 
    ORDER BY ac.anio ASC
');
$stmtCursos->execute([':id' => $id_alumno]);
$cursosActuales = $stmtCursos->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once 'head.php'; ?>
    <title>Editar Estudiante - <?= htmlspecialchars($alumno['nombres'] . ' ' . $alumno['apellidos'], ENT_QUOTES, 'UTF-8') ?></title>
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
                    <div class="card-header-custom d-flex justify-content-between align-items-center">
                        <h2 class="h5 fw-bold mb-0 text-dark">
                            <i class="bi bi-pencil-square text-warning me-2"></i> Editar Registro de Estudiante
                        </h2>
                        <span class="badge bg-secondary">ID: #<?= (int)$alumno['id_alumno'] ?></span>
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

                        <form method="post" action="editar_db.php" id="formEditar" onsubmit="return validateForm();">
                            <!-- Parámetro de identificación seguro y directo sin entrada manual -->
                            <input type="hidden" name="id_alumno" value="<?= (int)$alumno['id_alumno'] ?>">

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="fn" class="form-label">Nombres <span class="text-danger">*</span></label>
                                    <input type="text" name="nombres" id="fn" class="form-control" value="<?= htmlspecialchars($alumno['nombres'], ENT_QUOTES, 'UTF-8') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="ln" class="form-label">Apellidos <span class="text-danger">*</span></label>
                                    <input type="text" name="apellidos" id="ln" class="form-control" value="<?= htmlspecialchars($alumno['apellidos'], ENT_QUOTES, 'UTF-8') ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="em" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                                <input type="email" name="correo" id="em" class="form-control" value="<?= htmlspecialchars($alumno['correo'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>

                            <div class="mb-4">
                                <label for="afi" class="form-label">Aficiones y Pasatiempos <span class="text-danger">*</span></label>
                                <textarea name="aficiones" id="afi" rows="3" class="form-control" required><?= htmlspecialchars($alumno['aficiones'], ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>

                            <div class="border-top pt-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h3 class="h6 fw-bold mb-0 text-dark">Cursos y Certificaciones IT</h3>
                                        <small class="text-muted">Añada, modifique o remueva cursos asociados</small>
                                    </div>
                                    <button type="button" id="addCurso" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Agregar Curso
                                    </button>
                                </div>

                                <div id="curso_fields">
                                    <?php 
                                    $pos = 0;
                                    foreach ($cursosActuales as $cur): 
                                        $pos++;
                                        $anioMostrado = (int)$cur['anio'];
                                        if ($anioMostrado >= 0 && $anioMostrado <= 99) {
                                            $anioMostrado += 2000;
                                        }
                                    ?>
                                        <div class="course-row" id="curso<?= $pos ?>">
                                            <div class="row g-2 align-items-center">
                                                <div class="col-3 col-sm-2">
                                                    <label class="form-label small text-muted mb-1">Año</label>
                                                    <input type="number" name="cur_anio<?= $pos ?>" class="form-control form-control-sm" value="<?= $anioMostrado ?>" min="1900" max="2100" required>
                                                </div>
                                                <div class="col-7 col-sm-8">
                                                    <label class="form-label small text-muted mb-1">Nombre del Curso</label>
                                                    <input type="text" name="cur_nombre<?= $pos ?>" class="form-control form-control-sm cursos-autocomplete" value="<?= htmlspecialchars($cur['nombre'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                </div>
                                                <div class="col-2 col-sm-2 text-end">
                                                    <label class="form-label small text-muted mb-1 d-block">&nbsp;</label>
                                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="$('#curso<?= $pos ?>').remove();">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 border-top pt-4">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-warning fw-semibold">
                                    <i class="bi bi-check2-circle me-1"></i> Grabar Cambios
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
    var cuentaCur = <?= count($cursosActuales) ?>;

    function attachAutocomplete() {
        $('.cursos-autocomplete').autocomplete({
            source: 'cursos.php',
            minLength: 1
        });
    }

    $(document).ready(function() {
        attachAutocomplete();

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
                            '<input type="number" name="cur_anio' + cuentaCur + '" class="form-control form-control-sm" placeholder="2024" min="1900" max="2100" required>' +
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
