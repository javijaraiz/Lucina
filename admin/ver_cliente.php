<?php
/**
 * Lucina - Comparador Energético (DAW DUAL - Proyecto Intermodular - 25/26)
 * Módulo: Panel Administrativo - Ficha de Cliente
 * Descripción: Muestra detalladamente la información de un cliente y el historial
 * de facturas analizadas. Permite comparar dichas facturas con las tarifas
 * actuales del mercado almacenadas en la base de datos.
 */

require_once '../funciones/conexion.php';
require_once '../funciones/sesiones.php';

// Verificación de credenciales de administrador
requerirLogin();

// Recuperamos el identificador del cliente desde la URL
$idCliente = (int)($_GET['id'] ?? 0);

if (!$idCliente) {
    header('Location: clientes.php');
    exit();
}

/**
 * Función Auxiliar: Cálculo de costes para comparativa en el panel.
 * Replica la lógica de ahorro.php para asegurar coherencia en los datos mostrados al gestor.
 */
function calcularCosteExpediente($p1, $p2, $p3, $pot1, $pot2, $dias, $tarifa) {
    $energia = $p1 * ($tarifa['tar_precio_energia_p1'] ?? 0) +
               $p2 * ($tarifa['tar_precio_energia_p2'] ?? 0) +
               $p3 * ($tarifa['tar_precio_energia_p3'] ?? 0);

    $potencia = ($pot1 * ($tarifa['tar_precio_potencia_p1'] ?? 0) +
                 $pot2 * ($tarifa['tar_precio_potencia_p2'] ?? 0)) * $dias;

    $iee = ($energia + $potencia) * 0.051127; // Impuesto Especial Electricidad
    $alq = 0.027 * $dias; // Alquiler estimado
    
    $subtotal = $energia + $potencia + $iee + $alq;
    $total = $subtotal * 1.21; // IVA 21%

    return ['total_final' => $total];
}

