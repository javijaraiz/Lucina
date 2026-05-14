<?php
/**
 * Lucina - Comparador Energético (DAW DUAL - Proyecto Intermodular - 25/26)
 * Módulo: Controlador de Notificaciones (Refactorización de Seguridad)
 * Descripción: Endpoint para el envío de interés de clientes.
 */

// Cargamos el gestor de sesiones centralizado
require_once 'sesiones.php';
require_once 'conexion.php';
require_once 'notificaciones.php';

// Bloqueo de peticiones no autorizadas
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Protocolo no válido']);
    exit;
}

// Rescatamos identificadores
$idTarifaSeleccionada = (int)($_POST['tarifaId'] ?? 0);
$idClienteSesion      = (int)($_SESSION['usuario_id'] ?? 0);

// Paso de validación detallado para depuración
if (!$idTarifaSeleccionada) {
    echo json_encode(['success' => false, 'error' => 'Falta el identificador de la tarifa elegida']);
    exit;
}

if (!$idClienteSesion) {
    // Si falta el ID de sesión, puede ser por cookies deshabilitadas o flujo incompleto
    echo json_encode(['success' => false, 'error' => 'Sesión de cliente expirada o no encontrada']);
    exit;
}

try {
    // PASO 1: Consulta de integridad
    $stmtCliente = $conn->prepare("SELECT cli_nombre FROM clientes WHERE cli_id = ?");
    $stmtCliente->execute([$idClienteSesion]);
    $perfil = $stmtCliente->fetch(PDO::FETCH_ASSOC);

    $stmtTarifa = $conn->prepare("SELECT t.*, c.comp_nombre 
                                   FROM tarifas t 
                                   JOIN companias c ON t.tar_comp_id = c.comp_id 
                                   WHERE t.tar_id = ?");
    $stmtTarifa->execute([$idTarifaSeleccionada]);
    $tarifa = $stmtTarifa->fetch(PDO::FETCH_ASSOC);

    if ($perfil && $tarifa) {
        $ahorroProyectado = $_POST['ahorro'] ?? '—';

        // Guardamos la tarifa elegida en observaciones del cliente
        $nota = date('d/m/Y H:i') . ": El cliente ha marcado interés en " . $tarifa['comp_nombre'] . " / " . $tarifa['tar_nombre_tarifa'] . " — coste estimado " . $ahorroProyectado . " €/mes";
        $stmtNota = $conn->prepare("UPDATE clientes SET cli_observaciones = ? WHERE cli_id = ?");
        $guardado = $stmtNota->execute([$nota, $idClienteSesion]);

        // Intentamos enviar el email al admin, pero si falla el interés ya quedó registrado
        try {
            $notificador = new LucinaCorreo();
            $notificador->enviarAvisoInteres(
                $perfil['cli_nombre'],
                $tarifa['tar_nombre_tarifa'],
                $tarifa['comp_nombre'],
                $ahorroProyectado,
                $idClienteSesion
            );
        } catch (Exception $e) {
            error_log("Fallo SMTP: " . $e->getMessage());
        }

        echo json_encode([
            'success' => $guardado,
            'error'   => $guardado ? '' : 'No se pudo registrar el interés'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'error'   => 'No se encontró la ficha del cliente en la base de datos (ID: '.$idClienteSesion.')'
        ]);
    }

} catch (PDOException $e) {
    error_log("Error crítico en notificar_interes: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error'   => 'Fallo técnico en la base de datos: ' . $e->getMessage()
    ]);
}
?>
