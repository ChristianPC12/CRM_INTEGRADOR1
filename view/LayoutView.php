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
    <?php
    $css_mapping = [
        'dashboard' => 'Dashboard.css',
        'clientes' => 'Cliente.css',
        'compras' => 'Compra.css',
        'codigo' => 'Codigo.css',
        'analisis' => 'Analisis.css',
        'Bitacora' => 'Bitacora.css',
        'cumple' => 'Cumple.css',
        'guia' => 'Manual.css',
        'guia2' => 'Manual2.css',
        'usuarios' => 'Usuario.css'
    ];

    if (isset($css_mapping[$vista])):
        $css_file = $css_mapping[$vista];
        $cache_buster = in_array($vista, ['cumple', 'guia', 'guia2', 'usuarios']) ? '?v=' . time() : '';
        $preload = in_array($vista, ['guia', 'guia2']);
        ?>
        <?php if ($preload): ?>
            <link rel="preload" href="/CRM_INT/CRM/public/css/<?= $css_file . $cache_buster ?>" as="style"
                onload="this.onload=null;this.rel='stylesheet'">
            <noscript>
                <link rel="stylesheet" href="/CRM_INT/CRM/public/css/<?= $css_file . $cache_buster ?>">
            </noscript>
        <?php else: ?>
            <link rel="stylesheet" href="/CRM_INT/CRM/public/css/<?= $css_file . $cache_buster ?>">
        <?php endif; ?>
    <?php endif; ?>

    <!-- CSS general del layout (siempre) -->
    <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Layout.css?v=<?= time() ?>">
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

        <!-- Botón de Acerca de -->
        <button type="button" class="btn-cumple-mini" id="btnAcercaMini" title="Acerca de CRM Bastos"
            aria-label="Abrir Acerca de">
            <i class="bi bi-info-circle-fill"></i>
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
                <a href="/CRM_INT/CRM/index.php?view=usuarios" id="link-usuarios"
                    class="<?= $vista === 'usuarios' ? 'active' : '' ?>">
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
                <a href="/CRM_INT/CRM/index.php?view=analisis" id="link-analisis"
                    class="<?= $vista === 'analisis' ? 'active' : '' ?>">
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
                <a href="/CRM_INT/CRM/index.php?view=Bitacora" id="link-Bitacora"
                    class="<?= $vista === 'Bitacora' ? 'active' : '' ?>">
                    <i class="bi bi-journal-text"></i> Bitácora
                </a>
            </li>
            <li>
                <a href="/CRM_INT/CRM/index.php?view=guia" class="<?= $vista === 'guia' ? 'active' : '' ?>">
                    <i class="bi bi-info-circle"></i> Manual de usuario
                </a>
            </li>
            <li>
                <a href="/CRM_INT/CRM/index.php?view=guia2" class="<?= $vista === 'guia2' ? 'active' : '' ?>">
                    <i class="bi bi-person-badge"></i> Manual Técnico
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
            $view_mapping = [
                'clientes' => 'ClienteView.php',
                'usuarios' => 'UsuarioView.php',
                'recompensas' => 'RecompensasView.php',
                'compras' => 'CompraView.php',
                'codigo' => 'CodigoView.php',
                'analisis' => 'AnalisisView.php',
                'cumple' => 'CumpleView.php',
                'Bitacora' => 'BitacoraView.php',
                'guia' => 'ManualView.php',
                'guia2' => 'Manual2View.php'
            ];

            $view_file = $view_mapping[$vista] ?? 'DashboardView.php';
            include $view_file;
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

            <!-- Modal "Acerca de" -->
            <div id="modalAcerca" class="modal-cumples" style="display:none;">
                <div class="modal-contenido-cumples">
                    <div class="modal-header-cumples">
                        <h3><i class="bi bi-info-circle"></i> Acerca de</h3>
                        <button class="btn-cerrar-cumples" onclick="cerrarModalAcerca()">&times;</button>
                    </div>

                    <div class="modal-body-cumples">
                        <div class="text-center mb-3">
                            <strong>CRM Bastos</strong><br>
                            <small>Proyecto académico — <?= date('Y') ?></small>
                        </div>

                        <div class="equipo-box">
                            <h5 class="equipo-titulo"><i class="bi bi-people-fill"></i> Integrantes</h5>
                            <ul class="equipo-lista">
                                <li>
                                    <span class="equipo-nombre">Christian Paniagua Castro</span>
                                    <span class="equipo-carnet">504590528</span>
                                    <a class="btn-gh" href="https://github.com/ChristianPC12" target="_blank"
                                        rel="noopener noreferrer">
                                        <i class="bi bi-github"></i> GitHub
                                    </a>
                                </li>
                                <li>
                                    <span class="equipo-nombre">Steven Baltodano Ugarte</span>
                                    <span class="equipo-carnet">504640801</span>
                                    <a class="btn-gh" href="https://github.com/stevenBalto" target="_blank"
                                        rel="noopener noreferrer">
                                        <i class="bi bi-github"></i> GitHub
                                    </a>
                                </li>
                                <li>
                                    <span class="equipo-nombre">Reyman Barquero Ramirez</span>
                                    <span class="equipo-carnet">504640986</span>
                                    <a class="btn-gh" href="https://github.com/ReymanBarra" target="_blank"
                                        rel="noopener noreferrer">
                                        <i class="bi bi-github"></i> GitHub
                                    </a>
                                </li>
                                <li>
                                    <span class="equipo-nombre">Bryan Vega Ordoñez</span>
                                    <span class="equipo-carnet">504480650</span>
                                    <a class="btn-gh" href="https://github.com/ChitoBryan" target="_blank"
                                        rel="noopener noreferrer">
                                        <i class="bi bi-github"></i> GitHub
                                    </a>
                                </li>
                                <li>
                                    <span class="equipo-nombre">Felipe Sandoval Chaverri</span>
                                    <span class="equipo-carnet">504260417</span>
                                    <a class="btn-gh" href="https://github.com/Felipe0619" target="_blank"
                                        rel="noopener noreferrer">
                                        <i class="bi bi-github"></i> GitHub
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="modal-footer-cumples">
                        <button class="btn-ir-cumples" onclick="cerrarModalAcerca()">
                            <i class="bi bi-check-circle"></i> Cerrar
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
            <?php if ($vista === 'clientes'): ?>
                <script src="/CRM_INT/CRM/public/js/Cliente.js"></script>
            <?php elseif ($vista === 'cumple'): ?>
                <script src="/CRM_INT/CRM/public/js/Cumple.js"></script>
            <?php elseif ($vista === 'guia'): ?>
                <script src="/CRM_INT/CRM/public/js/Manual.js"></script>
            <?php endif; ?>

            <!-- Logout automático al cerrar la pestaña/navegador -->
            <script>
                window.addEventListener('beforeunload', function () {
                    navigator.sendBeacon('/CRM_INT/CRM/controller/LogoutOnClose.php');
                });
            </script>

            <!-- Script principal de control de acceso y funcionalidades -->
            <script src="/CRM_INT/CRM/public/js/AccessControl.js?v=<?= time() ?>"></script>

            <!-- Script para el badge de cumpleaños -->
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

                    // Botón mini de Cumpleaños (en header): abre el modal de cumples
                    const btnCumpleMini = document.getElementById('btnCumpleMini');
                    if (btnCumpleMini && typeof abrirModalCumples === 'function') {
                        btnCumpleMini.addEventListener('click', function (e) {
                            e.preventDefault();
                            abrirModalCumples();
                        });
                    }
                });
            </script>

            <script>
                // Botón mini de "Acerca de": abre el modal
                const btnAcercaMini = document.getElementById('btnAcercaMini');
                if (btnAcercaMini) {
                    btnAcercaMini.addEventListener('click', function (e) {
                        e.preventDefault();
                        const m = document.getElementById('modalAcerca');
                        if (m) m.style.display = 'flex';
                        document.body.classList.add('modal-open');
                    });
                }

                // Cerrar modal "Acerca de"
                function cerrarModalAcerca() {
                    const m = document.getElementById('modalAcerca');
                    if (m) m.style.display = 'none';
                    document.body.classList.remove('modal-open');
                }

                // Cerrar si se hace click fuera del contenido
                document.addEventListener('click', function (ev) {
                    const m = document.getElementById('modalAcerca');
                    if (!m) return;
                    const contenido = m.querySelector('.modal-contenido-cumples');
                    if (m.style.display !== 'none' && m.style.display !== '' && !contenido.contains(ev.target) && m === ev.target) {
                        cerrarModalAcerca();
                    }
                });
            </script>

        </main>
    </div>
</body>

</html>