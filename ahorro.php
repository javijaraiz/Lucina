<?php
require_once 'funciones/conexion.php';
require_once 'funciones/sesiones.php';

$factura = $_SESSION['datos_factura'] ?? [
    'importe_total' => 0.0,
    'consumo_p1'    => 0,
    'consumo_p2'    => 0,
    'consumo_p3'    => 0,
    'potencia_p1'   => 0,
    'potencia_p2'   => 0,
    'dias_factura'  => 30
];

// Calcula el coste teórico de la factura con una tarifa dada
function calcularCosteTeorico($p1, $p2, $p3, $pot1, $pot2, $dias, $tarifa) {
    $energia  = ($p1 * $tarifa['tar_precio_energia_p1']) +
                ($p2 * $tarifa['tar_precio_energia_p2']) +
                ($p3 * $tarifa['tar_precio_energia_p3']);

    $potencia = (($pot1 * $tarifa['tar_precio_potencia_p1']) +
                 ($pot2 * $tarifa['tar_precio_potencia_p2'])) * $dias;

    $iee      = ($energia + $potencia) * 0.051127; // Impuesto Especial Electricidad (IEE)
    $alquiler = 0.026 * $dias;                     // ~0.026 €/día, estimación estándar de alquiler del contador
    $base     = $energia + $potencia + $iee + $alquiler;
    $total    = $base * 1.21; // IVA 21%

    return [
        'energia'   => $energia,
        'potencia'  => $potencia,
        'impuestos' => $iee + $alquiler,
        'iva'       => $total - $base,
        'total'     => $total
    ];
}

