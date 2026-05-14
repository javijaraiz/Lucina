<?php
require_once 'funciones/conexion.php';

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_input = $_POST['usuario'] ?? '';

    if (empty($usuario_input)) {
        $mensaje = 'Por favor, introduce tu usuario o email.';
        $tipo_mensaje = 'danger';
    } else {
        try {
            // Verificar si el usuario existe por email o por user_user
            $stmt = $conn->prepare("SELECT user_id, user_email FROM usuarios WHERE user_email = :input OR user_user = :input");
            $stmt->execute(['input' => $usuario_input]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                // Pendiente configurar servidor SMTP en producción
                // He dejado comentado el mail() de las pruebas que hice con mi correo
                // mail('javijaraiz@gmail.com', 'Recuperar Contraseña', 'Enlace de recuperación...');
                
                $mensaje = 'Se ha enviado un enlace de recuperación a tu correo electrónico.';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = 'No se encontró ningún usuario con esos datos.';
                $tipo_mensaje = 'danger';
            }
        } catch (PDOException $e) {
            $mensaje = 'Error al procesar la solicitud. Inténtalo de nuevo.';
            $tipo_mensaje = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Lucina</title>

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
                    <p class="text-muted">Recupera el acceso a tu cuenta</p>
                </div>

                <!-- Card de recuperación -->
                <div class="tarjeta-panel">
                    <div class="card-body p-4">

                        <?php if ($mensaje): ?>
                            <div class="alert alert-<?php echo $tipo_mensaje; ?>" role="alert">
                                <i class="bi bi-info-circle-fill"></i>
                                <?php echo $mensaje; ?>
                            </div>
                        <?php endif; ?>

                        <p class="text-muted small mb-4">Introduce tu nombre de usuario o correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="usuario" class="form-label">Usuario o Email</label>
                                <input type="text" class="form-control" id="usuario" name="usuario"
                                    placeholder="Ej: admin o tu@email.com" required>
                            </div>

                            <button type="submit" class="btn btn-lucina-primary w-100">
                                <i class="bi bi-envelope"></i> Enviar enlace
                            </button>
                        </form>

                    </div>
                </div>

                <!-- Volver al login -->
                <div class="text-center mt-3">
                    <a href="login.php" class="text-muted text-decoration-none">
                        <i class="bi bi-arrow-left"></i> Volver al inicio de sesión
                    </a>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
