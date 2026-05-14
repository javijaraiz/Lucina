<?php
/**
 * Lucina - Comparador Energético (DAW DUAL - Proyecto Intermodular - 25/26)
 * Módulo: Catálogo de Tarifas
 * Descripción: Gestión del listado de precios de energía y potencia. Permite
 * visualizar, editar y dar de baja las ofertas comerciales vigentes en el sistema.
 */

require_once '../funciones/conexion.php';
require_once '../funciones/sesiones.php';

// Control de acceso para administradores
requerirLogin();

$mensajeError = "";
$mensajeExito = "";

// Lógica de eliminación de tarifa
if (isset($_GET['eliminar'])) {
    $idTarifaBaja = (int)$_GET['eliminar'];
    try {
        $consultaBaja = $conn->prepare("DELETE FROM tarifas WHERE tar_id = ?");
        $consultaBaja->execute([$idTarifaBaja]);
        $mensajeExito = "La tarifa se ha eliminado correctamente del catálogo.";
    } catch (PDOException $errorBaja) {
        $mensajeError = "No se ha podido eliminar la tarifa: " . $errorBaja->getMessage();
    }
}

// Carga de datos uniendo tarifas con sus respectivas compañías comercializadoras
try {
    $sentenciaTarifas = "SELECT t.*, c.comp_nombre 
                         FROM tarifas t 
                         INNER JOIN companias c ON t.tar_comp_id = c.comp_id 
                         ORDER BY c.comp_nombre ASC, t.tar_nombre_tarifa ASC";
    $operacion = $conn->query($sentenciaTarifas);
    $listadoTarifas = $operacion->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $errorCarga) {
    $listadoTarifas = [];
    $mensajeError = "Fallo al cargar el listado de tarifas desde la base de datos.";
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Tarifas — Lucina Admin</title>
    
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
        <!-- Cabecera de gestión -->
        <div class="topbar bg-white shadow-sm px-4 py-3 d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 fw-bold">Gestión de Tarifas</h4>
            <a href="nueva_tarifa.php" class="btn btn-primary px-3 shadow-sm rounded-3">
                <i class="bi bi-plus-lg"></i> Crear Nueva Tarifa
            </a>
        </div>

        <div class="container-fluid">
            <!-- Alertas de estado -->
            <?php if ($mensajeError): ?>
                <div class="alert alert-danger border-0 shadow-sm"><?= $mensajeError ?></div>
            <?php endif; ?>
            <?php if ($mensajeExito): ?>
                <div class="alert alert-success border-0 shadow-sm"><?= $mensajeExito ?></div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Tarifas de Luz Publicadas</h6>
                    <span class="badge bg-secondary-subtle text-secondary"><?= count($listadoTarifas) ?> registros</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Comercializadora</th>
                                <th>Nombre de la Oferta</th>
                                <th>Eenergía (P1)</th>
                                <th>Energía (P2)</th>
                                <th>Energía (P3)</th>
                                <th>Estado</th>
                                <th class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($listadoTarifas)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">No existen ofertas registradas en el catálogo.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($listadoTarifas as $tarifa): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-bold text-primary"><?= htmlspecialchars($tarifa['comp_nombre']) ?></span>
                                        </td>
                                        <td class="small fw-semibold"><?= htmlspecialchars($tarifa['tar_nombre_tarifa']) ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= number_format($tarifa['tar_precio_energia_p1'], 4) ?> €</span></td>
                                        <td><span class="badge bg-light text-dark border"><?= number_format($tarifa['tar_precio_energia_p2'], 4) ?> €</span></td>
                                        <td><span class="badge bg-light text-dark border"><?= number_format($tarifa['tar_precio_energia_p3'], 4) ?> €</span></td>
                                        <td>
                                            <span class="badge <?= $tarifa['tar_activo'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?> px-2">
                                                <?= $tarifa['tar_activo'] ? 'Activa' : 'Baja' ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group">
                                                <a href="editar_tarifa.php?id=<?= $tarifa['tar_id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="tarifas.php?eliminar=<?= $tarifa['tar_id'] ?>" 
                                                   class="btn btn-sm btn-outline-danger" 
                                                   onclick="return confirm('¿Eliminar esta tarifa?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
