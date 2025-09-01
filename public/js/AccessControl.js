// ===== SISTEMA DE CONTROL DE ACCESO Y FUNCIONALIDADES PRINCIPALES =====

document.addEventListener('DOMContentLoaded', function () {
    
    // ===== CONFIGURACIÓN INICIAL =====
    const rol = (localStorage.getItem('rolUsuario') || '').toLowerCase();
    const vistaActual = new URLSearchParams(window.location.search).get('view') || 'dashboard';
    
    // ===== CONFIGURACIÓN DE LOGO COMO ATAJO =====
    initLogoShortcut();
    
    // ===== AUTO-ABRIR MODAL DE CUMPLEAÑOS PARA SALONEROS =====
    initCumpleanosAutoOpen();
    
    // ===== APLICAR CONTROL DE ACCESO SEGÚN ROL =====
    applyRoleBasedAccess(rol, vistaActual);
});

// ===== FUNCIONES DE INICIALIZACIÓN =====

function initLogoShortcut() {
    const logo = document.querySelector(".img-header");
    if (logo) {
        logo.style.cursor = 'pointer';
        logo.addEventListener('click', function (e) {
            e.preventDefault();
            if (typeof abrirModal === 'function') {
                abrirModal();
            } else if (typeof mostrarModal === 'function') {
                mostrarModal();
            }
        });
    }
}

function initCumpleanosAutoOpen() {
    const vistaActual = new URLSearchParams(window.location.search).get('view') || 'dashboard';
    if (vistaActual === 'dashboard') {
        let intentos = 0;
        const maxIntentos = 20;
        const intervalo = setInterval(function () {
            const rol = (localStorage.getItem('rolUsuario') || '').toLowerCase();
            if (rol === 'salonero') {
                if (typeof abrirModalCumples === 'function') {
                    abrirModalCumples();
                }
                clearInterval(intervalo);
            }
            intentos++;
            if (intentos >= maxIntentos) {
                clearInterval(intervalo);
            }
        }, 100);
    }
}

// ===== CONTROL DE ACCESO PRINCIPAL =====

function applyRoleBasedAccess(rol, vista) {
    switch (rol) {
        case 'salonero':
            applySaloneroRestrictions(vista);
            break;
        case 'administrador':
            applyAdministradorRestrictions(vista);
            break;
        case 'propietario':
            // Propietario tiene acceso completo
            break;
        default:
            console.warn('Rol no reconocido:', rol);
    }
}

// ===== RESTRICCIONES POR ROL =====

function applySaloneroRestrictions(vista) {
    // Restringir acceso a ciertas secciones del menú
    restrictMenuAccess(['link-usuarios', 'link-analisis', 'link-Bitacora']);
    
    // Aplicar restricciones específicas por vista
    switch (vista) {
        case 'dashboard':
            hideDashboardMetrics();
            break;
        case 'clientes':
            makeClientesReadOnly();
            break;
        case 'compras':
            restrictBeneficiosForSalonero();
            break;
        case 'codigo':
            restrictPrintingButtons();
            break;
    }
}

function applyAdministradorRestrictions(vista) {
    // Restringir acceso a ciertas secciones del menú
    restrictMenuAccess(['link-usuarios', 'link-Bitacora']);
    
    // Aplicar restricciones específicas por vista
    switch (vista) {
        case 'clientes':
            restrictClientesForAdmin();
            break;
        case 'compras':
            restrictDeleteInBeneficios();
            break;
        case 'codigo':
            restrictPrintingButtons();
            break;
    }
}

// ===== FUNCIONES DE RESTRICCIÓN DE MENÚ =====

function restrictMenuAccess(linkIds) {
    linkIds.forEach(linkId => {
        const link = document.getElementById(linkId);
        if (link) {
            link.classList.add('disabled-link');
            link.removeAttribute('href');
            link.title = "Acceso restringido";
        }
    });
}

// ===== FUNCIONES DE RESTRICCIÓN POR VISTA =====

function hideDashboardMetrics() {
    document.querySelectorAll('.tarjeta-metrica').forEach(div => {
        div.style.display = 'none';
    });
}

function makeClientesReadOnly() {
    // Deshabilitar botones principales
    disableButton('submitBtn', "No tienes permisos para guardar clientes");
    disableButton('cancelBtn', "No tienes permisos para editar clientes");
    
    // Hacer formulario de solo lectura
    const form = document.getElementById('clienteForm');
    if (form) {
        const campos = form.querySelectorAll("input, select, textarea");
        campos.forEach((campo) => {
            campo.readOnly = true;
            campo.disabled = true;
            campo.classList.add('form-readonly');
        });
    }
    
    // Observar y deshabilitar botones dinámicos
    observeAndDisableButtons('clienteLista', [
        { selector: 'button.btn-warning', message: "No tienes permisos para editar clientes" },
        { selector: 'button.btn-danger', message: "No tienes permisos para eliminar clientes" },
        { 
            condition: (btn) => btn.textContent.trim() === 'Reasignar' || 
                              (btn.onclick && btn.onclick.toString().includes('abrirModalReasignar')),
            message: "No tienes permisos para reasignar clientes"
        }
    ]);
}

