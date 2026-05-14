<?php
/**
 * Lucina - Comparador Energético (DAW DUAL - Proyecto Intermodular - 25/26)
 * Módulo: Gestión de Clientes (CRM)
 * Descripción: Listado maestro de clientes registrados en el sistema.
 * Permite la visualización de expedientes y la eliminación completa de registros
 * (incluyendo limpieza de archivos físicos en el servidor).
 */

require_once '../funciones/conexion.php';
require_once '../funciones/sesiones.php';

// Verificación de acceso restringido
requerirLogin();

$mensajeError = "";
$mensajeExito = "";

// Lógica de eliminación de expedientes
if (isset($_GET['eliminar'])) {
    $idClienteBaja = (int)$_GET['eliminar'];
    try {
        // Iniciamos transacción para asegurar consistencia entre BD y Disco
        $conn->beginTransaction();
        
        // 1. Localización de archivos PDF asociados para su borrado físico posterior
        $consultaRutas = $conn->prepare("SELECT fac_ruta_archivo FROM facturas WHERE fac_cli_id = ?");
        $consultaRutas->execute([$idClienteBaja]);
        $listadoArchivos = $consultaRutas->fetchAll(PDO::FETCH_ASSOC);

        // 2. Limpieza de registros de facturación en la base de datos
        $borraFacturas = $conn->prepare("DELETE FROM facturas WHERE fac_cli_id = ?");
        $borraFacturas->execute([$idClienteBaja]);
        
        // 3. Eliminación del perfil del cliente
        $borraCliente = $conn->prepare("DELETE FROM clientes WHERE cli_id = ?");
        $borraCliente->execute([$idClienteBaja]);
        
        $conn->commit();

        // 4. Mantenimiento del servidor: eliminación de ficheros huérfanos
        foreach ($listadoArchivos as $archivo) {
            $nombreFichero = $archivo['fac_ruta_archivo'];
            // Seguridad: No eliminamos archivos de la carpeta de demostración
            if (!empty($nombreFichero) && strpos($nombreFichero, 'demo/') === false) {
                $rutaAlDisco = "../archivos/" . $nombreFichero;
                if (file_exists($rutaAlDisco)) {
                    unlink($rutaAlDisco);
                }
            }
        }

        $mensajeExito = "El expediente y sus documentos asociados se han eliminado del sistema.";
    } catch (PDOException $errorBaja) {
        if ($conn->inTransaction()) $conn->rollBack();
        $mensajeError = "No se pudo procesar la baja: " . $errorBaja->getMessage();
    }
}

// Consulta de la base de clientes con estadísticas de facturación
try {
    $sentenciaClientes = "SELECT c.*, (SELECT COUNT(*) FROM facturas f WHERE f.fac_cli_id = c.cli_id) as total_facturas 
                          FROM clientes c 
                          ORDER BY c.cli_id DESC";
    $operacion = $conn->query($sentenciaClientes);
    $listadoClientes = $operacion->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $errorCarga) {
    $listadoClientes = [];
    $mensajeError = "Fallo al conectar con el repositorio de clientes.";
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suministros y Clientes — Lucina Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="icon" href="../favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="admin.css">
</head>

<body>

    <!-- Panel de Navegación -->
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
        <!-- Cabecera -->
        <div class="topbar bg-white shadow-sm px-4 py-3 d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 fw-bold">Gestión de Presupuestos</h4>
            <span class="badge bg-secondary-subtle text-secondary border px-3 rounded-pill h6 mb-0">
                <?= count($listadoClientes) ?> Registros en BD
            </span>
        </div>

        <div class="container-fluid">
            <!-- Mensajes de Feedback -->
            <?php if ($mensajeError): ?>
                <div class="alert alert-danger shadow-sm border-0 alert-dismissible fade show">
                    <i class="bi bi-exclamation-octagon me-2"></i> <?= $mensajeError ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($mensajeExito): ?>
                <div class="alert alert-success shadow-sm border-0 alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i> <?= $mensajeExito ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Listado Principal -->
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-people-fill text-primary me-2"></i>Clientes registrados en el comparador</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Identidad</th>
                                <th>Contacto</th>
                                <th>Localidad</th>
                                <th>Actividad</th>
                                <th>Fecha Alta</th>
                                <th class="text-end pe-4">Operaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($listadoClientes)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        No existen clientes registrados actualmente.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($listadoClientes as $cli): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold me-3 shadow-sm" style="width: 40px; height: 40px; font-size: 1.1rem;">
                                                    <?= mb_strtoupper(mb_substr($cli['cli_nombre'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark fs-6" style="color: #000 !important;"><?= htmlspecialchars($cli['cli_nombre']) ?></div>
                                                    <div class="text-muted small">ID: #<?= $cli['cli_id'] ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="small">
                                            <div><i class="bi bi-envelope text-muted me-1"></i><?= htmlspecialchars($cli['cli_email'] ?? '—') ?></div>
                                            <div><i class="bi bi-telephone text-muted me-1"></i><?= htmlspecialchars($cli['cli_telefono'] ?? '—') ?></div>
                                        </td>
                                        <td>
                                            <span class="small text-muted text-uppercase"><?= htmlspecialchars($cli['cli_poblacion'] ?? 'Desc.') ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-blue-lucina rounded-pill" style="font-size: 0.75rem;">
                                                <?= $cli['total_facturas'] ?> Análisis
                                            </span>
                                        </td>
                                        <td>
                                            <span class="small text-muted"><?= !empty($cli['cli_fecha_alta']) ? date('d/m/Y', strtotime($cli['cli_fecha_alta'])) : '—' ?></span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group">
                                                <a href="ver_cliente.php?id=<?= $cli['cli_id'] ?>" class="btn btn-sm btn-outline-primary" title="Ver Expediente">
                                                    <i class="bi bi-journal-text"></i>
                                                </a>
                                                <a href="clientes.php?eliminar=<?= $cli['cli_id'] ?>" 
                                                   class="btn btn-sm btn-outline-danger" 
                                                   onclick="return confirm('¿Eliminar definitivamente este cliente y sus estudios?')"
                                                   title="Eliminar">
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
