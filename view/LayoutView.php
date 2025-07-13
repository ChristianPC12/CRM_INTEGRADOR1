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
    <?php elseif ($vista === 'compras'): ?>
        <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Compra.css">
    <?php elseif ($vista === 'codigo'): ?>
        <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Codigo.css">
    <?php elseif ($vista === 'analisis'): ?>
        <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Analisis.css">
    <?php endif; ?>
    <!-- CSS general -->
    <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Layout.css?v=<?= time() ?>">
    <!-- CSS de usuarios (cargado al final para mayor prioridad) -->
    <?php if ($vista === 'usuarios'): ?>
        <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Usuario.css?v=<?= time() ?>">
    <?php endif; ?>
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
                <a href="index.php?view=compras" id="link-compras"
                    class="<?= $vista === 'compras' ? 'active' : '' ?>">
                    <i class="bi bi-tag"></i> Beneficios
                </a>
            </li>
            <li>
                <a href="index.php?view=codigo" id="link-codigo"
                    class="<?= $vista === 'codigo' ? 'active' : '' ?>">
                    <i class="bi bi-upc-scan"></i> Código de barras
                </a>
            </li>
            <li>
                <a href="index.php?view=analisis" id="link-analisis"
                    class="<?= $vista === 'analisis' ? 'active' : '' ?>">
                    <i class="bi bi-graph-up-arrow"></i> Análisis
                </a>
            </li>
            <li>
                 <a href="index.php?view=cumple" id="link-cumple"
                     class="<?= $vista === 'cumple' ? 'active' : '' ?>">
                     <i class="bi bi-gift"></i> Cumpleaños
                     <span id="cumple-badge" style="display:none; margin-left:8px; vertical-align:middle;"></span>
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
} elseif ($vista === 'compras') {
    include 'CompraView.php';
} elseif ($vista === 'codigo') {
    include 'CodigoView.php';
} elseif ($vista === 'analisis') {
    include 'AnalisisView.php';
} elseif ($vista === 'cumple') {
    include 'CumpleView.php';
} else {
    include 'DashboardView.php';
}
            ?>
        <!-- Modal para ingresar número de tarjeta -->
        <div id="modalTarjeta" class="modal-tarjeta">
            <div class="modal-contenido">
                <h3>Ingrese número de tarjeta</h3>
                <input type="text" id="modalInputTarjeta" placeholder="Ej: 60" />
                <div id="modalMensajeError" class="modal-mensaje-error"></div>
                <div class="modal-botones">
                    <button onclick="cerrarModal()">Cancelar</button>
                    <button onclick="redirigirCompra()">Buscar</button>
                </div>
            </div>
        </div>

    <!-- Scripts Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script general -->
    <script src="/CRM_INT/CRM/public/js/Layout.js"></script>

    <!-- Scripts específicos -->
    <?php if ($vista === 'dashboard'): ?>
    <?php elseif ($vista === 'clientes'): ?>
        <script src="/CRM_INT/CRM/public/js/Cliente.js"></script>
    <?php endif; ?>
    
    <?php if ($vista === 'cumple'): ?>
        <script src="/CRM_INT/CRM/public/js/Cumple.js"></script>
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

            if (rol === "Salonero") {
                // Desactivar Usuarios en todas las vistas (acceso restringido)
                const linkUsuarios = document.getElementById('link-usuarios');
                if (linkUsuarios) {
                    linkUsuarios.classList.add('disabled-link');
                    linkUsuarios.removeAttribute('href');
                    linkUsuarios.title = "Acceso restringido";
                }

                // Solo aplicar restricciones de solo lectura en la vista de clientes
                const currentView = '<?= $vista ?>';

                if (currentView === 'clientes') {
                    const submitBtn = document.getElementById('submitBtn');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('btn-disabled');
                        submitBtn.title = "No tienes permisos para guardar clientes";
                    }

                    const cancelBtn = document.getElementById('cancelBtn');
                    if (cancelBtn) {
                        cancelBtn.disabled = true;
                        cancelBtn.classList.add('btn-disabled');
                        cancelBtn.title = "No tienes permisos para editar clientes";
                    }

                    const form = document.getElementById('clienteForm');
                    if (form) {
                        const campos = form.querySelectorAll("input, select, textarea");
                        campos.forEach((campo) => {
                            campo.readOnly = true;
                            campo.disabled = true;
                            campo.classList.add('form-readonly');
                        });
                    }

                    const observer = new MutationObserver(() => {
                        document.querySelectorAll("button.btn-warning").forEach((btn) => {
                            btn.disabled = true;
                            btn.classList.add('btn-disabled');
                            btn.title = "No tienes permisos para editar clientes";
                        });
                        document.querySelectorAll("button.btn-danger").forEach((btn) => {
                            btn.disabled = true;
                            btn.classList.add('btn-disabled');
                            btn.title = "No tienes permisos para eliminar clientes";
                        });
                    });

                    const lista = document.getElementById("clienteLista");
                    if (lista) {
                        observer.observe(lista, { childList: true, subtree: true });
                    }
                }
            } else if (rol === "Administrador") {
                const linkUsuarios = document.getElementById('link-usuarios');
                if (linkUsuarios) {
                    linkUsuarios.classList.add('disabled-link');
                    linkUsuarios.removeAttribute('href');
                    linkUsuarios.title = "Acceso restringido";
                }

                const currentView = '<?= $vista ?>';

                if (currentView === 'clientes') {
                    const observer = new MutationObserver(() => {
                        document.querySelectorAll("button.btn-danger").forEach((btn) => {
                            btn.disabled = true;
                            btn.classList.add('btn-disabled');
                            btn.title = "No tienes permisos para eliminar clientes";
                        });
                    });

                    const lista = document.getElementById("clienteLista");
                    if (lista) {
                        observer.observe(lista, { childList: true, subtree: true });
                    }
                }
            }

     
          const rolUsuario = "<?= isset($_SESSION['rol']) ? strtolower($_SESSION['rol']) : '' ?>";
          const vistaActual = "<?= $vista ?>";

            // Mostrar el modal automáticamente solo si el rol es salonero Y está en dashboard
            if (rolUsuario === "salonero" && vistaActual === "dashboard") {
              const modal = document.getElementById('modalTarjeta');
              if (modal) modal.style.display = 'flex';
            }
            // Mostrar el modal al hacer click en el logo para cualquier rol
            const logo = document.querySelector(".img-header");
            if (logo) {
              logo.style.cursor = 'pointer';
              logo.addEventListener('click', function() {
                const modal = document.getElementById('modalTarjeta');
                if (modal) modal.style.display = 'flex';
              });
            }
        });
    </script>

    <script>
// Badge de cumpleaños pendientes
function mostrarCumpleBadge(pendientes) {
    const badge = document.getElementById('cumple-badge');
    if (!badge) return;
    if (pendientes) {
        badge.style.display = 'inline-block';
        badge.innerHTML = `<span style="display:inline-block;width:12px;height:12px;background:#f9c41f;border-radius:50%;border:2px solid #000;box-shadow:0 0 2px #000;vertical-align:middle;"></span>`;
    } else {
        badge.style.display = 'none';
        badge.innerHTML = '';
    }
}
document.addEventListener('DOMContentLoaded', function() {
    fetch('/CRM_INT/CRM/controller/CumpleController.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=hayPendientes'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.hayPendientes) {
            mostrarCumpleBadge(true);
        } else {
            mostrarCumpleBadge(false);
        }
    })
    .catch(() => mostrarCumpleBadge(false));
});
</script>

</body>

</html>
