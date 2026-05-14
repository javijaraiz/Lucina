<?php
// panel.php — redirige al panel de administración real
require_once 'funciones/sesiones.php';
requerirLogin();
header('Location: admin/index.php');
exit();
