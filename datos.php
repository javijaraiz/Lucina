<?php
require_once 'funciones/sesiones.php';
require_once 'funciones/conexion.php';

$datosSesion = $_SESSION['datos_extraidos'] ?? [];

// Estructura de trabajo para el formulario
$factura = [
    'cups'          => (string)($datosSesion['cups'] ?? ''),
    'direccion'     => (string)($datosSesion['direccion'] ?? ''),
    'poblacion'     => (string)($datosSesion['poblacion'] ?? ''),
    'provincia'     => (string)($datosSesion['provincia'] ?? ''),
    'consumo_p1'    => floatval($datosSesion['consumo_p1'] ?? 0),
    'consumo_p2'    => floatval($datosSesion['consumo_p2'] ?? 0),
    'consumo_p3'    => floatval($datosSesion['consumo_p3'] ?? 0),
    'potencia_p1'   => floatval($datosSesion['potencia_p1'] ?? 0),
    'potencia_p2'   => floatval($datosSesion['potencia_p2'] ?? 0),
    'importe_total' => floatval($datosSesion['importe_total'] ?? 0),
    'impuesto_electrico' => floatval($datosSesion['impuesto_electrico'] ?? 0),
    'alquiler_contador'  => floatval($datosSesion['alquiler_contador'] ?? 0),
    'iva'           => floatval($datosSesion['iva'] ?? 21),
    'bono_social'   => (string)($datosSesion['bono_social'] ?? 'No'),
    'observaciones' => (string)($datosSesion['observaciones'] ?? ''),
    'dias_factura'  => intval($datosSesion['dias_factura'] ?? 30)
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $factura = [
        'cups'          => $_POST['cups'] ?? '',
        'direccion'     => $_POST['direccion'] ?? '',
        'poblacion'     => $_POST['poblacion'] ?? '',
        'provincia'     => $_POST['provincia'] ?? '',
        'consumo_p1'    => floatval($_POST['consumo_p1'] ?? 0),
        'consumo_p2'    => floatval($_POST['consumo_p2'] ?? 0),
        'consumo_p3'    => floatval($_POST['consumo_p3'] ?? 0),
        'potencia_p1'   => floatval($_POST['potencia_p1'] ?? 0),
        'potencia_p2'   => floatval($_POST['potencia_p2'] ?? 0),
        'importe_total' => floatval($_POST['importe_total'] ?? 0),
        'impuesto_electrico' => floatval($_POST['impuesto_electrico'] ?? 0),
        'alquiler_contador'  => floatval($_POST['alquiler_contador'] ?? 0),
        'iva'           => floatval($_POST['iva'] ?? 21),
        'bono_social'   => $_POST['bono_social'] ?? 'No',
        'observaciones' => $_POST['observaciones'] ?? '',
        'dias_factura'  => intval($_POST['dias_factura'] ?? 30)
    ];

    $_SESSION['datos_factura'] = $factura;

    $clienteTemporal = $_SESSION['cliente_temp'] ?? [];
    if (!empty($clienteTemporal['email'])) {
        try {
            $conn->beginTransaction();

            // Upsert: si el email ya existe actualiza los datos, si no crea el cliente
            $stmtCliente = $conn->prepare("INSERT INTO clientes (cli_nombre, cli_email, cli_telefono, cli_poblacion, cli_activo)
                                VALUES (:nombre, :email, :tel, :pob, 1)
                                ON DUPLICATE KEY UPDATE cli_nombre = :nombre, cli_telefono = :tel, cli_poblacion = :pob");
            $stmtCliente->execute([
                'nombre' => $clienteTemporal['nombre'] ?? 'Invitado',
                'email'  => $clienteTemporal['email'],
                'tel'    => $clienteTemporal['telefono'] ?? '',
                'pob'    => $factura['poblacion'] ?? ''
            ]);

            // lastInsertId() devuelve 0 en un UPDATE, así que recuperamos el id por email
            $idCliente = $conn->lastInsertId();
            if (!$idCliente) {
                $q = $conn->prepare("SELECT cli_id FROM clientes WHERE cli_email = ?");
                $q->execute([$clienteTemporal['email']]);
                $idCliente = $q->fetchColumn();
            }

            $stmtFactura = $conn->prepare("INSERT INTO facturas
                (fac_user_id, fac_cli_id, fac_cups, fac_poblacion, fac_provincia,
                 fac_consumo_p1_kwh, fac_consumo_p2_kwh, fac_consumo_p3_kwh,
                 fac_potencia_contratada_p1_kw, fac_potencia_contratada_p2_kw, fac_importe_total_factura,
                 fac_ruta_archivo, fac_activo, fac_fecha)
                VALUES (:user_id, :cli_id, :cups, :pob, :prov, :p1, :p2, :p3, :pot1, :pot2, :imp, :ruta, 1, NOW())");
            $stmtFactura->execute([
                'user_id' => 1,
                'cli_id'  => $idCliente,
                'cups'    => $factura['cups'] ?? '',
                'pob'     => $factura['poblacion'] ?? '',
                'prov'    => $factura['provincia'] ?? '',
                'p1'      => $factura['consumo_p1'] ?? 0,
                'p2'      => $factura['consumo_p2'] ?? 0,
                'p3'      => $factura['consumo_p3'] ?? 0,
                'pot1'    => $factura['potencia_p1'] ?? 0,
                'pot2'    => $factura['potencia_p2'] ?? 0,
                'imp'     => $factura['importe_total'] ?? 0,
                'ruta'    => $_POST['ruta_archivo'] ?? ($datosSesion['ruta_archivo'] ?? '')
            ]);

            $conn->commit();

            if ($idCliente) $_SESSION['usuario_id'] = $idCliente;

        } catch (Exception $e) {
            if ($conn->inTransaction()) $conn->rollBack();
            error_log("Error al registrar cliente: " . $e->getMessage());
        }
    }

    header('Location: ahorro.php');
    exit();
}

$consumoTotalReferencia = $factura['consumo_p1'] + $factura['consumo_p2'] + $factura['consumo_p3'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación de Datos - Lucina</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>

<body>

    <!-- Ayuda directa vía WhatsApp -->
    <a href="https://wa.me/34659982383" class="floating-whatsapp" target="_blank">
        <div class="whatsapp-content">
            <span>¿Necesitas ayuda con los datos?</span>
            <i class="bi bi-whatsapp"></i>
        </div>
    </a>

    <!-- Cabecera de gestores -->
    <div class="top-header">
        <div class="container d-flex justify-content-end">
            <a href="login.php"><i class="bi bi-person-lock"></i> Acceso Gestores</a>
        </div>
    </div>

    <!-- Navegación -->
    <nav class="navbar navbar-expand-lg navbar-lucina">
        <div class="container d-flex justify-content-center align-items-center">
            <a class="navbar-brand" href="index.php">
                <img src="logo.png" alt="Lucina">
            </a>
        </div>
    </nav>

    <div class="container my-5">

        <!-- Estado del proceso (Stepper) -->
        <div class="modern-stepper">
            <div class="m-step active">
                <div class="m-step-circle">1</div>
                <div class="m-step-label">Análisis</div>
            </div>
            <div class="m-step-line" style="background: var(--color-azul);"></div>
            <div class="m-step active">
                <div class="m-step-circle">2</div>
                <div class="m-step-label">Validación</div>
            </div>
            <div class="m-step-line"></div>
            <div class="m-step">
                <div class="m-step-circle">3</div>
                <div class="m-step-label">Ahorro</div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">

                <!-- Recuadro informativo de revisión -->
                <div class="alert alert-info border-0 shadow-sm p-4 mb-5 text-center" style="background: rgba(3, 152, 210, 0.05); border-radius: 20px;">
                    <h2 class="fw-bold text-primary text-uppercase mb-2" style="font-size: 1.5rem;">Revisión de Datos Técnicos</h2>
                <!-- Formulario de validación y corrección -->
                <form method="POST" action="" class="refinar-form">

                    <!-- SECCIÓN: SUMINISTRO -->
                    <h5 class="mb-4 text-primary d-flex align-items-center">
                        <i class="bi bi-geo-alt-fill me-2"></i> Punto de Suministro
                    </h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label for="cups" class="form-label fw-bold small">Código CUPS</label>
                            <input type="text" class="form-control" id="cups" name="cups"
                                value="<?php echo htmlspecialchars($factura['cups']); ?>"
                                placeholder="ES0000...">
                        </div>
                        <div class="col-md-12">
                            <label for="direccion" class="form-label fw-bold small">Dirección completa</label>
                            <input type="text" class="form-control" id="direccion" name="direccion"
                                value="<?php echo htmlspecialchars($factura['direccion']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="poblacion" class="form-label fw-bold small">Población</label>
                            <input type="text" class="form-control" id="poblacion" name="poblacion"
                                value="<?php echo htmlspecialchars($factura['poblacion']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="provincia" class="form-label fw-bold small">Provincia</label>
                            <input type="text" class="form-control" id="provincia" name="provincia"
                                value="<?php echo htmlspecialchars($factura['provincia']); ?>">
                        </div>
                    </div>

                    <hr class="my-5 opacity-25">

                    <!-- SECCIÓN: CONSUMOS -->
                    <h5 class="mb-4 text-primary d-flex align-items-center">
                        <i class="bi bi-lightning-charge-fill me-2"></i> Detalle de Consumo (kWh)
                    </h5>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="consumo_p1" class="form-label small">Periodo P1 (Punta)</label>
                            <input type="number" step="0.01" class="form-control" id="consumo_p1" name="consumo_p1"
                                value="<?php echo $factura['consumo_p1']; ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="consumo_p2" class="form-label small">Periodo P2 (Llano)</label>
                            <input type="number" step="0.01" class="form-control" id="consumo_p2" name="consumo_p2"
                                value="<?php echo $factura['consumo_p2']; ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="consumo_p3" class="form-label small">Periodo P3 (Valle)</label>
                            <input type="number" step="0.01" class="form-control" id="consumo_p3" name="consumo_p3"
                                value="<?php echo $factura['consumo_p3']; ?>" required>
                        </div>
                    </div>

                    <!-- Indicador visual del total acumulado -->
                    <div class="total-consumo shadow-sm mb-5 p-4 rounded">
                        <div class="small fw-bold text-uppercase opacity-75">Consumo Total Calculado</div>
                        <div class="display-6 fw-bold" id="consumo-total-texto">
                            <?php echo number_format($consumoTotalReferencia, 2); ?> <small class="fs-4">kWh</small>
                        </div>
                    </div>

                    <!-- SECCIÓN: POTENCIAS -->
                    <h5 class="mb-4 text-primary d-flex align-items-center">
                        <i class="bi bi-plug-fill me-2"></i> Potencias Contratadas (kW)
                    </h5>
                    <div class="row g-3 mb-5">
                        <div class="col-md-6">
                            <label for="potencia_p1" class="form-label small">Término de Potencia P1</label>
                            <input type="number" step="0.01" class="form-control" id="potencia_p1" name="potencia_p1"
                                value="<?php echo $factura['potencia_p1']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="potencia_p2" class="form-label small">Término de Potencia P2</label>
                            <input type="number" step="0.01" class="form-control" id="potencia_p2" name="potencia_p2"
                                value="<?php echo $factura['potencia_p2']; ?>" required>
                        </div>
                    </div>

                    <hr class="my-5 opacity-25">

                    <!-- SECCIÓN: IMPORTES -->
                    <h5 class="mb-4 text-primary d-flex align-items-center">
                        <i class="bi bi-cash-stack me-2"></i> costes e Impuestos
                    </h5>
                    <div class="row g-3 mb-5">
                        <div class="col-md-6">
                            <label for="importe_total" class="form-label fw-bold text-danger">IMPORTE TOTAL FACTURA (€) *</label>
                            <input type="number" step="0.01" class="form-control form-control-lg border-danger shadow-sm"
                                id="importe_total" name="importe_total"
                                value="<?php echo $factura['importe_total']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="iva" class="form-label fw-bold small">Tipo de IVA aplicado</label>
                            <select class="form-select" id="iva" name="iva">
                                <option value="21" <?php echo ($factura['iva'] == 21) ? 'selected' : ''; ?>>21% (Estándar)</option>
                                <option value="10" <?php echo ($factura['iva'] == 10) ? 'selected' : ''; ?>>10% (Reducido)</option>
                                <option value="5" <?php echo ($factura['iva'] == 5) ? 'selected' : ''; ?>>5% (Super-reducido)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="bono_social" class="form-label fw-bold small">¿Cuentas con Bono Social?</label>
                            <select class="form-select" id="bono_social" name="bono_social">
                                <option value="No" <?php echo ($factura['bono_social'] == 'No') ? 'selected' : ''; ?>>No</option>
                                <option value="Si" <?php echo ($factura['bono_social'] == 'Si') ? 'selected' : ''; ?>>Sí, tengo descuento activo</option>
                            </select>
                        </div>
                    </div>

                    <!-- OBSERVACIONES ADICIONALES -->
                    <div class="mb-5">
                        <label for="observaciones" class="form-label fw-bold small">Notas adicionales para el asesor</label>
                        <textarea class="form-control" id="observaciones" name="observaciones"
                            rows="4" placeholder="Indica aquí si tienes paneles solares, coche eléctrico o cualquier detalle relevante..."><?php echo htmlspecialchars($factura['observaciones']); ?></textarea>
                    </div>

                    <div class="text-center mt-5">
                        <button type="submit" class="btn btn-lucina-cta btn-lg w-100 py-3 shadow-lg">
                            <i class="bi bi-search me-2"></i> Buscar la mejor tarifa basada en estos datos
                        </button>
                        
                        <!-- Campos ocultos necesarios para el flujo -->
                        <input type="hidden" name="dias_factura" value="<?php echo $factura['dias_factura']; ?>">
                        <input type="hidden" name="ruta_archivo" value="<?php echo htmlspecialchars($_POST['ruta_archivo'] ?? ($datosSesion['ruta_archivo'] ?? '')); ?>">
                        <input type="hidden" name="impuesto_electrico" value="<?php echo $factura['impuesto_electrico']; ?>">
                        <input type="hidden" name="alquiler_contador" value="<?php echo $factura['alquiler_contador']; ?>">
                    </div>

                </form>

                <!-- Pie de página -->
                <footer class="text-center mt-5 pt-5 text-muted small border-top">
                    <p>© 2026 Lucina - Tu ahorro es nuestra prioridad.</p>
                </footer>

            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/lucina.js"></script>

    <script>
        /**
         * Lógica de UI para actualizar el total de consumo en tiempo real
         * mientras el usuario edita los campos.
         */
        document.addEventListener('DOMContentLoaded', function () {
            const camposConsumo = ['consumo_p1', 'consumo_p2', 'consumo_p3'];

            camposConsumo.forEach(id => {
                document.getElementById(id).addEventListener('input', function () {
                    const p1 = parseFloat(document.getElementById('consumo_p1').value) || 0;
                    const p2 = parseFloat(document.getElementById('consumo_p2').value) || 0;
                    const p3 = parseFloat(document.getElementById('consumo_p3').value) || 0;

                    const suma = p1 + p2 + p3;
                    document.getElementById('consumo-total-texto').innerHTML = suma.toFixed(2) + ' <small>kWh</small>';
                });
            });
        });
    </script>

</body>
</html>
