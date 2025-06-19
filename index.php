<?php
session_start();

// Verificar si hay una solicitud de logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Verificar si la sesión ha expirado (30 minutos de inactividad)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 1800)) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Si no está autenticado, mostrar solo el login
if (!isset($_SESSION['usuario']) || !isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    include_once 'view/LoginView.php';
    exit;
}

// Actualizar el tiempo de última actividad
$_SESSION['login_time'] = time();

// Si está autenticado, cargar el layout con la vista correspondiente
$view = isset($_GET['view']) ? $_GET['view'] : 'dashboard';

// Validar que la vista solicitada existe (añade aquí tus vistas permitidas)
$allowedViews = ['dashboard', 'usuarios', 'reportes', 'configuracion'];

if (!in_array($view, $allowedViews)) {
    $view = 'dashboard'; // Vista por defecto
}

// Cargar el layout principal que incluye el dashboard
require_once "view/LayoutView.php";
?>