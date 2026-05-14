<?php
/**
 * Lucina - Comparador Energético (v1.0 Entrega DAW)
 * Módulo: Edición de Presupuesto / Estudio Técnico
 * Descripción: Permite al gestor corregir manualmente los datos extraídos por
 * el motor de análisis en caso de error o refinamiento.
 */

require_once '../funciones/conexion.php';
require_once '../funciones/sesiones.php';

// Verificación de privilegios
requerirLogin();

$idEstudio = (int)($_GET['id'] ?? 0);

if (!$idEstudio) {
    header('Location: clientes.php');
    exit;
}

// RECUPERACIÓN DE DATOS: Cargamos el estudio y el nombre del propietario
try {
    $consultaEstudio = $conn->prepare("SELECT f.*, c.cli_nombre FROM facturas f JOIN clientes c ON f.fac_cli_id = c.cli_id WHERE f.fac_id = ?");
    $consultaEstudio->execute([$idEstudio]);
    $estudio = $consultaEstudio->fetch(PDO::FETCH_ASSOC);

    if (!$estudio) {
        header('Location: clientes.php');
        exit;
    }
} catch (PDOException $errorCarga) {
    die("Error al conectar con la base de datos.");
}

$mensajeError = '';
$mensajeExito = '';

// PROCESAMIENTO DE CAMBIOS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cups      = trim($_POST['cups'] ?? '');
    $p1        = (float)($_POST['p1'] ?? 0);
    $p2        = (float)($_POST['p2'] ?? 0);
    $p3        = (float)($_POST['p3'] ?? 0);
    $pot1      = (float)($_POST['pot1'] ?? 0);
    $pot2      = (float)($_POST['pot2'] ?? 0);
    $importe   = (float)($_POST['importe'] ?? 0);

    try {
        $sentenciaUpdate = $conn->prepare("UPDATE facturas SET 
            fac_cups = ?, 
            fac_consumo_p1_kwh = ?, 
            fac_consumo_p2_kwh = ?, 
            fac_consumo_p3_kwh = ?, 
            fac_potencia_contratada_p1_kw = ?, 
            fac_potencia_contratada_p2_kw = ?, 
            fac_importe_total_factura = ?
            WHERE fac_id = ?");
        
        $ejecucion = $sentenciaUpdate->execute([$cups, $p1, $p2, $p3, $pot1, $pot2, $importe, $idEstudio]);
        
        if ($ejecucion) {
            $mensajeExito = 'Los datos del estudio técnico han sido actualizados.';
            // Refrescamos localmente
            $estudio['fac_cups'] = $cups;
            $estudio['fac_consumo_p1_kwh'] = $p1;
            $estudio['fac_consumo_p2_kwh'] = $p2;
            $estudio['fac_consumo_p3_kwh'] = $p3;
            $estudio['fac_potencia_contratada_p1_kw'] = $pot1;
            $estudio['fac_potencia_contratada_p2_kw'] = $pot2;
            $estudio['fac_importe_total_factura'] = $importe;
        } else {
            $mensajeError = 'Fallo en la ejecución de la consulta de actualización.';
        }
    } catch (PDOException $errorSQL) {
        $mensajeError = 'Error técnico en la base de datos: ' . $errorSQL->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edición Técnica — Lucina Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
            <a href="tarifas.php" class="sidebar-link"><i class="bi bi-lightning-charge me-2"></i> Catálogo de Tarifas</a>
            <div class="sidebar-label text-uppercase small opacity-50 px-3 py-2 mt-3">CRM Clientes</div>
            <a href="clientes.php" class="sidebar-link active"><i class="bi bi-person-lines-fill me-2"></i> Presupuestos</a>
        </nav>
        <div class="sidebar-footer mt-auto p-3 border-top">
            <a href="../salir.php" class="text-danger text-decoration-none small fw-bold"><i class="bi bi-box-arrow-left me-1"></i> Cerrar sesión</a>
        </div>
    </aside>

    <main class="main bg-light">
        <div class="topbar bg-white shadow-sm px-4 py-3 d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 fw-bold">Refinar Datos del Estudio</h4>
            <a href="ver_cliente.php?id=<?= $estudio['fac_cli_id'] ?>" class="btn btn-outline-secondary btn-sm px-3 rounded-3">
                <i class="bi bi-arrow-left me-1"></i> Volver al Expediente
            </a>
        </div>

        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    
                    <div class="mb-3">
                        <span class="badge bg-primary px-3 rounded-pill mb-2">Editor Técnico</span>
                        <h2 class="fw-bold">Gestión de Factura</h2>
                        <p class="text-muted">Propietario del punto de suministro: <strong><?= htmlspecialchars($estudio['cli_nombre']) ?></strong></p>
                    </div>

                    <?php if ($mensajeExito): ?>
                        <div class="alert alert-success border-0 shadow-sm mb-4"><?= $mensajeExito ?></div>
                    <?php endif; ?>
                    <?php if ($mensajeError): ?>
                        <div class="alert alert-danger border-0 shadow-sm mb-4"><?= $mensajeError ?></div>
                    <?php endif; ?>

                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body p-4">
                            <form action="" method="POST">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted">Código CUPS Oficial</label>
                                        <input type="text" name="cups" class="form-control" value="<?= htmlspecialchars($estudio['fac_cups']) ?>" required>
                                    </div>
                                    
                                    <div class="col-12 border-top pt-3">
                                        <h6 class="text-primary fw-bold small text-uppercase">Desglose de Consumo (kWh)</h6>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Energía P1</label>
                                        <input type="number" step="0.01" name="p1" class="form-control" value="<?= $estudio['fac_consumo_p1_kwh'] ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Energía P2</label>
                                        <input type="number" step="0.01" name="p2" class="form-control" value="<?= $estudio['fac_consumo_p2_kwh'] ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Energía P3</label>
                                        <input type="number" step="0.01" name="p3" class="form-control" value="<?= $estudio['fac_consumo_p3_kwh'] ?>" required>
                                    </div>

                                    <div class="col-md-6 border-top pt-3">
                                        <label class="form-label small fw-bold text-primary text-uppercase">Potencia P1 (kW)</label>
                                        <input type="number" step="0.01" name="pot1" class="form-control" value="<?= $estudio['fac_potencia_contratada_p1_kw'] ?>" required>
                                    </div>
                                    <div class="col-md-6 border-top pt-3">
                                        <label class="form-label small fw-bold text-primary text-uppercase">Potencia P2 (kW)</label>
                                        <input type="number" step="0.01" name="pot2" class="form-control" value="<?= $estudio['fac_potencia_contratada_p2_kw'] ?>" required>
                                    </div>

                                    <div class="col-12 border-top pt-4">
                                        <label class="form-label small fw-bold text-danger text-uppercase">Importe Final de la Factura (€)</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" name="importe" class="form-control form-control-lg border-danger" value="<?= $estudio['fac_importe_total_factura'] ?>" required>
                                            <span class="input-group-text bg-danger-subtle text-danger border-danger">€</span>
                                        </div>
                                        <small class="text-muted italic">Valor bruto con impuestos para el motor de comparativa.</small>
                                    </div>

                                    <div class="col-12 mt-4 text-end">
                                        <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                                            <i class="bi bi-save me-2"></i> Guardar Modificaciones
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
