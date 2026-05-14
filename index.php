<?php
require_once 'funciones/sesiones.php';
$userLogueado = estaLogueado();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lucina - Tu Comparador Energético Inteligente</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>

<body>

    <a href="https://wa.me/34659982383" class="floating-whatsapp" target="_blank">
        <div class="whatsapp-content">
            <span>¿Necesitas ayuda?</span>
            <i class="bi bi-whatsapp"></i>
        </div>
    </a>

    <div class="top-header">
        <div class="container d-flex justify-content-end">
            <a href="login.php"><i class="bi bi-person-lock"></i> Acceso Gestores</a>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-lucina">
        <div class="container d-flex justify-content-center align-items-center">
            <a class="navbar-brand" href="index.php">
                <img src="logo.png" alt="Lucina">
            </a>
        </div>
    </nav>

    <div class="container my-5">

        <!-- Barra de progreso del proceso (Stepper) -->
        <div class="modern-stepper">
            <div class="m-step active">
                <div class="m-step-circle">1</div>
                <div class="m-step-label">Análisis</div>
            </div>
            <div class="m-step-line"></div>
            <div class="m-step">
                <div class="m-step-circle">2</div>
                <div class="m-step-label">Validación</div>
            </div>
            <div class="m-step-line"></div>
            <div class="m-step">
                <div class="m-step-circle">3</div>
                <div class="m-step-label">Ahorro</div>
            </div>
        </div>

        <!-- Sección Hero / Introducción -->
        <div class="hero-lucina">
            <h1>Optimiza tu factura eléctrica y <span>ahorra</span></h1>
            <p>Sube tu factura ahora para que nuestro sistema la analice y encuentre la mejor tarifa para ti.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-9">
                
                <div class="tarjeta-panel mb-4 shadow-sm border-0">
                    <div class="card-body p-4 text-center">
                        <div class="display-4 text-primary mb-3">
                            <i class="bi bi-robot"></i>
                        </div>
                        <h3 class="h5 fw-bold">Análisis con IA de Gemini</h3>
                        <p class="text-muted small mb-0">Tecnología de última generación para procesar tus PDF de factura al instante.</p>
                    </div>
                </div>

                <div class="tarjeta-panel mb-4 shadow-sm border-0">
                    <div class="card-body p-4">
                        <h5 class="mb-3 text-center fw-bold text-primary">1. Identifícate</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="nombre_cli" class="form-label small fw-bold">Nombre Completo</label>
                                <input type="text" class="form-control" id="nombre_cli" placeholder="Ej: Juan Pérez" required>
                            </div>
                            <div class="col-md-4">
                                <label for="email_cli" class="form-label small fw-bold">Correo Electrónico</label>
                                <input type="email" class="form-control" id="email_cli" placeholder="juan@correo.com" required>
                            </div>
                            <div class="col-md-4">
                                <label for="tel_cli" class="form-label small fw-bold">Teléfono de contacto</label>
                                <input type="tel" class="form-control" id="tel_cli" placeholder="666 000 000" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-dashboard mb-4 shadow-sm border-0">
                    <div class="card-body p-4">
                        <h5 class="mb-3 text-center fw-bold text-primary">2. Adjunta tu última factura</h5>
                        <div class="dropzone-premium" id="zona-subida">
                            <div class="dropzone-icon"><i class="bi bi-file-earmark-pdf"></i></div>
                            <h4>Suelte aquí su factura (PDF o imagen)</h4>
                            <p class="text-muted">O haga clic para explorar sus archivos</p>
                            <div class="small text-muted mt-3">Máxima confidencialidad en el tratamiento de datos</div>
                        </div>
                    </div>
                </div>
                
                <div id="progress-container" class="mt-4" style="display: none;">
                    <p id="file-name" class="fw-bold mb-2 text-primary"></p>
                    <div class="progress" style="height: 12px; border-radius: 10px;">
                        <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <small class="text-muted">Analizando facturación...</small>
                        <small id="progress-percent" class="fw-bold">0%</small>
                    </div>
                </div>

                <div class="trust-badges mt-4">
                    <div class="t-badge"><i class="bi bi-shield-check"></i> Seguro</div>
                    <div class="t-badge"><i class="bi bi-lightning"></i> Rápido</div>
                    <div class="t-badge"><i class="bi bi-hand-thumbs-up"></i> Sin compromisos</div>
                </div>

                <!-- Pie de página informativo -->
                <footer class="text-center mt-5 pt-5 text-muted small border-top">
                    <p>© 2026 Lucina - Proyecto Final de Ciclo DAW.</p>
                    <nav class="footer-links">
                        <a href="#" class="text-primary mx-2">Aviso Legal</a>
                        <a href="#" class="text-primary mx-2">Privacidad</a>
                        <a href="#" class="text-primary mx-2">Cookies</a>
                    </nav>
                </footer>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/lucina.js"></script>

    <script>
        inicializarZonaSubida('zona-subida', function (archivo) {
            const nombre = document.getElementById('nombre_cli').value;
            const email  = document.getElementById('email_cli').value;
            const tel    = document.getElementById('tel_cli').value;

            if (!nombre || !email || !tel) {
                mostrarNotificacion('Es obligatorio indicar los datos de contacto.', 'warning');
                return;
            }

            document.getElementById('file-name').textContent = 'Factura: ' + archivo.name;
            document.getElementById('progress-container').style.display = 'block';

            let pct = 0;
            const timer = setInterval(() => {
                pct += 5;
                if (pct > 90) clearInterval(timer);
                document.getElementById('progress-bar').style.width = pct + '%';
                document.getElementById('progress-percent').textContent = pct + '%';
            }, 100);

            const form = new FormData();
            form.append('factura', archivo);
            form.append('nombre', nombre);
            form.append('email', email);
            form.append('telefono', tel);

            fetch('funciones/analizar.php', { method: 'POST', body: form })
                .then(r => r.json())
                .then(res => {
                    clearInterval(timer);
                    if (res.success) {
                        document.getElementById('progress-bar').style.width = '100%';
                        document.getElementById('progress-percent').textContent = '100%';
                        setTimeout(() => {
                            mostrarNotificacion(res.mensaje, 'success');
                            setTimeout(() => window.location.href = 'datos.php', 800);
                        }, 500);
                    } else {
                        mostrarNotificacion('Error al procesar el documento.', 'danger');
                        document.getElementById('progress-container').style.display = 'none';
                    }
                })
                .catch(err => {
                    clearInterval(timer);
                    console.error(err);
                    mostrarNotificacion('Fallo en la comunicación con el servidor.', 'danger');
                });
        });
    </script>

</body>
</html>
