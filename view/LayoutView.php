<?php
// Lee la vista actual desde la URL (?view=...), si no viene usa 'dashboard'
$vista = $_GET['view'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>CRM Bastos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Evita cachear (útil para ver cambios al instante) -->
    <meta http-equiv="Cache-Control" content="no-store" />

    <!-- Bootstrap y Bootstrap Icons (estilos base del sitio) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome (íconos adicionales) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- CSS específico según la vista actual -->
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
    <?php elseif ($vista === 'guia2'): ?>
        <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Manual2.css?v=<?= time() ?>">
    <?php endif; ?>

    <!-- CSS general del layout (siempre) -->
    <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Layout.css?v=<?= time() ?>">
    <!-- CSS de usuarios solo si la vista es 'usuarios' (tiene prioridad al ir al final) -->
    <?php if ($vista === 'usuarios'): ?>
        <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Usuario.css?v=<?= time() ?>">
    <?php endif; ?>
</head>

<body>
    <!-- BOTÓN HAMBURGUESA PARA MÓVIL: abre/cierra el menú lateral -->
    <button class="btn-menu-toggle" id="menuToggle" aria-label="Abrir menú">
        <i class="bi bi-list"></i>
    </button>

    <!-- Sidebar de navegación (menú lateral) -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <!-- Logo del sistema -->
            <img src="/CRM_INT/CRM/public/img/Principal_Amarillo.png" alt="Logo" class="img-header">
            <h3>CRM Bastos</h3>
        </div>

        <!-- Acceso rápido a "Cumpleaños de la semana" -->
        <button type="button" class="btn-cumple-mini" id="btnCumpleMini" title="Cumpleaños de la semana"
            aria-label="Abrir Cumpleaños">
            <i class="bi bi-gift-fill"></i>
        </button>

        <!-- Links de navegación; marcan 'active' según $vista -->
        <ul>
            <li>
                <a href="/CRM_INT/CRM/index.php?view=dashboard" class="<?= $vista === 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-house"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="/CRM_INT/CRM/index.php?view=clientes" class="<?= $vista === 'clientes' ? 'active' : '' ?>">
                    <i class="bi bi-award-fill"></i> Clientes VIP
                </a>
            </li>
            <li>
                <a href="/CRM_INT/CRM/index.php?view=usuarios" class="<?= $vista === 'usuarios' ? 'active' : '' ?>">
                    <i class="bi bi-person"></i> Usuarios
                </a>
            </li>
            <li>
                <a href="/CRM_INT/CRM/index.php?view=compras" class="<?= $vista === 'compras' ? 'active' : '' ?>">
                    <i class="bi bi-tag"></i> Beneficios
                </a>
            </li>
            <li>
                <a href="/CRM_INT/CRM/index.php?view=codigo" class="<?= $vista === 'codigo' ? 'active' : '' ?>">
                    <i class="bi bi-upc-scan"></i> Código de barras
                </a>
            </li>
            <li>
                <a href="/CRM_INT/CRM/index.php?view=analisis"  class="<?= $vista === 'analisis' ? 'active' : '' ?>">
                    <i class="bi bi-graph-up-arrow"></i> Análisis
                </a>
            </li>
            <li>
                <a href="/CRM_INT/CRM/index.php?view=cumple" class="<?= $vista === 'cumple' ? 'active' : '' ?>">
                    <i class="bi bi-gift"></i> Cumpleaños
                    <!-- Badge que indica pendientes (se controla por JS) -->
                    <span id="cumple-badge" style="display:none; margin-left:8px; vertical-align:middle;"></span>
                </a>
            </li>

            <li>
                <a href="/CRM_INT/CRM/index.php?view=Bitacora" id="link-Bitacora" class="<?= $vista === 'Bitacora' ? 'active' : '' ?>">
                    <i class="bi bi-journal-text"></i> Bitácora
                </a>
            </li>
            <li>
                <a href="/CRM_INT/CRM/index.php?view=guia" class="<?= $vista === 'guia' ? 'active' : '' ?>">
                    <i class="bi bi-info-circle"></i> Manual de uso
                </a>
            </li>
            <li>
                <a href="/CRM_INT/CRM/index.php?view=guia2" class="<?= $vista === 'guia2' ? 'active' : '' ?>">
                    <i class="bi bi-person-badge"></i> Manual de usuario
                </a>
            </li>

            <li>
                <!-- Cerrar sesión (controlador en PHP) -->
                <a href="/CRM_INT/CRM/controller/UsuarioController.php?action=logout" style="color: #dc3545;">
                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                </a>
            </li>

        </ul>
    </aside>

    <!-- Fondo oscuro para móvil cuando el sidebar está abierto -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="content">
        <main class="main-content">
            <?php
            // Incluye la vista correspondiente según $vista
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
            } elseif ($vista === 'guia2') {
                include 'Manual2View.php';
            } else {
                // Vista por defecto (dashboard)
                include 'DashboardView.php';
            }
            ?>

            <!-- Modal simple para buscar por número de tarjeta -->
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

            <!-- Modal de "Cumpleaños de la Semana" (se muestra encima del dashboard) -->
            <div id="modalCumples" class="modal-cumples" style="display: none;">
                <div class="modal-contenido-cumples">
                    <div class="modal-header-cumples">
                        <h3><i class="bi bi-gift"></i> Cumpleaños de la Semana</h3>
                        <button class="btn-cerrar-cumples" onclick="cerrarModalCumples()">&times;</button>
                    </div>
                    <div class="modal-body-cumples">
                        <!-- Aquí se pinta el rango de fechas de la semana -->
                        <div id="rangoCumplesSemana" class="alert alert-info text-center fw-bold">
                            <!-- Aquí se mostrará el rango de la semana -->
                        </div>
                        <!-- Aquí se listan los cumpleañeros -->
                        <div id="listaCumpleanos" class="cumples-container">
                            <!-- Aquí se cargarán los cumpleaños -->
                        </div>
                    </div>
                    <div class="modal-footer-cumples">
                        <button class="btn-ir-cumples" onclick="irACumpleanos()">
                            <i class="bi bi-arrow-right-circle"></i> Ir a Cumpleaños
                        </button>
                    </div>
                </div>
            </div>

            <!-- Scripts Bootstrap (JS de componentes) -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

            <!-- Script general del layout (maneja menú, modales, etc.) -->
            <script src="/CRM_INT/CRM/public/js/Layout.js"></script>
            <!-- Scanner global (con cache-busting por versión) -->
            <script src="/CRM_INT/CRM/public/js/ScannerGlobal.js?v=<?= time() ?>"></script>

            <!-- Scripts específicos por vista (se cargan solo donde aplica) -->
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

            <?php if ($vista === 'guia2'): ?>
            <!-- <script src="/CRM_INT/CRM/public/js/Manual2.js"></script> -->
            <?php endif; ?>

            <!-- Logout automático al cerrar la pestaña/navegador -->
            <script>
                window.addEventListener('beforeunload', function () {
                    // Notifica al servidor que se cerró la pestaña (cierra sesión)
                    navigator.sendBeacon('/CRM_INT/CRM/controller/LogoutOnClose.php');
                });

                // Confirmación manual de cierre de sesión (si se usa)
                function confirmarCerrarSesion() {
                    if (confirm("¿Seguro que desea cerrar sesión?")) {
                        window.location.href = "index.php?logout=true";
                    }
                }
            </script>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // LOGO abre modal de búsqueda por tarjeta (atajo)
                    const logo = document.querySelector(".img-header");
                    if (logo) {
                        logo.style.cursor = 'pointer';
                        logo.addEventListener('click', function (e) {
                            e.preventDefault();
                            if (typeof abrirModal === 'function') {
                                abrirModal();      // Abre #modalTarjeta (definido en Layout.js)
                            } else if (typeof mostrarModal === 'function') {
                                mostrarModal();    // Fallback si el nombre cambia
                            }
                        });
                    }

                    // Si estamos en el dashboard: abre automáticamente el modal de "Cumpleaños"
                    // SOLO para el rol 'salonero' (lee el rol desde localStorage)
                    const vistaActual = '<?= $vista ?>';
                    if (vistaActual === 'dashboard') {
                        let intentos = 0;
                        const maxIntentos = 20;
                        const intervalo = setInterval(function () {
                            const rol = (localStorage.getItem('rolUsuario') || '').toLowerCase();
                            if (rol === 'salonero') {
                                abrirModalCumples();   // Muestra modal de cumpleaños al cargar
                                clearInterval(intervalo);
                            }
                            intentos++;
                            if (intentos >= maxIntentos) {
                                clearInterval(intervalo);
                            }
                        }, 100);
                    }

                    // Rol actual guardado en localStorage (minuscula)
                    const rol = (localStorage.getItem('rolUsuario') || '').toLowerCase();

                    // REGLAS DE PERMISOS por rol y vista
                    // Admin en Beneficios: no puede eliminar
                    if (rol === "administrador" && '<?= $vista ?>' === 'compras') {
                        function disableDeleteButtons() {
                            document.querySelectorAll("button").forEach((btn) => {
                                // Detecta botones de eliminar (por texto o clase)
                                if (btn.textContent.trim().toLowerCase() === 'eliminar' || btn.classList.contains('btn-danger')) {
                                    btn.disabled = true;
                                    btn.classList.add('btn-disabled');
                                    btn.title = "No tienes permisos para eliminar beneficios";
                                }
                            });
                        }
                        // Al cargar
                        disableDeleteButtons();
                        // Observa cambios en la lista para deshabilitar nuevos botones
                        const lista = document.getElementById("compraLista") || document.body;
                        const observer = new MutationObserver(disableDeleteButtons);
                        observer.observe(lista, { childList: true, subtree: true });
                    }

                    // Salonero en Beneficios: no puede acumular, ni aplicar descuento, ni eliminar
                    if (rol === "salonero" && '<?= $vista ?>' === 'compras') {
                        
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
                            // Deshabilita el input de "Acumular compra"
                            const inputAcumulada = document.getElementById("cantidadAcumulada");
                            if (inputAcumulada) {
                                inputAcumulada.disabled = true;
                                inputAcumulada.classList.add('form-readonly');
                                inputAcumulada.title = "No tienes permisos para acumular compras";
                            }
                        });
                        const lista = document.getElementById("compraLista") || document.body;
                        observer.observe(lista, { childList: true, subtree: true });
                        // Aplicar también al cargar
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
                        // Deshabilitar input al cargar
                        const inputAcumulada = document.getElementById("cantidadAcumulada");
                        if (inputAcumulada) {
                            inputAcumulada.disabled = true;
                            inputAcumulada.classList.add('form-readonly');
                            inputAcumulada.title = "No tienes permisos para acumular compras";
                        }
                    }

                    if (rol === "salonero") {
                        // Enlace Usuarios: sin acceso
                        const linkUsuarios = document.getElementById('link-usuarios');
                        if (linkUsuarios) {
                            linkUsuarios.classList.add('disabled-link');
                            linkUsuarios.removeAttribute('href');
                            linkUsuarios.title = "Acceso restringido";
                        }
                        // Enlace Análisis: sin acceso
                        const linkAnalisis = document.getElementById('link-analisis');
                        if (linkAnalisis) {
                            linkAnalisis.classList.add('disabled-link');
                            linkAnalisis.removeAttribute('href');
                            linkAnalisis.title = "Acceso restringido";
                        }
                        // Enlace Bitácora: sin acceso
                        const linkBitacora = document.getElementById('link-Bitacora');
                        if (linkBitacora) {
                            linkBitacora.classList.add('disabled-link');
                            linkBitacora.removeAttribute('href');
                            linkBitacora.title = "Acceso restringido";
                        }

                        // Vista actual
                        const currentView = '<?= $vista ?>';

                        // En dashboard: oculta las tarjetas de métricas para salonero
                        if (currentView === 'dashboard') {
                            document.querySelectorAll('.tarjeta-metrica').forEach(div => {
                                div.style.display = 'none';
                            });
                        }

                        // En clientes: todo en solo lectura para salonero
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

                            // Observa y deshabilita botones de editar/eliminar/reasignar que aparezcan
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
                                // Reasignar
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

                        // En código de barras: deshabilita imprimir para salonero
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
                            // Expone la función (por si se llama desde otros lados)
                            window.disableImprimirBtns = disableImprimirBtns;
                            // Observa la lista e inhabilita nuevos botones
                            const observer = new MutationObserver(disableImprimirBtns);
                            const lista = document.getElementById("codigoLista");
                            if (lista) {
                                observer.observe(lista, { childList: true, subtree: true });
                            }
                            // Ejecuta al cargar
                            disableImprimirBtns();
                        }
                    } else if (rol === "administrador") {
                        // Admin: restringe Usuarios y Bitácora (sin acceso directo desde menú)
                        const linkUsuarios = document.getElementById('link-usuarios');
                        if (linkUsuarios) {
                            linkUsuarios.classList.add('disabled-link');
                            linkUsuarios.removeAttribute('href');
                            linkUsuarios.title = "Acceso restringido";
                        }

                        const linkBitacora = document.getElementById('link-Bitacora');
                        if (linkBitacora) {
                            linkBitacora.classList.add('disabled-link');
                            linkBitacora.removeAttribute('href');
                            linkBitacora.title = "Acceso restringido";
                        }

                        const currentView = '<?= $vista ?>';

                        // Admin en clientes: no puede eliminar ni reasignar
                        if (currentView === 'clientes') {
                            const observer = new MutationObserver(() => {
                                document.querySelectorAll("button.btn-danger").forEach((btn) => {
                                    btn.disabled = true;
                                    btn.classList.add('btn-disabled');
                                    btn.title = "No tienes permisos para eliminar clientes";
                                });

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

                        // Admin en código de barras: no puede imprimir
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
                        
                            disableImprimirBtns();
                        }
                    } else if (rol === "propietario") {
                        // Propietario: acceso total (sin restricciones)
                    }

                    // Código antiguo comentado (duplicado/obsoleto). Se deja como referencia.
                    // const rolUsuario = "<?= isset($_SESSION['rol']) ? strtolower($_SESSION['rol']) : '' ?>";
                    // const vistaActual = "<?= $vista ?>";
                    // if (rolUsuario === "salonero" && vistaActual === "dashboard") {
                    //     abrirModal(); // Esta función ya no existe
                    // }
                    // const logo = document.querySelector(".img-header");
                    // if (logo) {
                    //     logo.style.cursor = 'pointer';
                    //     logo.addEventListener('click', function () {
                    //         abrirModal(); // Esta función ya no existe
                    //     });
                    // }
                });
            </script>

            <script>
                // Muestra/oculta el "punto" de pendientes en el menú Cumpleaños
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
                    // Consulta al backend los cumpleaños de la semana y activa el badge si hay "Activos"
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

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // Botón mini de Cumpleaños (en header): abre el modal de cumples
                    const btnCumpleMini = document.getElementById('btnCumpleMini');
                    if (btnCumpleMini && typeof abrirModalCumples === 'function') {
                        btnCumpleMini.addEventListener('click', function (e) {
                            e.preventDefault();
                            abrirModalCumples(); // Abre el modal con id="modalCumples"
                        });
                    }
                });
            </script>


