<?php
session_start();
require_once "db.php";

// Si ya tiene sesión activa, redirigir a index.php
if (isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['correo']) && isset($_POST['pass'])) {
    $correo = trim($_POST['correo']);
    $pass = trim($_POST['pass']);

    if (strlen($correo) < 1 || strlen($pass) < 1) {
        $_SESSION['error'] = 'Por favor complete todos los campos.';
        header("Location: login.php");
        exit();
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'El formato de correo electrónico no es válido.';
        header("Location: login.php");
        exit();
    }

    $stmt = $pdo->prepare('SELECT id_usuario, nombre, password FROM usuario WHERE correo = :em');
    $stmt->execute([':em' => $correo]);
    $row = $stmt->fetch();

    if ($row !== false && ($row['password'] === $pass || password_verify($pass, $row['password']))) {
        $_SESSION['nombre'] = $row['nombre'];
        $_SESSION['id_usuario'] = $row['id_usuario'];
        $_SESSION['success'] = "Bienvenido al sistema, " . $row['nombre'];
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['error'] = 'Credenciales incorrectas. Verifique su correo y contraseña.';
        header("Location: login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once 'head.php'; ?>
    <title>Iniciar Sesión - Control Académico</title>
</head>
<body>
    <nav class="navbar navbar-custom mb-5">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-mortarboard-fill text-primary"></i> Control Académico
            </a>
            <a href="index.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left"></i> Volver al Inicio
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="card-custom p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 64px; height: 64px;">
                            <i class="bi bi-person-lock fs-2"></i>
                        </div>
                        <h2 class="h4 fw-bold mb-1">Ingreso al Sistema</h2>
                        <p class="text-muted small">Ingrese sus credenciales de usuario para continuar</p>
                    </div>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-custom alert-dismissible fade show mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                            <div><?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <form method="post" id="loginForm" onsubmit="return validateLogin();">
                        <div class="mb-3">
                            <label for="f_correo" class="form-label">Correo Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="correo" id="f_correo" class="form-control" placeholder="ejemplo@correo.com" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="f_pass" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-shield-lock"></i></span>
                                <input type="password" name="pass" id="f_pass" class="form-control" placeholder="••••••••" required>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary fw-semibold py-2">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary py-2">
                                Cancelar
                            </a>
                        </div>
                    </form>
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
    function validateLogin() {
        var email = document.getElementById('f_correo').value.trim();
        var pass = document.getElementById('f_pass').value.trim();

        if (email === '' || pass === '') {
            alert('Debe completar ambos campos.');
            return false;
        }
        if (email.indexOf('@') === -1) {
            alert('Ingrese una dirección de correo electrónico válida.');
            return false;
        }
        return true;
    }
    </script>
</body>
</html>
