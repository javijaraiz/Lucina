<?php
/**
 * Lucina - Comparador Energético (DAW DUAL - Proyecto Intermodular - 25/26)
 * Módulo: Edición de Tarifa
 * Descripción: Permite actualizar los precios de energía y potencia de una
 * oferta ya existente en el catálogo.
 */

require_once '../funciones/conexion.php';
require_once '../funciones/sesiones.php';

// Verificación de acceso seguro
requerirLogin();

$mensajeError = "";
$mensajeExito = "";
$idTarifa = (int)($_GET['id'] ?? 0);

if (!$idTarifa) {
    header('Location: tarifas.php');
    exit();
}

// CARGA DE COMPAÑÍAS: Para el desplegable de selección
try {
    $consultaCias = $conn->query("SELECT comp_id, comp_nombre FROM companias WHERE comp_activo = 1 ORDER BY comp_nombre ASC");
    $listadoCompanias = $consultaCias->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $errorCargaCompania) {
    $listadoCompanias = [];
}

// RECUPERACIÓN DE DATOS: Cargamos el estado actual de la tarifa
try {
    $consultaTarifa = $conn->prepare("SELECT * FROM tarifas WHERE tar_id = ?");
    $consultaTarifa->execute([$idTarifa]);
    $tarifaActual = $consultaTarifa->fetch(PDO::FETCH_ASSOC);

    if (!$tarifaActual) {
        header('Location: tarifas.php');
        exit();
    }
} catch (PDOException $errorCargaTarifa) {
    die("Fallo crítico en el acceso a datos.");
}