// CARGA DE DATOS: Cliente, Historial y Tarifas
try {
    // 1. Datos básicos del cliente
    $consultaCli = $conn->prepare("SELECT * FROM clientes WHERE cli_id = ?");
    $consultaCli->execute([$idCliente]);
    $cliente = $consultaCli->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        header('Location: clientes.php');
        exit();
    }

    // 2. Historial de facturas procesadas
    $consultaFac = $conn->prepare("SELECT * FROM facturas WHERE fac_cli_id = ? ORDER BY fac_id DESC");
    $consultaFac->execute([$idCliente]);
    $historialFacturas = $consultaFac->fetchAll(PDO::FETCH_ASSOC);

    // 3. Tarifas vigentes para la tabla comparativa
    $consultaTar = $conn->query("SELECT t.*, c.comp_nombre, c.comp_logo_url FROM tarifas t JOIN companias c ON t.tar_comp_id = c.comp_id WHERE t.tar_activo = 1");
    $tarifasSistema = $consultaTar->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $errorBD) {
    die("Fallo crítico en el acceso a datos: " . $errorBD->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expediente: <?= htmlspecialchars($cliente['cli_nombre']) ?> — Lucina Pro</title>
    
    <!-- Estética corporativa y librerías -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="icon" href="../favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="admin.css">
</head>

<body>

    <!-- Menú Lateral (Sidebar) -->
    <aside class="sidebar shadow">
        <div class="sidebar-brand d-flex align-items-center mb-4">
            <img src="../logo.png" alt="Lucina" style="max-height: 40px;">
            <span class="ms-2 fw-bold text-primary">Lucina Admin</span>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-label text-uppercase small opacity-50 px-3 py-2">Gestión General</div>
            <a href="index.php" class="sidebar-link"><i class="bi bi-speedometer2 me-2"></i> Escritorio</a>
            <a href="companias.php" class="sidebar-link"><i class="bi bi-building me-2"></i> Compañías</a>
            <a href="tarifas.php" class="sidebar-link"><i class="bi bi-lightning-charge me-2"></i> Catálogo Tarifas</a>
            
            <div class="sidebar-label text-uppercase small opacity-50 px-3 py-2 mt-3">CRM Clientes</div>
            <a href="clientes.php" class="sidebar-link active"><i class="bi bi-person-lines-fill me-2"></i> Listado Clientes</a>
        </nav>
        <div class="sidebar-footer mt-auto p-3 border-top">
            <a href="../salir.php" class="text-danger text-decoration-none small fw-bold"><i class="bi bi-box-arrow-left me-1"></i> Cerrar Sesión</a>
        </div>
    </aside>

    <main class="main bg-light">
        <!-- Cabecera de acción -->
        <div class="topbar bg-white shadow-sm px-4 py-3 d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 fw-bold">Expediente del Cliente</h4>
            <a href="clientes.php" class="btn btn-outline-secondary btn-sm px-4">
                <i class="bi bi-reply me-1"></i> Volver
            </a>
        </div>

        <div class="container-fluid">
            <div class="row g-4">
                
                <!-- Columna Izquierda: Perfil del Cliente -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 pt-4 px-4">
                            <h6 class="fw-bold text-primary mb-0"><i class="bi bi-person-circle me-2"></i>Información Personal</h6>
                        </div>
                        <div class="card-body px-4">
                            <div class="text-center mb-4">
                                <div class="avatar-expediente mx-auto mb-3">
                                    <?php 
                                    $nombresArr = explode(" ", $cliente['cli_nombre']);
                                    echo mb_strtoupper(mb_substr($nombresArr[0], 0, 1) . mb_substr(end($nombresArr), 0, 1));
                                    ?>
                                </div>
                                <h5 class="fw-bold mb-1"><?= htmlspecialchars($cliente['cli_nombre']) ?></h5>
                                <span class="badge bg-success-subtle text-success border border-success px-3">Estado: Activo</span>
                            </div>
                            
                            <ul class="list-group list-group-flush small">
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted">Correo:</span>
                                    <span class="fw-bold"><?= htmlspecialchars($cliente['cli_email'] ?? '—') ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted">Móvil:</span>
                                    <span class="fw-bold"><?= htmlspecialchars($cliente['cli_telefono'] ?? '—') ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted">Población:</span>
                                    <span class="fw-bold"><?= htmlspecialchars($cliente['cli_poblacion'] ?? '—') ?></span>
                                </li>
                            </ul>

                            <div class="mt-4 p-3 bg-light rounded border">
                                <label class="small fw-bold text-primary d-block mb-2">Notas Internas</label>
                                <?php if (!empty($cliente['cli_observaciones'])): ?>
                                    <p class="small mb-0 <?= str_contains($cliente['cli_observaciones'], 'interés en') ? 'text-success fw-bold' : 'text-muted' ?>">
                                        <i class="bi bi-<?= str_contains($cliente['cli_observaciones'], 'interés en') ? 'hand-thumbs-up-fill text-success' : 'chat-left-text text-muted' ?> me-1"></i>
                                        <?= htmlspecialchars($cliente['cli_observaciones']) ?>
                                    </p>
                                <?php else: ?>
                                    <p class="small text-muted mb-0">Sin notas registradas.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Historial de Estudios -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-primary mb-0"><i class="bi bi-clock-history me-2"></i>Historial de Estudios Energéticos</h6>
                            <span class="badge bg-blue-lucina rounded-pill"><?= count($historialFacturas) ?> Registros</span>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($historialFacturas)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-clipboard-x fs-1 text-muted opacity-25 d-block mb-3"></i>
                                    <p class="text-muted">No se han realizado análisis para este cliente todavía.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($historialFacturas as $facturaItem): ?>
                                    <div class="estudio-item p-4 border-bottom">
                                        <div class="d-flex justify-content-between align-items-start mb-4">
                                            <div>
                                                <h6 class="fw-bold mb-1">CUPS: <span class="bg-light text-primary px-2 py-1 rounded"><?= htmlspecialchars($facturaItem['fac_cups']) ?></span></h6>
                                                <div class="small text-muted"><i class="bi bi-calendar-check me-1"></i> Fecha de carga: <?= date('d/m/Y H:i', strtotime($facturaItem['fac_fecha'])) ?></div>
                                            </div>
                                            <div class="text-end">
                                                <div class="small text-muted">Coste Actual</div>
                                                <h4 class="fw-bold text-primary mb-0"><?= number_format($facturaItem['fac_importe_total_factura'], 2) ?> €</h4>
                                            </div>
                                        </div>

                                        <!-- Resumen de consumos -->
                                        <div class="row g-2 text-center mb-4">
                                            <div class="col-6 col-md-3">
                                                <div class="consumo-box p-2 border rounded">
                                                    <div class="small text-muted">Punta (P1)</div>
                                                    <div class="fw-bold"><?= $facturaItem['fac_consumo_p1_kwh'] ?> kWh</div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="consumo-box p-2 border rounded">
                                                    <div class="small text-muted">Llano (P2)</div>
                                                    <div class="fw-bold"><?= $facturaItem['fac_consumo_p2_kwh'] ?> kWh</div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="consumo-box p-2 border rounded">
                                                    <div class="small text-muted">Valle (P3)</div>
                                                    <div class="fw-bold"><?= $facturaItem['fac_consumo_p3_kwh'] ?> kWh</div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="consumo-box p-2 border rounded">
                                                    <div class="small text-muted">Potencia P1</div>
                                                    <div class="fw-bold"><?= $facturaItem['fac_potencia_contratada_p1_kw'] ?> kW</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Comparativa interna con tarifas actuales -->
                                        <div class="comparativa-panel p-3 border rounded-3 bg-white shadow-sm mb-4">
                                            <p class="small fw-bold mb-3"><i class="bi bi-graph-up-arrow me-2"></i>Comparativa Directa por Tarifas del Sistema</p>
                                            <table class="table table-sm table-hover mb-0 fw-500" style="font-size: 0.82rem;">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Compañía / Tarifa</th>
                                                        <th class="text-center">Coste Estimado</th>
                                                        <th class="text-center">Diferencia</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($tarifasSistema as $tarifa):
                                                        $calcResult = calcularCosteExpediente(
                                                            $facturaItem['fac_consumo_p1_kwh'],
                                                            $facturaItem['fac_consumo_p2_kwh'],
                                                            $facturaItem['fac_consumo_p3_kwh'],
                                                            $facturaItem['fac_potencia_contratada_p1_kw'],
                                                            $facturaItem['fac_potencia_contratada_p2_kw'],
                                                            30,
                                                            $tarifa
                                                        );
                                                        
                                                        $difEuro = $facturaItem['fac_importe_total_factura'] - $calcResult['total_final'];
                                                    ?>
                                                        <tr>
                                                            <td>
                                                                <span class="text-primary"><?= htmlspecialchars($tarifa['comp_nombre']) ?></span> / 
                                                                <span class="text-muted"><?= htmlspecialchars($tarifa['tar_nombre_tarifa']) ?></span>
                                                            </td>
                                                            <td class="text-center fw-bold"><?= number_format($calcResult['total_final'], 2) ?> €</td>
                                                            <td class="text-center <?= $difEuro > 0 ? 'text-success' : 'text-danger' ?> fw-bold">
                                                                <?= $difEuro > 0 ? '+' : '' ?><?= number_format($difEuro, 2) ?> €
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <!-- Acciones sobre la factura -->
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="btn-group">
                                                <?php 
                                                $pathFactura = $facturaItem['fac_ruta_archivo'];
                                                $urlArchivo = (strpos($pathFactura, 'demo/') === 0) ? "../" . $pathFactura : "../archivos/" . $pathFactura;
                                                ?>
                                                <a href="<?= $urlArchivo ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-file-earmark-pdf"></i> Original
                                                </a>
                                                <a href="editar_presupuesto.php?id=<?= $facturaItem['fac_id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil-square"></i> Editar
                                                </a>
                                                <button onclick="solicitarBajaEstudio(<?= $facturaItem['fac_id'] ?>)" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash3"></i> Borrar
                                                </button>
                                            </div>
                                            <span class="small text-muted fst-italic">* Proyección basada en tarifas del catálogo.</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts de interactividad y validación -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        /**
         * Confirma la eliminación de un estudio antes de realizar la petición al servidor.
         */
        function solicitarBajaEstudio(idFactura) {
            const confirmacion = confirm('¿Confirmas que deseas eliminar este estudio técnico y su archivo PDF asociado? Esta acción no se puede deshacer.');
            if (confirmacion) {
                window.location.href = 'eliminar_presupuesto.php?id=' + idFactura;
            }
        }
    </script>
</body>
</html>
