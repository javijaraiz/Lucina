<?php
// Archivo para gestionar sesiones
// TODO: Mejorar la seguridad con tokens CSRF

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Función para verificar si el usuario está logueado
function estaLogueado()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Función para obtener el ID del usuario actual
function obtenerUserId()
{
    return $_SESSION['user_id'] ?? null;
}

// Función para obtener el nombre del usuario actual
function obtenerUserNombre()
{
    return $_SESSION['user_nombre'] ?? 'Usuario';
}

// Función para obtener el email del usuario actual
function obtenerUserEmail()
{
    return $_SESSION['user_email'] ?? '';
}

// Función para verificar si es administrador
function esAdmin()
{
    return isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin';
}

// Función para requerir login (redirige si no está logueado)
function requerirLogin()
{
    if (!estaLogueado()) {
        header('Location: login.php');
        exit();
    }
}

// Función para requerir admin
function requerirAdmin()
{
    requerirLogin();
    if (!esAdmin()) {
        header('Location: panel.php');
        exit();
    }
}

// Función para iniciar sesión de usuario
function iniciarSesion($userId, $nombre, $email, $rol = 'user')
{
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_nombre'] = $nombre;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_rol'] = $rol;
    $_SESSION['login_time'] = time();
}

// Función para cerrar sesión
function cerrarSesion()
{
    session_unset();
    session_destroy();
}

// Acordarse de que esto es importante para la seguridad
?>
