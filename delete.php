<?php
session_start();
require_once "db.php";

// Verificación de autenticación
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = "Debe iniciar sesión para realizar esta operación.";
    header('Location: login.php');
    exit();
}

// Procesar eliminación vía POST
if (isset($_POST['delete']) && isset($_POST['id_alumno']) && is_numeric($_POST['id_alumno'])) {
    $id_alumno = (int)$_POST['id_alumno'];

    try {
        $pdo->beginTransaction();

        // 1. Eliminar relaciones en alumno_cursos
        $stmtRel = $pdo->prepare("DELETE FROM alumno_cursos WHERE id_alumno = :idx");
        $stmtRel->execute([':idx' => $id_alumno]);

        // 2. Eliminar registro del alumno
        $stmtAlu = $pdo->prepare("DELETE FROM alumno WHERE id_alumno = :idx");
        $stmtAlu->execute([':idx' => $id_alumno]);

        $pdo->commit();
        $_SESSION['success'] = '¡Registro de alumno eliminado con éxito!';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Error al eliminar el registro: ' . $e->getMessage();
    }

    header('Location: index.php');
    exit();
}

// Validación de parámetro GET
if (!isset($_GET['id_alumno']) || !is_numeric($_GET['id_alumno'])) {
    $_SESSION['error'] = "No se especificó ningún registro de alumno para eliminar.";
    header('Location: index.php');
    exit();
}

$stmt = $pdo->prepare("SELECT id_alumno, nombres, apellidos, correo FROM alumno WHERE id_alumno = :idx");
$stmt->execute([':idx' => $_GET['id_alumno']]);
$alumno = $stmt->fetch();

if ($alumno === false) {
    $_SESSION['error'] = 'El alumno a eliminar no fue encontrado en la base de datos.';
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once 'head.php'; ?>
    <title>Confirmar Eliminación de Alumno</title>
</head>
<body>
    <nav class="navbar navbar-custom mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-mortarboard-fill text-primary"></i> Control Académico
            </a>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card-custom border-danger">
                    <div class="card-header bg-danger text-white py-3">
                        <h2 class="h5 mb-0 fw-bold">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> Confirmar Eliminación
                        </h2>
                    </div>
                    <div class="p-4">
                        <p class="text-dark">
                            ¿Está seguro de que desea eliminar permanentemente al siguiente alumno y todas sus certificaciones asociadas?
                        </p>
                        
                        <div class="bg-light p-3 rounded mb-4 border">
                            <p class="mb-1"><strong>Nombre completo:</strong> <?= htmlspecialchars($alumno['nombres'] . ' ' . $alumno['apellidos'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="mb-0"><strong>Correo:</strong> <?= htmlspecialchars($alumno['correo'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>

                        <form method="post" action="delete.php">
                            <!-- El ID se envía directamente de forma segura por parámetro oculto sin entrada manual -->
                            <input type="hidden" name="id_alumno" value="<?= (int)$alumno['id_alumno'] ?>">
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-1"></i> Cancelar
                                </a>
                                <button type="submit" name="delete" class="btn btn-danger">
                                    <i class="bi bi-trash3-fill me-1"></i> Eliminar Registro
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
</body>
</html>
