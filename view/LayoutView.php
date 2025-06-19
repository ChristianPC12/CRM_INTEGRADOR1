<?php
$vista = $_GET['view'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>CRM Bastos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store" />

    <!-- Bootstrap y Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- CSS por vista -->
    <?php if ($vista === 'dashboard'): ?>
        <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Dashboard.css">
    <?php elseif ($vista === 'clientes'): ?>
        <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Cliente.css">
    <?php elseif ($vista === 'usuarios'): ?>
        <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Usuario.css">
    <?php endif; ?>

    <!-- CSS general -->
    <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Layout.css?v=<?= time() ?>">
</head>

<body>
    <!-- BOTÓN HAMBURGUESA PARA MÓVIL -->
<button class="btn-menu-toggle" id="menuToggle" aria-label="Abrir menú">
    <i class="bi bi-list"></i>
</button>

    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="/CRM_INT/CRM/public/img/Principal_Amarillo.png" alt="Logo" class="img-header">
            <h3>CRM Bastos</h3>
        </div>
        <ul>
            <li>
                <a href="index.php?view=dashboard" id="link-dashboard"
                    class="<?= $vista === 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-house"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="index.php?view=clientes" id="link-clientes-vip"
                    class="<?= $vista === 'clientes' ? 'active' : '' ?>">
                    <i class="bi bi-award-fill"></i> Clientes VIP
                </a>
            </li>
            <li>
                <a href="index.php?view=usuarios" id="link-usuarios"
                    class="<?= $vista === 'usuarios' ? 'active' : '' ?>">
                    <i class="bi bi-person"></i> Usuarios
                </a>
            </li>
            <li>
                <a href="#" onclick="confirmarCerrarSesion()" style="color: #dc3545;">
                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                </a>
            </li>
        </ul>

    </aside>

    <div class="content">
        <main class="main-content">
            <?php
            if ($vista === 'clientes') {
                include 'ClienteView.php';
            } elseif ($vista === 'usuarios') {
                include 'UsuarioView.php';
            } elseif ($vista === 'recompensas') {
                include 'RecompensasView.php';
            } else {
                include 'DashboardView.php';
            }
            ?>
        </main>
    </div>

    <!-- Scripts Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script general -->
    <script src="/CRM_INT/CRM/public/js/Layout.js"></script>

    <!-- Scripts específicos -->
    <?php if ($vista === 'dashboard'): ?>
        <script src="/CRM_INT/CRM/public/js/Dashboard.js"></script>
    <?php elseif ($vista === 'clientes'): ?>
        <script src="/CRM_INT/CRM/public/js/Cliente.js"></script>
    <?php endif; ?>

    <!-- Logout automático al cerrar la pestaña -->
    <script>
        window.addEventListener('beforeunload', function () {
            navigator.sendBeacon('/CRM_INT/CRM/controller/LogoutOnClose.php');
        });

        function confirmarCerrarSesion() {
            if (confirm("¿Seguro que desea cerrar sesión?")) {
                window.location.href = "index.php?logout=true";
            }
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Obtener el rol del usuario de localStorage
            const rol = localStorage.getItem('rolUsuario');

            if (rol === 'Salonero') {
                // Desactivar Clientes VIP
                const linkClientes = document.getElementById('link-clientes-vip');
                if (linkClientes) {
                    linkClientes.classList.add('disabled-link');
                    linkClientes.removeAttribute('href'); // Evita navegación
                    linkClientes.style.pointerEvents = "none"; // No se puede clickear
                    linkClientes.style.opacity = 0.5;
                    linkClientes.title = "Acceso restringido";
                }

                // Desactivar Usuarios
                const linkUsuarios = document.getElementById('link-usuarios');
                if (linkUsuarios) {
                    linkUsuarios.classList.add('disabled-link');
                    linkUsuarios.removeAttribute('href');
                    linkUsuarios.style.pointerEvents = "none";
                    linkUsuarios.style.opacity = 0.5;
                    linkUsuarios.title = "Acceso restringido";
                }
            }
        });

    </script>
</body>

</html>