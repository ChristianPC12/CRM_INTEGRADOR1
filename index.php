<?php
session_start();

// Cierre de sesión (logout)
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

// Expira por inactividad (30 min)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 1800) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

// Verificar autenticación para TODAS las vistas
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    include_once 'view/LoginView.php';
    exit; // Detener ejecución si no está autenticado
}

// Refrescar tiempo activo
$_SESSION['login_time'] = time();

// Cargar la vista solicitada (o dashboard por defecto)
$view = $_GET['view'] ?? 'dashboard';
require_once "view/LayoutView.php";