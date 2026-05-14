<?php
/**
 * Lucina - Comparador Energético (v1.0 Entrega DAW)
 * Módulo: Gestión de Compañías
 * Descripción: Permite administrar las empresas comercializadoras de energía
 * disponibles en el comparador, incluyendo logotipos y estado de activación.
 */

require_once '../funciones/conexion.php';
require_once '../funciones/sesiones.php';

// Verificación de acceso seguro
requerirLogin();

$mensajeError = "";
$mensajeExito = "";

// Lógica de eliminación de registros
if (isset($_GET['eliminar'])) {
    $idBaja = (int)$_GET['eliminar'];
    try {
        // Borrado lógico: Se desactiva la compañía en lugar de borrarla
        $consultaBaja = $conn->prepare("UPDATE companias SET comp_activo = 0 WHERE comp_id = ?");
        $consultaBaja->execute([$idBaja]);
        $mensajeExito = "Compañía ocultada correctamente.";
    } catch (PDOException $errorBaja) {
        $mensajeError = "Fallo técnico al intentar borrar: " . $errorBaja->getMessage();
    }
}

// Carga de la lista de compañías activas e inactivas
try {
    $consultaCias = $conn->query("SELECT * FROM companias ORDER BY comp_nombre ASC");
    $listadoCompanias = $consultaCias->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $errorCarga) {
    $listadoCompanias = [];
    $mensajeError = "No se pudo recuperar la lista de compañías.";
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Compañías — Lucina Admin</title>
    
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
        <!-- Cabecera de gestión -->
        <div class="topbar bg-white shadow-sm px-4 py-3 d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 fw-bold">Compañías Eléctricas</h4>
            <a href="nueva_compania.php" class="btn btn-primary px-3 shadow-sm rounded-3">
                <i class="bi bi-plus-lg"></i> Registrar Nueva
            </a>
        </div>

        <div class="container-fluid">
            <!-- Avisos al administrador -->
            <?php if ($mensajeError): ?>
                <div class="alert alert-danger border-0 shadow-sm"><?= $mensajeError ?></div>
            <?php endif; ?>
            <?php if ($mensajeExito): ?>
                <div class="alert alert-success border-0 shadow-sm"><?= $mensajeExito ?></div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-list-ul me-2"></i>Cartera de Proveedores</h6>
                    <span class="badge bg-secondary-subtle text-secondary px-2"><?= count($listadoCompanias) ?> empresas</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Nombre Comercial</th>
                                <th>Imagen de Marca</th>
                                <th>Vigencia</th>
                                <th class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($listadoCompanias)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">No hay compañías definidas.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($listadoCompanias as $cia): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">
                                            <?= htmlspecialchars($cia['comp_nombre']) ?>
                                            <?= !$cia['comp_activo'] ? ' <span class="badge bg-secondary text-uppercase ms-1" style="font-size: 0.7rem;">NO ACTIVO</span>' : '' ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($cia['comp_logo_url'])): ?>
                                                <img src="<?= htmlspecialchars($cia['comp_logo_url']) ?>" class="rounded" style="height: 30px; object-fit: contain;">
                                            <?php else: ?>
                                                <span class="text-muted small italic">Sin logotipo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $cia['comp_activo'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?> px-2">
                                                <?= $cia['comp_activo'] ? 'Operativa' : 'NO ACTIVO' ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="editar_compania.php?id=<?= $cia['comp_id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="companias.php?eliminar=<?= $cia['comp_id'] ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('¿Seguro que deseas eliminar esta compañía?')">
                                                <i class="bi bi-trash"></i>
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
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
