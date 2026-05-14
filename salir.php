<?php
// Cerrar sesión
require_once 'funciones/sesiones.php';

cerrarSesion();

// Redirigir al inicio
header('Location: index.php');
exit();
?>
