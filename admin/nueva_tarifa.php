<?php
/**
 * Lucina - Comparador Energético (DAW DUAL - Proyecto Intermodular - 25/26)
 * Módulo: Alta de Tarifas
 * Descripción: Formulario para el registro de nuevas ofertas económicas en el 
 * sistema, vinculándolas a una compañía comercializadora existente.
 */

require_once '../funciones/conexion.php';
require_once '../funciones/sesiones.php';

// Verificación de privilegios
requerirLogin();

$mensajeError = "";
$mensajeExito = "";

// Carga de compañías para el desplegable del formulario
try {
    $consultaCias = $conn->query("SELECT comp_id, comp_nombre FROM companias WHERE comp_activo = 1 ORDER BY comp_nombre ASC");
    $listadoCompanias = $consultaCias->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $errorCarga) {
    $listadoCompanias = [];
    $mensajeError = "No se pudieron precargar las compañías comercializadoras.";
}

// Procesamiento del formulario de inserción
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idCompania      = $_POST['comp_id'] ?? '';
    $nombreTarifa    = trim($_POST['nombre'] ?? '');
    $precioEneP1     = $_POST['p_ene_p1'] ?? 0;
    $precioEneP2     = $_POST['p_ene_p2'] ?? 0;
    $precioEneP3     = $_POST['p_ene_p3'] ?? 0;
    $precioPotP1     = $_POST['p_pot_p1'] ?? 0;
    $precioPotP2     = $_POST['p_pot_p2'] ?? 0;
    $notas           = trim($_POST['observaciones'] ?? '');
    $estadoActivo    = isset($_POST['activo']) ? 1 : 0;
    $fechaActual     = date('Y-m-d');

    if (empty($idCompania) || empty($nombreTarifa)) {
        $mensajeError = "Es obligatorio seleccionar una compañía e indicar el nombre comercial de la tarifa.";
    } else {
        try {
            $sentenciaInsert = "INSERT INTO tarifas 
                    (tar_comp_id, tar_nombre_tarifa, tar_fecha_actualizacion, 
                     tar_precio_energia_p1, tar_precio_energia_p2, tar_precio_energia_p3, 
                     tar_precio_potencia_p1, tar_precio_potencia_p2, tar_observaciones, tar_activo) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $operacion = $conn->prepare($sentenciaInsert);
            $operacion->execute([
                $idCompania, $nombreTarifa, $fechaActual,
                $precioEneP1, $precioEneP2, $precioEneP3,
                $precioPotP1, $precioPotP2, $notas, $estadoActivo
            ]);
            
            $mensajeExito = "La nueva tarifa ha sido incorporada al catálogo con éxito.";
        } catch (PDOException $errorInsert) {
            $mensajeError = "Error en la persistencia del registro: " . $errorInsert->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Tarifa — Lucina Admin</title>
    
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
            <h4 class="mb-0 fw-bold">Definir Nueva Tarifa</h4>
            <a href="tarifas.php" class="btn btn-outline-secondary btn-sm px-3 rounded-3">
                <i class="bi bi-reply-all me-1"></i> Regresar al Listado
            </a>
        </div>

        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body p-4">
                            <!-- Feedback de operación -->
                            <?php if ($mensajeError): ?>
                                <div class="alert alert-danger border-0"><?= $mensajeError ?></div>
                            <?php endif; ?>
                            <?php if ($mensajeExito): ?>
                                <div class="alert alert-success border-0"><?= $mensajeExito ?></div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Compañía Eléctrica</label>
                                        <select name="comp_id" class="form-select" required>
                                            <option value="">-- Seleccionar Marca --</option>
                                            <?php foreach ($listadoCompanias as $cia): ?>
                                                <option value="<?= $cia['comp_id'] ?>">
                                                    <?= htmlspecialchars($cia['comp_nombre']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Nombre Comercial</label>
                                        <input type="text" name="nombre" class="form-control"
                                            placeholder="Ej. Plan Transparente, Tarifa Flat..." required>
                                    </div>

                                    <div class="col-12 border-top pt-4 pt-1">
                                        <h6 class="text-primary fw-bold"><i class="bi bi-lightning me-2"></i>Costes de Energía (€/kWh)</h6>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Tramo P1 (Punta)</label>
                                        <input type="number" step="0.000001" name="p_ene_p1" class="form-control" value="0.000000">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Tramo P2 (Llano)</label>
                                        <input type="number" step="0.000001" name="p_ene_p2" class="form-control" value="0.000000">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Tramo P3 (Valle)</label>
                                        <input type="number" step="0.000001" name="p_ene_p3" class="form-control" value="0.000000">
                                    </div>

                                    <div class="col-12 border-top pt-4 mt-1">
                                        <h6 class="text-primary fw-bold"><i class="bi bi-plug me-2"></i>Costes de Potencia (€/kW/día)</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Término Fijo P1</label>
                                        <input type="number" step="0.000001" name="p_pot_p1" class="form-control" value="0.000000">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Término Fijo P2</label>
                                        <input type="number" step="0.000001" name="p_pot_p2" class="form-control" value="0.000000">
                                    </div>

                                    <div class="col-12 border-top pt-4 mt-1">
                                        <label class="form-label small fw-bold text-muted">Observaciones y Condiciones</label>
                                        <textarea name="observaciones" class="form-control" rows="3" placeholder="Información técnica adicional..."></textarea>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch bg-light p-3 rounded border">
                                            <input class="form-check-input ms-0 me-2" type="checkbox" name="activo" id="activo" checked>
                                            <label class="form-check-label fw-bold" for="activo">Tarifa disponible públicamente</label>
                                        </div>
                                    </div>
                                    <div class="col-12 text-end mt-4">
                                        <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                                            <i class="bi bi-save me-2"></i> Registrar Oferta
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
