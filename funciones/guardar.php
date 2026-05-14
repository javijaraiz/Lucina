<?php
/**
 * Lucina - Comparador Energético (v1.0 Entrega DAW)
 * Módulo: Persistencia de Datos
 * Descripción: Recibe los datos finales del presupuesto (cliente + factura) y los
 * almacena en la base de datos de forma transaccional.
 */

header('Content-Type: application/json');
require_once 'conexion.php';
require_once 'sesiones.php';

// Paso 1: Verificación de seguridad básica (Solo peticiones POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'mensaje' => 'Método de envío no válido']);
    exit();
}

// Paso 2: Lectura del flujo de entrada JSON
$entradaRaw = file_get_contents('php://input');
$datosRecibidos = json_decode($entradaRaw, true);

if (!$datosRecibidos) {
    echo json_encode(['success' => false, 'mensaje' => 'No se recibieron datos procesables']);
    exit();
}

$idTarifaSeleccionada = $datosRecibidos['tarifa_id'] ?? null;
$perfilCliente = $datosRecibidos['cliente'] ?? [];
$datosFacturaSesion = $_SESSION['datos_extraidos'] ?? [];

// Validamos que al menos tengamos lo mínimo para empezar
if (!$idTarifaSeleccionada || empty($perfilCliente)) {
    echo json_encode(['success' => false, 'mensaje' => 'Faltan campos obligatorios para el guardado']);
    exit();
}

try {
    // Iniciamos una transacción para asegurar la integridad de los datos
    // Si falla el guardado de la factura, no queremos que se quede el cliente cojo (o viceversa).
    $conn->beginTransaction();

    // ---------------------------------------------------------
    // ACCIÓN 1: Gestión de la ficha del Cliente
    // ---------------------------------------------------------
    // Utilizamos ON DUPLICATE KEY para actualizar los datos si el email ya existe.
    $consultaCliente = $conn->prepare("INSERT INTO clientes (cli_nombre, cli_email, cli_telefono, cli_poblacion, cli_activo) 
                            VALUES (:nombre, :email, :tel, :pob, 1)
                            ON DUPLICATE KEY UPDATE cli_nombre = :nombre, cli_telefono = :tel, cli_poblacion = :pob");
    
    $consultaCliente->execute([
        'nombre' => $perfilCliente['nombre'] ?? 'Sin nombre',
        'email'  => $perfilCliente['email'] ?? '',
        'tel'    => $perfilCliente['telefono'] ?? '',
        'pob'    => $datosFacturaSesion['poblacion'] ?? ''
    ]);
    
    // Obtenemos el ID del cliente (nuevo o existente)
    $idClienteActual = $conn->lastInsertId();
    if (!$idClienteActual) {
        $buscaId = $conn->prepare("SELECT cli_id FROM clientes WHERE cli_email = ?");
        $buscaId->execute([$perfilCliente['email']]);
        $idClienteActual = $buscaId->fetchColumn();
    }

    // ---------------------------------------------------------
    // ACCIÓN 2: Registro de la Factura / Análisis
    // ---------------------------------------------------------
    $consultaFactura = $conn->prepare("INSERT INTO facturas 
        (fac_user_id, fac_cli_id, fac_cups, fac_poblacion, fac_provincia, 
         fac_consumo_p1_kwh, fac_consumo_p2_kwh, fac_consumo_p3_kwh, 
         fac_potencia_contratada_p1_kw, fac_potencia_contratada_p2_kw, fac_importe_total_factura, 
         fac_ruta_archivo, fac_activo)
        VALUES (:user_id, :cli_id, :cups, :pob, :prov, :p1, :p2, :p3, :pot1, :pot2, :imp, :ruta, 1)");

    $consultaFactura->execute([
        'user_id' => 1, // ID del gestor administrativo (por defecto el sistem)
        'cli_id'  => $idClienteActual,
        'cups'    => $datosFacturaSesion['cups'] ?? '',
        'pob'     => $datosFacturaSesion['poblacion'] ?? '',
        'prov'    => $datosFacturaSesion['provincia'] ?? '',
        'p1'      => $datosFacturaSesion['consumo_p1'] ?? 0,
        'p2'      => $datosFacturaSesion['consumo_p2'] ?? 0,
        'p3'      => $datosFacturaSesion['consumo_p3'] ?? 0,
        'pot1'    => $datosFacturaSesion['potencia_p1'] ?? 0,
        'potencia_p2_kw' => $datosFacturaSesion['potencia_p2'] ?? 0, // Corregido el mapeo para mayor claridad
        'imp'     => $datosFacturaSesion['importe_total'] ?? 0,
        'ruta'    => $datosFacturaSesion['ruta_archivo'] ?? ''
    ]);

    // Cerramos la transacción
    $conn->commit();

    // Persistimos el ID del cliente en la sesión para el flujo de interés posterior
    $_SESSION['usuario_id'] = $idClienteActual;

    echo json_encode([
        'success' => true,
        'mensaje' => 'Información registrada con éxito. Un gestor energético revisará tu caso.',
        'cliente_id' => $idClienteActual
    ]);

} catch (Exception $error) {
    // Si algo sale mal, deshacemos todos los cambios en la BD
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error técnico en el proceso: ' . $error->getMessage()
    ]);
}

exit();
?>
