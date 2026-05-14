<?php
require_once 'funciones/conexion.php';
require_once 'funciones/sesiones.php';

// Si ya está logueado, redirigir al panel de control
if (estaLogueado()) {
    header("Location: admin/index.php");
    exit;
}

$error = '';

// Procesar el formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos';
    } else {
        try {
            // Buscar usuario en la base de datos
            $stmt = $conn->prepare("SELECT user_id, user_nombre_completo, user_email, user_password_hash, user_activo, user_user
FROM usuarios
WHERE user_email = :email OR user_user = :email");
            $stmt->execute(['email' => $email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && $usuario['user_activo'] == 1) {
                // Verificar la contraseña
                if (password_verify($password, $usuario['user_password_hash'])) {
                    // Login exitoso
                    iniciarSesion(
                        $usuario['user_id'],
                        $usuario['user_nombre_completo'],
                        $usuario['user_email']
                    );
                    header('Location: panel.php');
                    exit();
                } else {
                    $error = 'Email o contraseña incorrectos';
                }
            } else {
                $error = 'Email o contraseña incorrectos';
            }
        } catch (PDOException $e) {
            $error = 'Error al iniciar sesión. Inténtalo de nuevo.';
            // TODO: Registrar el error en un log
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Lucina</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/estilos.css">
</head>

<body class="bg-white">

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5">

                <!-- Logo y título -->
                <div class="text-center mb-4">
                    <img src="logo.png" alt="Lucina" height="100" class="mb-3">
                    <p class="text-muted">Inicia sesión en tu cuenta</p>
                </div>

                <!-- Card de login -->
                <div class="tarjeta-panel">
                    <div class="card-body p-4">


                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="email" name="email"
                                    placeholder="Nombre de usuario" required
                                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="••••••••" required>
                                <div class="text-end mt-1">
                                    <a href="recuperar_password.php" class="text-muted text-decoration-none small">¿Olvidaste tu contraseña?</a>
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">
                                    Recordarme
                                </label>
                            </div>

                            <button type="submit" class="btn btn-lucina-primary w-100">
                                <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                            </button>
                        </form>



                    </div>
                </div>

                <!-- Volver al inicio -->
                <div class="text-center mt-3">
                    <a href="index.php" class="text-muted text-decoration-none">
                        <i class="bi bi-arrow-left"></i> Volver al inicio
                    </a>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

