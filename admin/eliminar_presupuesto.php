<?php
require_once '../funciones/conexion.php';
require_once '../funciones/sesiones.php';

// Verificación de seguridad
requerirLogin();

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

try {
    // Obtener info del presupuesto para borrar el archivo
    $stmt = $conn->prepare("SELECT fac_ruta_archivo, fac_cli_id FROM facturas WHERE fac_id = ?");
    $stmt->execute([$id]);
    $fac = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($fac) {
        $cliente_id = $fac['fac_cli_id'];
        $archivo = $fac['fac_ruta_archivo'];

        // No borrar si es de la carpeta demo/ o si está vacío
        if ($archivo && strpos($archivo, 'demo/') !== 0) {
            $ruta_completa = '../archivos/' . $archivo;
            if (file_exists($ruta_completa)) {
                @unlink($ruta_completa);
            }
        }

        // Borrar de la base de datos
        $stmt_del = $conn->prepare("DELETE FROM facturas WHERE fac_id = ?");
        $stmt_del->execute([$id]);

        header('Location: ver_cliente.php?id=' . $cliente_id . '&msg=deleted');
    } else {
        header('Location: index.php');
    }
} catch (PDOException $e) {
    // En caso de error, guardamos el log o redirigimos
    header('Location: index.php?error=db');
}
exit;