function restrictBeneficiosForSalonero() {
    const restrictions = [
        { 
            condition: (btn) => btn.textContent.trim().toLowerCase() === 'aplicar' || 
                              (btn.onclick && btn.onclick.toString().includes('descuento')),
            message: "No tienes permisos para aplicar descuentos"
        },
        { 
            selector: 'button.btn-danger',
            message: "No tienes permisos para eliminar beneficios"
        }
    ];
    
    applyButtonRestrictions(restrictions);
    
    // NOTA: El salonero SÍ puede acumular compras, por lo que NO deshabilitamos 
    // el input "cantidadAcumulada" ni los botones de "acumular"
    
    // Observar cambios dinámicos (sin restricciones de acumulación)
    observeAndApplyRestrictions('compraLista', restrictions);
}

function restrictClientesForAdmin() {
    observeAndDisableButtons('clienteLista', [
        { selector: 'button.btn-danger', message: "No tienes permisos para eliminar clientes" },
        { 
            condition: (btn) => btn.textContent.trim() === 'Reasignar' || 
                              (btn.onclick && btn.onclick.toString().includes('abrirModalReasignar')),
            message: "No tienes permisos para reasignar clientes"
        }
    ]);
}

function restrictDeleteInBeneficios() {
    function disableDeleteButtons() {
        document.querySelectorAll("button").forEach((btn) => {
            // Detecta botones de eliminar por clase, texto o función onclick
            if (btn.classList.contains('btn-danger') || 
                btn.textContent.trim().toLowerCase().includes('eliminar') ||
                (btn.onclick && btn.onclick.toString().includes('eliminar'))) {
                btn.disabled = true;
                btn.classList.add('btn-disabled');
                btn.title = "No tienes permisos para eliminar beneficios";
            }
        });
    }
    
    // Aplicar inmediatamente
    disableDeleteButtons();
    
    // Observar tanto la lista específica como cambios globales
    const containers = ['compraLista', 'beneficioLista', 'listaBeneficios'];
    containers.forEach(containerId => {
        const container = document.getElementById(containerId);
        if (container) {
            const observer = new MutationObserver(disableDeleteButtons);
            observer.observe(container, { childList: true, subtree: true });
        }
    });
    
    // También observar el body para capturar cualquier botón que se agregue
    const bodyObserver = new MutationObserver(disableDeleteButtons);
    bodyObserver.observe(document.body, { childList: true, subtree: true });
    
    // Re-ejecutar cada vez que se hace clic en algo (por si se regenera contenido)
    document.addEventListener('click', () => {
        setTimeout(disableDeleteButtons, 100);
    });
}

function restrictPrintingButtons() {
    function disableImprimirBtns() {
        document.querySelectorAll('button').forEach((btn) => {
            if (btn.onclick && btn.onclick.toString().includes('imprimirCodigo')) {
                btn.disabled = true;
                btn.classList.add('btn-disabled');
                btn.title = "No tienes permisos para imprimir";
            }
        });
    }
    
    // Exponer función globalmente
    window.disableImprimirBtns = disableImprimirBtns;
    
    // Observar cambios
    const lista = document.getElementById("codigoLista");
    if (lista) {
        const observer = new MutationObserver(disableImprimirBtns);
        observer.observe(lista, { childList: true, subtree: true });
    }
    
    // Aplicar al cargar
    disableImprimirBtns();
}

// ===== FUNCIONES UTILITARIAS =====

function disableButton(buttonId, message) {
    const button = document.getElementById(buttonId);
    if (button) {
        button.disabled = true;
        button.classList.add('btn-disabled');
        button.title = message;
    }
}

function applyButtonRestrictions(restrictions) {
    document.querySelectorAll("button").forEach((btn) => {
        restrictions.forEach(restriction => {
            let shouldDisable = false;
            
            if (restriction.selector && btn.matches(restriction.selector)) {
                shouldDisable = true;
            } else if (restriction.condition && restriction.condition(btn)) {
                shouldDisable = true;
            }
            
            if (shouldDisable) {
                btn.disabled = true;
                btn.classList.add('btn-disabled');
                btn.title = restriction.message;
            }
        });
    });
}

function observeAndDisableButtons(containerId, buttonRestrictions) {
    const applyRestrictions = () => {
        document.querySelectorAll("button").forEach((btn) => {
            buttonRestrictions.forEach(restriction => {
                let shouldDisable = false;
                
                if (restriction.selector && btn.matches(restriction.selector)) {
                    shouldDisable = true;
                } else if (restriction.condition && restriction.condition(btn)) {
                    shouldDisable = true;
                }
                
                if (shouldDisable) {
                    btn.disabled = true;
                    btn.classList.add('btn-disabled');
                    btn.title = restriction.message;
                }
            });
        });
    };
    
    // Aplicar al cargar
    applyRestrictions();
    
    // Observar cambios
    const container = document.getElementById(containerId);
    if (container) {
        const observer = new MutationObserver(applyRestrictions);
        observer.observe(container, { childList: true, subtree: true });
    }
}

function observeAndApplyRestrictions(containerId, restrictions, additionalCallback = null) {
    const applyRestrictions = () => {
        applyButtonRestrictions(restrictions);
        if (additionalCallback) {
            additionalCallback();
        }
    };
    
    // Aplicar al cargar
    applyRestrictions();
    
    // Observar cambios
    const container = document.getElementById(containerId) || document.body;
    const observer = new MutationObserver(applyRestrictions);
    observer.observe(container, { childList: true, subtree: true });
}