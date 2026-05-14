<?php
require_once '../funciones/conexion.php';
require_once '../funciones/sesiones.php';

requerirLogin();

$mensajeError = null;

try {
    $conteoClientes  = $conn->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
    $conteoCompanias = $conn->query("SELECT COUNT(*) FROM companias WHERE comp_activo = 1")->fetchColumn();
    $conteoTarifas   = $conn->query("SELECT COUNT(*) FROM tarifas WHERE tar_activo = 1")->fetchColumn();
    $conteoFacturas  = $conn->query("SELECT COUNT(*) FROM facturas")->fetchColumn();

    $listadoRecientes = $conn->query("SELECT c.*,
                            (SELECT MAX(fac_fecha) FROM facturas WHERE fac_cli_id = c.cli_id) as ultima_fecha
                            FROM clientes c
                            ORDER BY c.cli_id DESC
                            LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $conteoClientes = $conteoCompanias = $conteoTarifas = $conteoFacturas = 0;
    $listadoRecientes = [];
    // Si llega aquí probablemente las tablas no existen en la BD
    $mensajeError = "Error al cargar los datos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración — Lucina</title>
    
    <!-- Librerías de estilos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="icon" href="../favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="admin.css">
</head>

<body>

    <!-- Menú de Navegación Lateral -->
    <aside class="sidebar shadow">
        <div class="sidebar-brand d-flex align-items-center mb-4">
            <img src="../logo.png" alt="Lucina" style="max-height: 40px;">
            <span class="ms-2 fw-bold text-primary">Lucina Admin</span>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-label text-uppercase small opacity-50 px-3 py-2">Resumen</div>
            <a href="index.php" class="sidebar-link active">
                <i class="bi bi-speedometer2 me-2"></i> Escritorio
            </a>
            <a href="companias.php" class="sidebar-link">
                <i class="bi bi-building me-2"></i> Compañías
            </a>
            <a href="tarifas.php" class="sidebar-link">
                <i class="bi bi-lightning-charge me-2"></i> Catálogo de Tarifas
            </a>
            
            <div class="sidebar-label text-uppercase small opacity-50 px-3 py-2 mt-3">CRM Clientes</div>
            <a href="clientes.php" class="sidebar-link">
                <i class="bi bi-person-lines-fill me-2"></i> Presupuestos
            </a>
        </nav>
        <div class="sidebar-footer mt-auto p-3 border-top">
            <a href="../salir.php" class="text-danger text-decoration-none small fw-bold"><i class="bi bi-box-arrow-left me-1"></i> Cerrar sesión</a>
        </div>
    </aside>

    <!-- Área de Contenido Principal -->
    <main class="main bg-light">

        <!-- Barra Superior -->
        <div class="topbar bg-white shadow-sm px-4 py-3 d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 fw-bold">Escritorio Principal</h4>
            <div class="d-flex align-items-center">
                <span class="badge bg-primary-subtle text-primary border border-primary px-3 rounded-pill">
                    <i class="bi bi-shield-lock me-1"></i> Sesión: Administrador
                </span>
            </div>
        </div>

        <div class="container-fluid">

            <?php if ($mensajeError): ?>
                <div class="alert alert-danger mb-4">
                    <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($mensajeError) ?>
                </div>
            <?php endif; ?>
            <div class="row g-4 mb-5">
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card border-0 shadow-sm p-3 bg-white rounded-3 d-flex align-items-center h-100">
                        <div class="stat-icon bg-primary-subtle text-primary p-3 rounded-circle me-3">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                        <div>
                            <div class="h3 fw-bold mb-0"><?= $conteoClientes ?></div>
                            <div class="small text-muted">Clientes Totales</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card border-0 shadow-sm p-3 bg-white rounded-3 d-flex align-items-center h-100">
                        <div class="stat-icon bg-success-subtle text-success p-3 rounded-circle me-3">
                            <i class="bi bi-building-check fs-4"></i>
                        </div>
                        <div>
                            <div class="h3 fw-bold mb-0"><?= $conteoCompanias ?></div>
                            <div class="small text-muted">Compañías Activas</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card border-0 shadow-sm p-3 bg-white rounded-3 d-flex align-items-center h-100">
                        <div class="stat-icon bg-warning-subtle text-warning p-3 rounded-circle me-3">
                            <i class="bi bi-tags fs-4"></i>
                        </div>
                        <div>
                            <div class="h3 fw-bold mb-0"><?= $conteoTarifas ?></div>
                            <div class="small text-muted">Tarifas Publicadas</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card border-0 shadow-sm p-3 bg-white rounded-3 d-flex align-items-center h-100">
                        <div class="stat-icon bg-info-subtle text-info p-3 rounded-circle me-3">
                            <i class="bi bi-file-earmark-bar-graph fs-4"></i>
                        </div>
                        <div>
                            <div class="h3 fw-bold mb-0"><?= $conteoFacturas ?></div>
                            <div class="small text-muted">Análisis Realizados</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Actividad Reciente -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-stars text-warning me-2"></i>Últimas Solicitudes de Ahorro</h6>
                            <a href="clientes.php" class="btn btn-sm btn-link text-decoration-none">Ver gestión completa →</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Cliente</th>
                                        <th>Contacto Principal</th>
                                        <th>Última Actividad</th>
                                        <th>Estado</th>
                                        <th class="text-end pe-4">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($listadoRecientes)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted fst-italic">
                                                No hay registros recientes para mostrar.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($listadoRecientes as $item): ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($item['cli_nombre']) ?></div>
                                                </td>
                                                <td>
                                                    <div class="small text-muted"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($item['cli_email'] ?? '—') ?></div>
                                                </td>
                                                <td class="small">
                                                    <?= $item['ultima_fecha'] ? date('d/m/Y H:i', strtotime($item['ultima_fecha'])) : 'Fase inicial' ?>
                                                </td>
                                                <td>
                                                    <?php if ($item['cli_activo']): ?>
                                                        <span class="badge bg-success-subtle text-success border border-success px-2 rounded-pill small">Activo</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary-subtle text-secondary border px-2 rounded-pill small">Inactivo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <a href="ver_cliente.php?id=<?= $item['cli_id'] ?>" class="btn btn-sm btn-outline-primary rounded-circle" title="Ver Expediente">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts de soporte -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
