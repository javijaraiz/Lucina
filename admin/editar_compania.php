<?php
/**
 * Lucina - Comparador Energético (v1.0 Entrega DAW)
 * Módulo: Edición de Compañía
 * Descripción: Permite modificar los datos básicos de una comercializadora
 * ya registrada en el sistema.
 */

require_once '../funciones/conexion.php';
require_once '../funciones/sesiones.php';

// Verificación de privilegios de gestor
requerirLogin();

$mensajeError = "";
$mensajeExito = "";
$idCompania = (int)($_GET['id'] ?? 0);

if (!$idCompania) {
    header('Location: companias.php');
    exit();
}

// RECUPERACIÓN DE DATOS: Cargamos el estado actual del registro
try {
    $consultaCia = $conn->prepare("SELECT * FROM companias WHERE comp_id = ?");
    $consultaCia->execute([$idCompania]);
    $compania = $consultaCia->fetch(PDO::FETCH_ASSOC);

    if (!$compania) {
        header('Location: companias.php');
        exit();
    }
} catch (PDOException $errorCarga) {
    die("Fallo crítico: No se pudo conectar con el repositorio de datos.");
}

// PROCESAMIENTO DE CAMBIOS: Guardado tras envío de formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreForm      = trim($_POST['nombre'] ?? '');
    $logoForm        = trim($_POST['logo_url'] ?? '');
    $notasForm       = trim($_POST['observaciones'] ?? '');
    $estadoActivado  = isset($_POST['activo']) ? 1 : 0;

    if (empty($nombreForm)) {
        $mensajeError = "El nombre de la compañía es un campo obligatorio.";
    } else {
        try {
            // Validación: Evitar duplicidad de nombres (exceptuando el registro actual)
            $chequeoDuplicado = $conn->prepare("SELECT COUNT(*) FROM companias WHERE comp_nombre = ? AND comp_id != ?");
            $chequeoDuplicado->execute([$nombreForm, $idCompania]);
            
            if ($chequeoDuplicado->fetchColumn() > 0) {
                $mensajeError = "Ya existe otra comercializadora registrada con ese nombre.";
            } else {
                // Ejecución de la actualización en BD
                $sentenciaUpdate = $conn->prepare("UPDATE companias SET comp_nombre = ?, comp_logo_url = ?, comp_observaciones = ?, comp_activo = ? WHERE comp_id = ?");
                $sentenciaUpdate->execute([$nombreForm, $logoForm, $notasForm, $estadoActivado, $idCompania]);
                
                $mensajeExito = "Los cambios han sido aplicados satisfactoriamente.";
                
                // Refrescamos los datos locales para que el formulario se actualice visualmente
                $compania['comp_nombre'] = $nombreForm;
                $compania['comp_logo_url'] = $logoForm;
                $compania['comp_observaciones'] = $notasForm;
                $compania['comp_activo'] = $estadoActivado;
            }
        } catch (PDOException $errorUpdate) {
            $mensajeError = "No se pudo realizar la actualización técnica: " . $errorUpdate->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Compañía — Lucina Admin</title>
    
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
            <h4 class="mb-0 fw-bold">Modificar Compañía: <span class="text-primary"><?= htmlspecialchars($compania['comp_nombre']) ?></span></h4>
            <a href="companias.php" class="btn btn-outline-secondary btn-sm px-3 rounded-3">
                <i class="bi bi-reply me-1"></i> Cancelar y Volver
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
                                        <label class="form-label small fw-bold text-muted">Nombre Comercial *</label>
                                        <input type="text" name="nombre" class="form-control" 
                                            value="<?= htmlspecialchars($compania['comp_nombre']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">URL del Logotipo Corporativo</label>
                                        <input type="text" name="logo_url" class="form-control" 
                                            value="<?= htmlspecialchars($compania['comp_logo_url']) ?>" placeholder="https://...">
                                    </div>
                                    <div class="col-12 border-top pt-4">
                                        <label class="form-label small fw-bold text-muted">Notas y Detalles de Contacto</label>
                                        <textarea name="observaciones" class="form-control" rows="4"><?= htmlspecialchars($compania['comp_observaciones']) ?></textarea>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch p-3 bg-light rounded border">
                                            <input class="form-check-input ms-0 me-2" type="checkbox" name="activo" id="activo" 
                                                <?= $compania['comp_activo'] ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-bold" for="activo">Activado / Desactivado</label>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-4 text-end">
                                        <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                                            <i class="bi bi-cloud-upload me-2"></i> Actualizar Información
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
