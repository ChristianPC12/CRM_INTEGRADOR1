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
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

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
    <?php elseif ($vista === 'Bitacora'): ?>
        <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Bitacora.css">
    <?php elseif ($vista === 'cumple'): ?>
        <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Cumple.css?v=<?= time() ?>">
    <?php elseif ($vista === 'guia'): ?>
        <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Manual.css?v=<?= time() ?>">
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
                <a href="/CRM_INT/CRM/index.php?view=dashboard" id="link-dashboard"
                    class="<?= $vista === 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-house"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="/CRM_INT/CRM/index.php?view=clientes" id="link-clientes-vip"
                    class="<?= $vista === 'clientes' ? 'active' : '' ?>">
                    <i class="bi bi-award-fill"></i> Clientes VIP
                </a>
            </li>
            <li>
                <a href="/CRM_INT/CRM/index.php?view=usuarios" id="link-usuarios"
                    class="<?= $vista === 'usuarios' ? 'active' : '' ?>">
                    <i class="bi bi-person"></i> Usuarios
                </a>
            </li>
            <li>
                <a href="/CRM_INT/CRM/index.php?view=compras" id="link-compras"
                    class="<?= $vista === 'compras' ? 'active' : '' ?>">
                    <i class="bi bi-tag"></i> Beneficios
                </a>
            </li>
            <li>
                <a href="/CRM_INT/CRM/index.php?view=codigo" id="link-codigo"
                    class="<?= $vista === 'codigo' ? 'active' : '' ?>">
                    <i class="bi bi-upc-scan"></i> Código de barras
                </a>
            </li>
            <li>
                <a href="/CRM_INT/CRM/index.php?view=analisis" id="link-analisis"
                    class="<?= $vista === 'analisis' ? 'active' : '' ?>">
                    <i class="bi bi-graph-up-arrow"></i> Análisis
                </a>
            </li>
            <li>
                <a href="/CRM_INT/CRM/index.php?view=cumple" id="link-cumple"
                    class="<?= $vista === 'cumple' ? 'active' : '' ?>">
                    <i class="bi bi-gift"></i> Cumpleaños
                    <span id="cumple-badge" style="display:none; margin-left:8px; vertical-align:middle;"></span>
                </a>
            </li>

            <li>
                <a href="/CRM_INT/CRM/index.php?view=Bitacora" id="link-Bitacora"
                    class="<?= $vista === 'Bitacora' ? 'active' : '' ?>">
                    <i class="bi bi-journal-text"></i> Bitácora
                </a>
            </li>
            <li>
                <a href="/CRM_INT/CRM/index.php?view=guia" id="link-guia"
                    class="<?= $vista === 'guia' ? 'active' : '' ?>">
                    <i class="bi bi-book"></i> Manual de guía
                </a>
            </li>



            <li>
                <a href="/CRM_INT/CRM/controller/UsuarioController.php?action=logout" style="color: #dc3545;">
                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                </a>
            </li>

        </ul>
    </aside>

    <!-- OVERLAY PARA MÓVIL -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

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
            } elseif ($vista === 'Bitacora') {
                include 'BitacoraView.php';
            } elseif ($vista === 'guia') {
                include 'ManualView.php';
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
            <script src="/CRM_INT/CRM/public/js/ScannerGlobal.js?v=<?= time() ?>"></script>

            <!-- Scripts específicos -->
            <?php if ($vista === 'dashboard'): ?>
            <?php elseif ($vista === 'clientes'): ?>
                <script src="/CRM_INT/CRM/public/js/Cliente.js"></script>
            <?php endif; ?>

            <?php if ($vista === 'cumple'): ?>
                <script src="/CRM_INT/CRM/public/js/Cumple.js"></script>
            <?php endif; ?>

            <?php if ($vista === 'guia'): ?>
                <script src="/CRM_INT/CRM/public/js/Manual.js"></script>
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
                    // Obtener el rol del usuario de localStorage y normalizar a minúsculas
                    const rol = (localStorage.getItem('rolUsuario') || '').toLowerCase();

                    // Si el rol es propietario, acceso total y no ejecutar restricciones
                    if (rol === "propietario") {
                        // Acceso total, no ejecutar ninguna restricción
                        return;
                    }

                    // Restricción para administrador en Beneficios: no puede eliminar beneficios
                    if (rol === "administrador" && '<?= $vista ?>' === 'compras') {
                        function disableDeleteButtons() {
                            document.querySelectorAll("button").forEach((btn) => {
                                if (btn.textContent.trim().toLowerCase() === 'eliminar' || btn.classList.contains('btn-danger')) {
                                    btn.disabled = true;
                                    btn.classList.add('btn-disabled');
                                    btn.title = "No tienes permisos para eliminar beneficios";
                                }
                            });
                        }
                        // Llamar al cargar la vista
                        disableDeleteButtons();
                        // Observar cambios en la lista de compras para deshabilitar botones nuevos
                        const lista = document.getElementById("compraLista") || document.body;
                        const observer = new MutationObserver(disableDeleteButtons);
                        observer.observe(lista, { childList: true, subtree: true });
                    }
                    // Restricciones para salonero en Beneficios
                    if (rol === "salonero" && '<?= $vista ?>' === 'compras') {
                        // Deshabilitar botones de acumular puntos, aplicar descuentos y eliminar beneficios
                        const observer = new MutationObserver(() => {
                            document.querySelectorAll("button").forEach((btn) => {
                                // Acumular puntos
                                if (btn.textContent.trim().toLowerCase() === 'acumular' || (btn.onclick && btn.onclick.toString().includes('acumular'))) {
                                    btn.disabled = true;
                                    btn.classList.add('btn-disabled');
                                    btn.title = "No tienes permisos para acumular puntos";
                                }
                                // Aplicar descuento
                                if (btn.textContent.trim().toLowerCase() === 'aplicar' || (btn.onclick && btn.onclick.toString().includes('descuento'))) {
                                    btn.disabled = true;
                                    btn.classList.add('btn-disabled');
                                    btn.title = "No tienes permisos para aplicar descuentos";
                                }
                                // Eliminar beneficio
                                if (btn.textContent.trim().toLowerCase() === 'eliminar' || btn.classList.contains('btn-danger')) {
                                    btn.disabled = true;
                                    btn.classList.add('btn-disabled');
                                    btn.title = "No tienes permisos para eliminar beneficios";
                                }
                            });
                            // Deshabilitar input de acumular compra
                            const inputAcumulada = document.getElementById("cantidadAcumulada");
                            if (inputAcumulada) {
                                inputAcumulada.disabled = true;
                                inputAcumulada.classList.add('form-readonly');
                                inputAcumulada.title = "No tienes permisos para acumular compras";
                            }
                        });
                        const lista = document.getElementById("compraLista") || document.body;
                        observer.observe(lista, { childList: true, subtree: true });
                        // Llamar también al cargar la vista
                        document.querySelectorAll("button").forEach((btn) => {
                            if (btn.textContent.trim().toLowerCase() === 'acumular' || (btn.onclick && btn.onclick.toString().includes('acumular'))) {
                                btn.disabled = true;
                                btn.classList.add('btn-disabled');
                                btn.title = "No tienes permisos para acumular puntos";
                            }
                            if (btn.textContent.trim().toLowerCase() === 'aplicar' || (btn.onclick && btn.onclick.toString().includes('descuento'))) {
                                btn.disabled = true;
                                btn.classList.add('btn-disabled');
                                btn.title = "No tienes permisos para aplicar descuentos";
                            }
                            if (btn.textContent.trim().toLowerCase() === 'eliminar' || btn.classList.contains('btn-danger')) {
                                btn.disabled = true;
                                btn.classList.add('btn-disabled');
                                btn.title = "No tienes permisos para eliminar beneficios";
                            }
                        });
                        // Deshabilitar input de acumular compra al cargar
                        const inputAcumulada = document.getElementById("cantidadAcumulada");
                        if (inputAcumulada) {
                            inputAcumulada.disabled = true;
                            inputAcumulada.classList.add('form-readonly');
                            inputAcumulada.title = "No tienes permisos para acumular compras";
                        }
                    }
                    if (rol === "salonero") {
                        // Desactivar Usuarios en todas las vistas (acceso restringido)
                        const linkUsuarios = document.getElementById('link-usuarios');
                        if (linkUsuarios) {
                            linkUsuarios.classList.add('disabled-link');
                            linkUsuarios.removeAttribute('href');
                            linkUsuarios.title = "Acceso restringido";
                        }
                        // Desactivar Análisis en todas las vistas (acceso restringido)
                        const linkAnalisis = document.getElementById('link-analisis');
                        if (linkAnalisis) {
                            linkAnalisis.classList.add('disabled-link');
                            linkAnalisis.removeAttribute('href');
                            linkAnalisis.title = "Acceso restringido";
                        }
                        // Desactivar Bitácora en todas las vistas (acceso restringido)
                        const linkBitacora = document.getElementById('link-Bitacora');
                        if (linkBitacora) {
                            linkBitacora.classList.add('disabled-link');
                            linkBitacora.removeAttribute('href');
                            linkBitacora.title = "Acceso restringido";
                        }

                        // Solo aplicar restricciones de solo lectura en la vista de clientes
                        const currentView = '<?= $vista ?>';

                        // OCULTAR MÉTRICAS EN DASHBOARD PARA SALONERO
                        if (currentView === 'dashboard') {
                            document.querySelectorAll('.tarjeta-metrica').forEach(div => {
                                div.style.display = 'none';
                            });
                        }

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
                                // Deshabilitar botón de reasignar en vez de ocultar
                                document.querySelectorAll('button').forEach((btn) => {
                                    if (btn.textContent.trim() === 'Reasignar' || (btn.onclick && btn.onclick.toString().includes('abrirModalReasignar'))) {
                                        btn.disabled = true;
                                        btn.classList.add('btn-disabled');
                                        btn.title = "No tienes permisos para reasignar clientes";
                                    }
                                });
                            });

                            const lista = document.getElementById("clienteLista");
                            if (lista) {
                                observer.observe(lista, { childList: true, subtree: true });
                            }
                        }
                        if (currentView === 'codigo') {
                            function disableImprimirBtns() {
                                document.querySelectorAll('button').forEach((btn) => {
                                    if (btn.onclick && btn.onclick.toString().includes('imprimirCodigo')) {
                                        btn.disabled = true;
                                        btn.classList.add('btn-disabled');
                                        btn.title = "No tienes permisos para imprimir";
                                    }
                                });
                            }
                            window.disableImprimirBtns = disableImprimirBtns;
                            const observer = new MutationObserver(disableImprimirBtns);
                            const lista = document.getElementById("codigoLista");
                            if (lista) {
                                observer.observe(lista, { childList: true, subtree: true });
                            }
                            // Llamar también al cargar la vista
                            disableImprimirBtns();
                        }
                    } else if (rol === "administrador") {
                        const linkUsuarios = document.getElementById('link-usuarios');
                        if (linkUsuarios) {
                            linkUsuarios.classList.add('disabled-link');
                            linkUsuarios.removeAttribute('href');
                            linkUsuarios.title = "Acceso restringido";
                        }
                        // Desactivar Bitácora en todas las vistas (acceso restringido)
                        const linkBitacora = document.getElementById('link-Bitacora');
                        if (linkBitacora) {
                            linkBitacora.classList.add('disabled-link');
                            linkBitacora.removeAttribute('href');
                            linkBitacora.title = "Acceso restringido";
                        }

                        const currentView = '<?= $vista ?>';

                        if (currentView === 'clientes') {
                            const observer = new MutationObserver(() => {
                                document.querySelectorAll("button.btn-danger").forEach((btn) => {
                                    btn.disabled = true;
                                    btn.classList.add('btn-disabled');
                                    btn.title = "No tienes permisos para eliminar clientes";
                                });
                                // Deshabilitar botón de reasignar igual que eliminar
                                document.querySelectorAll('button').forEach((btn) => {
                                    if (btn.textContent.trim() === 'Reasignar' || (btn.onclick && btn.onclick.toString().includes('abrirModalReasignar'))) {
                                        btn.disabled = true;
                                        btn.classList.add('btn-disabled');
                                        btn.title = "No tienes permisos para reasignar clientes";
                                    }
                                });
                            });

                            const lista = document.getElementById("clienteLista");
                            if (lista) {
                                observer.observe(lista, { childList: true, subtree: true });
                            }
                        }

                        // === BLOQUE PARA CÓDIGO DE BARRAS ===
                        if (currentView === 'codigo') {
                            function disableImprimirBtns() {
                                document.querySelectorAll('button').forEach((btn) => {
                                    if (btn.onclick && btn.onclick.toString().includes('imprimirCodigo')) {
                                        btn.disabled = true;
                                        btn.classList.add('btn-disabled');
                                        btn.title = "No tienes permisos para imprimir";
                                    }
                                });
                            }
                            window.disableImprimirBtns = disableImprimirBtns;
                            const lista = document.getElementById("codigoLista");
                            if (lista) {
                                const observer = new MutationObserver(disableImprimirBtns);
                                observer.observe(lista, { childList: true, subtree: true });
                            }
                            // Llamar también al cargar la vista
                            disableImprimirBtns();
                        }
                    } else if (rol === "propietario") {
                        // El propietario tiene acceso total, no se aplican restricciones.
                    }


                    const rolUsuario = "<?= isset($_SESSION['rol']) ? strtolower($_SESSION['rol']) : '' ?>";
                    const vistaActual = "<?= $vista ?>";

                    // Mostrar el modal automáticamente solo si el rol es salonero Y está en dashboard
                    if (rolUsuario === "salonero" && vistaActual === "dashboard") {
                        abrirModal();
                    }
                    // Mostrar el modal al hacer click en el logo para cualquier rol
                    const logo = document.querySelector(".img-header");
                    if (logo) {
                        logo.style.cursor = 'pointer';
                        logo.addEventListener('click', function () {
                            abrirModal();
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
                document.addEventListener('DOMContentLoaded', function () {
                    fetch('/CRM_INT/CRM/controller/CumpleController.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'action=readSemana'
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success && Array.isArray(data.data) && data.data.some(c => c.estado === 'Activo')) {
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