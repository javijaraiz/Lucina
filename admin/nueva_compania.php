<?php
/**
 * Lucina - Comparador Energético (v1.0 Entrega DAW)
 * Módulo: Alta de Compañías
 * Descripción: Permite añadir nuevas comercializadoras de electricidad al sistema.
 * Es un requisito previo para poder asociar tarifas después.
 */

require_once '../funciones/conexion.php';
require_once '../funciones/sesiones.php';

// Verificación de acceso seguro
requerirLogin();

$mensajeError = "";
$mensajeExito = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreCompania = trim($_POST['nombre'] ?? '');
    $urlLogo        = trim($_POST['logo_url'] ?? '');
    $notas          = trim($_POST['observaciones'] ?? '');
    $estadoActivo   = isset($_POST['activo']) ? 1 : 0;

    if (empty($nombreCompania)) {
        $mensajeError = "Es necesario indicar el nombre oficial de la compañía.";
    } else {
        try {
            // Comprobamos si el nombre ya está registrado para evitar duplicados (Constraint UNIQUE en BD)
            $consultaExiste = $conn->prepare("SELECT COUNT(*) FROM companias WHERE comp_nombre = ?");
            $consultaExiste->execute([$nombreCompania]);
            
            if ($consultaExiste->fetchColumn() > 0) {
                $mensajeError = "Ya existe una compañía con ese nombre en el sistema.";
            } else {
                // Inserción del nuevo registro
                $insertarCia = $conn->prepare("INSERT INTO companias (comp_nombre, comp_logo_url, comp_observaciones, comp_activo) VALUES (?, ?, ?, ?)");
                $insertarCia->execute([$nombreCompania, $urlLogo, $notas, $estadoActivo]);
                
                $mensajeExito = "La compañía se ha guardado correctamente.";
            }
        } catch (PDOException $errorBD) {
            $mensajeError = "Fallo en la comunicación con la base de datos: " . $errorBD->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Compañía — Lucina Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="icon" href="../favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="admin.css">
</head>

<body>

    <aside class="sidebar shadow">
        <div class="sidebar-brand d-flex align-items-center mb-4">
            <img src="../logo.png" alt="Lucina" style="max-height: 40px;">
            <span class="ms-2 fw-bold text-primary">Lucina Admin</span>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-label text-uppercase small opacity-50 px-3 py-2">Resumen</div>
            <a href="index.php" class="sidebar-link"><i class="bi bi-speedometer2 me-2"></i> Escritorio</a>
            <a href="companias.php" class="sidebar-link active"><i class="bi bi-building me-2"></i> Compañías</a>
            <a href="tarifas.php" class="sidebar-link"><i class="bi bi-lightning-charge me-2"></i> Catálogo de Tarifas</a>
            <div class="sidebar-label text-uppercase small opacity-50 px-3 py-2 mt-3">CRM Clientes</div>
            <a href="clientes.php" class="sidebar-link"><i class="bi bi-person-lines-fill me-2"></i> Presupuestos</a>
        </nav>
        <div class="sidebar-footer mt-auto p-3 border-top">
            <a href="../salir.php" class="text-danger text-decoration-none small fw-bold"><i class="bi bi-box-arrow-left me-1"></i> Cerrar sesión</a>
        </div>
    </aside>

    <main class="main bg-light">
        <div class="topbar bg-white shadow-sm px-4 py-3 d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 fw-bold">Registrar Nueva Compañía</h4>
            <a href="companias.php" class="btn btn-outline-secondary btn-sm px-3 rounded-3">
                <i class="bi bi-reply me-1"></i> Volver al Listado
            </a>
        </div>

        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body p-4">
                            <?php if ($mensajeError): ?>
                                <div class="alert alert-danger border-0"><?= $mensajeError ?></div>
                            <?php endif; ?>
                            <?php if ($mensajeExito): ?>
                                <div class="alert alert-success border-0"><?= $mensajeExito ?></div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Nombre de la Comercializadora *</label>
                                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Energía Solar S.L." required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">URL del Logotipo</label>
                                        <input type="text" name="logo_url" class="form-control" placeholder="https://dominio.com/logo.svg">
                                    </div>
                                    <div class="col-12 border-top pt-4">
                                        <label class="form-label small fw-bold text-muted">Notas y Detalles Internos</label>
                                        <textarea name="observaciones" class="form-control" rows="4" placeholder="Indicar aquí acuerdos comerciales, teléfonos de contacto..."></textarea>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch p-3 bg-light rounded border">
                                            <input class="form-check-input ms-0 me-2" type="checkbox" name="activo" id="activo" checked>
                                            <label class="form-check-label fw-bold" for="activo">Activado / Desactivado</label>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-4 text-end">
                                        <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                                            <i class="bi bi-save2 me-2"></i> Guardar Registro
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