try {
    $stmt   = $conn->query("SELECT t.*, c.comp_nombre, c.comp_logo_url
                            FROM tarifas t
                            INNER JOIN companias c ON t.tar_comp_id = c.comp_id
                            WHERE t.tar_activo = 1 AND c.comp_activo = 1");
    $tarifas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tarifas      = [];
    $mensajeError = "No se ha podido conectar con el repositorio de tarifas.";
}

$resultados = [];
foreach ($tarifas as $tarifa) {
    $desglose   = calcularCosteTeorico(
        $factura['consumo_p1'], $factura['consumo_p2'], $factura['consumo_p3'],
        $factura['potencia_p1'], $factura['potencia_p2'],
        $factura['dias_factura'], $tarifa
    );
    $ahorro     = $factura['importe_total'] - $desglose['total'];
    $resultados[] = [
        'info_tarifa' => $tarifa,
        'coste_mes'   => $desglose['total'],
        'desglose'    => $desglose,
        'ahorro_mes'  => $ahorro,
        'ahorro_año'  => $ahorro * 12
    ];
}

usort($resultados, fn($a, $b) => $b['ahorro_año'] <=> $a['ahorro_año']);

$mejor = $resultados[0] ?? null;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados del Estudio - Lucina</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>

<body>

    <!-- Botón de asistencia WhatsApp -->
    <a href="https://wa.me/34659982383" class="floating-whatsapp" target="_blank">
        <div class="whatsapp-content">
            <span>Consultar con un experto</span>
            <i class="bi bi-whatsapp"></i>
        </div>
    </a>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-lucina">
        <div class="container d-flex justify-content-center">
            <a class="navbar-brand" href="index.php">
                <img src="logo.png" alt="Lucina">
            </a>
        </div>
    </nav>

    <div class="container my-5">

        <!-- Stepper finalizado -->
        <div class="modern-stepper">
            <div class="m-step active"><div class="m-step-circle">1</div></div>
            <div class="m-step-line" style="background: var(--color-azul);"></div>
            <div class="m-step active"><div class="m-step-circle">2</div></div>
            <div class="m-step-line" style="background: var(--color-azul);"></div>
            <div class="m-step active"><div class="m-step-circle">3</div></div>
        </div>

        <!-- Panel de Comparativa Premium -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-9">
                <div class="premium-comparison-card shadow-sm border-0">
                    <div class="text-center mb-4">
                        <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill mb-3">
                            <i class="bi bi-shield-check me-1"></i> Análisis de Ahorro Finalizado
                        </span>
                        <h2 class="fw-bold text-white">Tu potencial de ahorro mensual</h2>
                    </div>
                    
                    <div class="row align-items-center g-0">
                        <!-- Estado Actual -->
                        <div class="col-md-5 text-center py-4 border-end border-white-10">
                            <p class="text-uppercase small mb-1 opacity-75 text-white">Pago actual estimado</p>
                            <div class="display-5 fw-bold text-white-50">
                                <?php echo number_format($factura['importe_total'], 2); ?><small class="fs-4">€</small>
                            </div>
                        </div>

                        <!-- Icono de transición -->
                        <div class="col-md-2 text-center d-none d-md-block">
                            <i class="bi bi-arrow-right-circle fs-1 text-success"></i>
                        </div>

                        <!-- Nuestra recomendación -->
                        <div class="col-md-5 py-4 text-center">
                            <p class="text-uppercase small mb-1 text-success fw-bold">Con nuestra mejor tarifa</p>
                            <div class="display-3 fw-bold price-glow text-white">
                                <?php echo number_format($mejor['coste_mes'] ?? 0, 2); ?> <small class="fs-2">€</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">

                <h4 class="mb-4 text-center fw-bold">¿Qué hubieses pagado con nuestras tarifas?</h4>

                <?php if (empty($resultados)): ?>
                    <div class="alert alert-info border-0 shadow-sm text-center py-4">
                        <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                        No hay tarifas disponibles para tu zona o consumo en este momento.
                    </div>
                <?php else: ?>

                    <?php foreach ($resultados as $idx => $item):
                        $ahorroIndividual = $item['ahorro_mes'];
                    ?>
                        <div class="oferta-card <?php echo $idx === 0 ? 'mejor-oferta' : ''; ?> mb-4 shadow-sm">
                            <div class="row align-items-center p-3">

                                <!-- Identidad de la compañía -->
                                <div class="col-md-3 text-center mb-3 mb-md-0">
                                    <?php if (!empty($item['info_tarifa']['comp_logo_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($item['info_tarifa']['comp_logo_url']); ?>"
                                            alt="Logo Compañía" class="img-fluid" style="max-height: 40px;">
                                    <?php else: ?>
                                        <span class="fw-bold"><?php echo htmlspecialchars($item['info_tarifa']['comp_nombre']); ?></span>
                                    <?php endif; ?>
                                    <div class="small text-muted mt-2 text-uppercase fw-bold">
                                        <?php echo htmlspecialchars($item['info_tarifa']['tar_nombre_tarifa']); ?>
                                    </div>
                                </div>

                                <!-- Resumen económico -->
                                <div class="col-md-5">
                                    <div class="d-flex justify-content-around text-center">
                                        <div>
                                            <div class="small text-muted">Ahorro Estimado</div>
                                            <div class="h4 mb-0 fw-bold text-success">
                                                +<?php echo number_format($ahorroIndividual, 2); ?>€
                                            </div>
                                        </div>
                                        <div class="border-start ps-4">
                                            <div class="small text-muted">COSTE FINAL</div>
                                            <div class="h4 mb-0 fw-bold text-primary">
                                                <?php echo number_format($item['coste_mes'], 2); ?>€
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Interacción (CTA) -->
                                <div class="col-md-4 text-center mt-3 mt-md-0">
                                    <button class="btn btn-primary w-100 py-2 fw-bold"
                                        onclick="solicitarInformacion(<?php echo $item['info_tarifa']['tar_id']; ?>, '<?php echo number_format($ahorroIndividual, 2); ?>')">
                                        <i class="bi bi-check-lg me-1"></i> ME INTERESA
                                    </button>
                                </div>

                            </div>

                            <!-- Desglose técnico inferior -->
                            <div class="bg-light p-2 rounded-bottom border-top flex-wrap d-flex justify-content-between px-4 small text-muted">
                                <span>Energía: <strong><?php echo number_format($item['desglose']['energia'], 2); ?>€</strong></span>
                                <span>Potencia: <strong><?php echo number_format($item['desglose']['potencia'], 2); ?>€</strong></span>
                                <span>IVA: <strong><?php echo number_format($item['desglose']['iva'], 2); ?>€</strong></span>
                            </div>
                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>

            </div>
        </div>

        <footer class="text-center mt-5 pt-5 text-muted small border-top">
            <p>© 2026 Lucina - Proyecto de Optimización Energética.</p>
        </footer>

    </div>

    <!-- Gestión de interacciones -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/lucina.js"></script>

    <script>
        /**
         * Envía la solicitud de interés al gestor y muestra confirmación.
         */
        function solicitarInformacion(idTarifa, ahorroCalculado) {
            // Deshabilitar botón para evitar duplicados
            const btn = event.currentTarget;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';

            const formData = new FormData();
            formData.append('tarifaId', idTarifa);
            formData.append('ahorro', ahorroCalculado);

            // Aviso asíncrono al administrador
            fetch('funciones/notificar_interes.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> SOLICITADO';
                    btn.classList.replace('btn-primary', 'btn-success');
                    mostrarNotificacion('¡Solicitud enviada! Un asesor revisará tu caso enseguida.', 'success');
                } else {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    mostrarNotificacion('Error: ' + (data.error || 'No se pudo procesar la solicitud'), 'danger');
                }
            })
            .catch(err => {
                console.error('Error aviso:', err);
                btn.disabled = false;
                btn.innerHTML = originalText;
                mostrarNotificacion('Fallo en la conexión. Revisa tu internet.', 'danger');
            });
        }
    </script>

</body>
</html>