// PROCESAMIENTO DE CAMBIOS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idCompaniaForm   = $_POST['comp_id'] ?? '';
    $nombreTarifaForm = trim($_POST['nombre'] ?? '');
    $eneP1Form        = $_POST['p_ene_p1'] ?? 0;
    $eneP2Form        = $_POST['p_ene_p2'] ?? 0;
    $eneP3Form        = $_POST['p_ene_p3'] ?? 0;
    $potP1Form        = $_POST['p_pot_p1'] ?? 0;
    $potP2Form        = $_POST['p_pot_p2'] ?? 0;
    $notasForm        = trim($_POST['observaciones'] ?? '');
    $estadoActivado   = isset($_POST['activo']) ? 1 : 0;

    if (empty($idCompaniaForm) || empty($nombreTarifaForm)) {
        $mensajeError = "La compañía vinculada y el nombre comercial son obligatorios.";
    } else {
        try {
            $sentenciaUpdate = "UPDATE tarifas SET 
                    tar_comp_id = ?, tar_nombre_tarifa = ?, 
                    tar_precio_energia_p1 = ?, tar_precio_energia_p2 = ?, tar_precio_energia_p3 = ?, 
                    tar_precio_potencia_p1 = ?, tar_precio_potencia_p2 = ?, 
                    tar_observaciones = ?, tar_activo = ?,
                    tar_fecha_actualizacion = CURDATE()
                    WHERE tar_id = ?";
            
            $operacion = $conn->prepare($sentenciaUpdate);
            $operacion->execute([
                $idCompaniaForm, $nombreTarifaForm,
                $eneP1Form, $eneP2Form, $eneP3Form,
                $potP1Form, $potP2Form, $notasForm, $estadoActivado, $idTarifa
            ]);
            
            $mensajeExito = "La tarifa se ha actualizado correctamente en el sistema.";
            
            // Refrescamos datos locales
            $tarifaActual['tar_comp_id'] = $idCompaniaForm;
            $tarifaActual['tar_nombre_tarifa'] = $nombreTarifaForm;
            $tarifaActual['tar_precio_energia_p1'] = $eneP1Form;
            $tarifaActual['tar_precio_energia_p2'] = $eneP2Form;
            $tarifaActual['tar_precio_energia_p3'] = $eneP3Form;
            $tarifaActual['tar_precio_potencia_p1'] = $potP1Form;
            $tarifaActual['tar_precio_potencia_p2'] = $potP2Form;
            $tarifaActual['tar_observaciones'] = $notasForm;
            $tarifaActual['tar_activo'] = $estadoActivado;
            
        } catch (PDOException $errorUpdate) {
            $mensajeError = "Error técnico al guardar: " . $errorUpdate->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Tarifa — Lucina Admin</title>
    
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
            <a href="companias.php" class="sidebar-link"><i class="bi bi-building me-2"></i> Compañías</a>
            <a href="tarifas.php" class="sidebar-link active"><i class="bi bi-lightning-charge me-2"></i> Catálogo de Tarifas</a>
            <div class="sidebar-label text-uppercase small opacity-50 px-3 py-2 mt-3">CRM Clientes</div>
            <a href="clientes.php" class="sidebar-link"><i class="bi bi-person-lines-fill me-2"></i> Presupuestos</a>
        </nav>
        <div class="sidebar-footer mt-auto p-3 border-top">
            <a href="../salir.php" class="text-danger text-decoration-none small fw-bold"><i class="bi bi-box-arrow-left me-1"></i> Cerrar sesión</a>
        </div>
    </aside>

    <main class="main bg-light">
        <div class="topbar bg-white shadow-sm px-4 py-3 d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 fw-bold">Editar Tarifa: <span class="text-primary"><?= htmlspecialchars($tarifaActual['tar_nombre_tarifa']) ?></span></h4>
            <a href="tarifas.php" class="btn btn-outline-secondary btn-sm px-3 rounded-3">
                <i class="bi bi-reply me-1"></i> Cancelar y Volver
            </a>
        </div>

        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    
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
                                        <label class="form-label small fw-bold text-muted">Compañía Gestora *</label>
                                        <select name="comp_id" class="form-select" required>
                                            <?php foreach ($listadoCompanias as $cia): ?>
                                                <option value="<?= $cia['comp_id'] ?>" <?= $cia['comp_id'] == $tarifaActual['tar_comp_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($cia['comp_nombre']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Nombre de la Oferta *</label>
                                        <input type="text" name="nombre" class="form-control" 
                                            value="<?= htmlspecialchars($tarifaActual['tar_nombre_tarifa']) ?>" required>
                                    </div>

                                    <div class="col-12 border-top pt-4">
                                        <h6 class="text-primary fw-bold"><i class="bi bi-lightning me-2"></i>Precios de Energía (€/kWh)</h6>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Precio P1 (Punta)</label>
                                        <input type="number" step="0.000001" name="p_ene_p1" class="form-control" value="<?= $tarifaActual['tar_precio_energia_p1'] ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Precio P2 (Llano)</label>
                                        <input type="number" step="0.000001" name="p_ene_p2" class="form-control" value="<?= $tarifaActual['tar_precio_energia_p2'] ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Precio P3 (Valle)</label>
                                        <input type="number" step="0.000001" name="p_ene_p3" class="form-control" value="<?= $tarifaActual['tar_precio_energia_p3'] ?>">
                                    </div>

                                    <div class="col-12 border-top pt-4">
                                        <h6 class="text-primary fw-bold"><i class="bi bi-plug me-2"></i>Témino Fijo de Potencia (€/kW/día)</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Precio Potencia P1</label>
                                        <input type="number" step="0.000001" name="p_pot_p1" class="form-control" value="<?= $tarifaActual['tar_precio_potencia_p1'] ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Precio Potencia P2</label>
                                        <input type="number" step="0.000001" name="p_pot_p2" class="form-control" value="<?= $tarifaActual['tar_precio_potencia_p2'] ?>">
                                    </div>

                                    <div class="col-12 border-top pt-4">
                                        <label class="form-label small fw-bold text-muted">Condiciones Adicionales</label>
                                        <textarea name="observaciones" class="form-control" rows="3"><?= htmlspecialchars($tarifaActual['tar_observaciones']) ?></textarea>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch p-3 bg-light rounded border">
                                            <input class="form-check-input ms-0 me-2" type="checkbox" name="activo" id="activo" 
                                                <?= $tarifaActual['tar_activo'] ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-bold" for="activo">Tarifa habilitada en el comparador público</label>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-4 text-end">
                                        <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                                            <i class="bi bi-check2-circle me-1"></i> Guardar Cambios
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
