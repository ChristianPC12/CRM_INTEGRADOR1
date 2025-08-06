document.addEventListener("DOMContentLoaded", function () {
  // Elementos DOM
  const elements = {
    btns: document.querySelectorAll(".compra-btn-opcion"),
    btnBuscar: document.getElementById("compraBtnBuscar"),
    btnBuscarIcon: document.getElementById("compraBtnBuscarIcon"),
    btnCompra: document.getElementById("compraOpcionCompra"),
    btnDescuento: document.getElementById("compraOpcionDescuento"),
    btnExpress: document.getElementById("compraOpcionExpress"),
    inputAcumulada: document.getElementById("cantidadAcumulada"),
    inputId: document.getElementById("compraInputId"),
    compraCardForm: document.getElementById("compraCardForm"),
    compraIndicacion: document.getElementById("compraIndicacion"),
    historialCompras: document.getElementById("historialCompras")
  };

  // Estado global
  const state = {
    idClienteActual: null,
    nombreClienteActual: null,
    datosClienteActual: null
  };

  // Configuración de botones
  const buttonConfig = {
    compra: {
      buscarText: "Buscar",
      acumularText: "Acumular",
      background: "var(--amarillo)",
      acumularBackground: "#39cc6b",
      color: "var(--negro)",
      acumularColor: "white",
      showIcon: false,
      showBuscar: true,
      readonly: false
    },
    descuento: {
      buscarText: "Aplicar",
      background: "#198754",
      color: "white",
      showIcon: false,
      showBuscar: true,
      readonly: true
    },
    express: {
      buscarText: "Buscar",
      background: "var(--amarillo)",
      color: "var(--negro)",
      showIcon: false,
      showBuscar: false, // NUEVO: Ocultar botón buscar para Express
      readonly: true
    }
  };

  // Inicialización
  init();

  function init() {
    handleURLParams();
    setupEventListeners();
    // El botón Express inicia oculto
    if (elements.btnExpress) {
      elements.btnExpress.style.display = "none";
    }
  }

  function handleURLParams() {
    const params = new URLSearchParams(window.location.search);
    const idAuto = params.get('idCliente');
    const buscarAuto = params.get('buscar');
    
    console.log('Parámetros URL detectados:', { idAuto, buscarAuto });
    
    if (idAuto) {
      console.log('Configurando búsqueda automática para tarjeta:', idAuto);
      
      selectButton('compra');
      elements.inputId.value = idAuto;
      
      if (buscarAuto === 'auto') {
        console.log('Ejecutando búsqueda automática...');
        setTimeout(() => {
          elements.btnBuscar.click();
        }, 300);
      }
    }
  }

  function setupEventListeners() {
    // Enter en input
    elements.inputId.addEventListener("keydown", function (e) {
      if (e.key === "Enter") {
        selectButton('compra');
        e.preventDefault();
        elements.btnBuscar.click();
      }
    });

    // Botones de opciones
    elements.btns.forEach((btn) => {
      btn.addEventListener("click", function () {
        const buttonType = getButtonType(this);
        
        // NUEVO: Si es Express y ya está seleccionado, deseleccionar y volver al estado inicial
        if (buttonType === 'express' && this.classList.contains("selected")) {
          deselectAllButtons();
          resetToInitialState(); // Volver al estado inicial
          return;
        }
        
        selectButton(buttonType);
        
        // NUEVO: Si es Express y hay cliente, mostrar modal inmediatamente
        if (buttonType === 'express' && state.idClienteActual && state.datosClienteActual) {
          mostrarModalExpress();
        }
      });
    });

    // Eventos de búsqueda
    elements.btnBuscar.addEventListener("click", manejarAccion);
    elements.btnBuscarIcon.addEventListener("click", buscarCliente);
  }

  function getButtonType(button) {
    if (button === elements.btnCompra) return 'compra';
    if (button === elements.btnDescuento) return 'descuento';
    if (button === elements.btnExpress) return 'express';
    return null;
  }

  function selectButton(type) {
    deselectAllButtons();
    
    const button = type === 'compra' ? elements.btnCompra : 
                   type === 'descuento' ? elements.btnDescuento : 
                   elements.btnExpress;
    
    if (button) {
      button.classList.add("selected");
    }
    
    updateButtonState(type);
  }

  function deselectAllButtons() {
    elements.btns.forEach((b) => b.classList.remove("selected"));
  }

  function resetToInitialState() {
    // Limpiar estado del cliente
    state.idClienteActual = null;
    state.nombreClienteActual = null;
    state.datosClienteActual = null;
    
    // Limpiar formularios
    elements.inputId.value = "";
    elements.inputAcumulada.value = "";
    
    // Ocultar formulario del cliente y mostrar indicación inicial
    elements.compraCardForm.style.display = "none";
    elements.compraIndicacion.style.display = "block";
    
    // Ocultar botón Express nuevamente
    if (elements.btnExpress) {
      elements.btnExpress.style.display = "none";
    }
    
    // Restablecer botón buscar al estado inicial
    elements.btnBuscar.style.display = "inline-block";
    elements.btnBuscar.textContent = "Buscar";
    elements.btnBuscar.style.background = "var(--amarillo)";
    elements.btnBuscar.style.color = "var(--negro)";
    elements.btnBuscarIcon.style.display = "none";
    
    // Restablecer input acumulada
    elements.inputAcumulada.removeAttribute("readonly");
    
    // Limpiar historial
    if (elements.historialCompras) {
      elements.historialCompras.innerHTML = "";
    }
  }

  function updateButtonState(type) {
    const config = buttonConfig[type];
    if (!config) return;

    // Configurar readonly del input
    if (config.readonly) {
      elements.inputAcumulada.setAttribute("readonly", true);
    } else {
      elements.inputAcumulada.removeAttribute("readonly");
    }

    // NUEVO: Mostrar/ocultar botón buscar según configuración
    elements.btnBuscar.style.display = config.showBuscar ? "inline-block" : "none";

    // Solo actualizar botón si es visible
    if (config.showBuscar) {
      // Para compra, verificar si hay cliente para mostrar "Acumular"
      if (type === 'compra' && state.idClienteActual) {
        elements.btnBuscar.textContent = config.acumularText;
        elements.btnBuscar.style.background = config.acumularBackground;
        elements.btnBuscar.style.color = config.acumularColor;
        elements.btnBuscarIcon.style.display = "block";
      } else {
        elements.btnBuscar.textContent = config.buscarText;
        elements.btnBuscar.style.background = config.background;
        elements.btnBuscar.style.color = config.color;
        elements.btnBuscarIcon.style.display = config.showIcon ? "block" : "none";
      }
    }
  }

  function getSelectedOption() {
    if (elements.btnCompra.classList.contains("selected")) return "compra";
    if (elements.btnDescuento.classList.contains("selected")) return "descuento";
    if (elements.btnExpress.classList.contains("selected")) return "express";
    return null;
  }

  async function manejarAccion() {
    const inputId = elements.inputId.value.trim();
    const opcion = getSelectedOption();

    if (!opcion || !inputId) {
      return alert("Seleccione una opción y digite la tarjeta.");
    }

    // Manejo específico por opción
    switch (opcion) {
      case 'descuento':
        await handleDescuento();
        break;
      case 'compra':
        if (elements.btnBuscar.textContent === "Acumular") {
          await handleCompra();
          return;
        }
        break;
      case 'express':
        // Para Express, solo buscar cliente y mostrar modal
        break;
    }

    await buscarCliente();
    
    // Actualizar estado después de la búsqueda
    if (opcion === "compra" && state.idClienteActual) {
      updateButtonState('compra');
    } else if (opcion === "express" && state.idClienteActual && state.datosClienteActual) {
      mostrarModalExpress();
    }
  }

  async function handleDescuento() {
    if (elements.btnBuscar.textContent !== "Aplicar") return;

    let saldo = parseFloat(document.getElementById("totalActual").value) || 0;
    if (saldo < 50000) {
      return alert("El saldo actual es insuficiente para aplicar el descuento.");
    }

    let saldoFinal = saldo - 50000;
    
    try {
      const response = await fetch("/CRM_INT/CRM/controller/ClienteController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: buildUpdateClienteBody(saldoFinal)
      });
      
      const json = await response.json();
      if (!json.success) {
        return alert("Ocurrió un error al actualizar el saldo en la base de datos.");
      }
      
      document.getElementById("totalActual").value = saldoFinal;
      state.datosClienteActual.acumulado = saldoFinal;
      
      alert(
        `Descuento exitoso, puedes aplicar el 15% a nombre del cliente VIP: ${state.nombreClienteActual}.\n` +
        `El saldo actual cambió de ₡${saldo.toLocaleString("es-CR")} a ₡${saldoFinal.toLocaleString("es-CR")}.`
      );
    } catch {
      alert("Error de servidor.");
    }
  }

  async function handleCompra() {
    const monto = parseFloat(elements.inputAcumulada.value.trim()) || 0;
    if (monto <= 0) {
      return alert("Ingrese un monto válido para acumular.");
    }

    try {
      const response = await fetch("/CRM_INT/CRM/controller/CompraController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=create&total=${monto}&idCliente=${state.idClienteActual}`,
      });
      
      const resCompra = await response.json();
      if (resCompra.success) {
        elements.inputAcumulada.value = "";
        await buscarCliente();
        cargarHistorialCompras(state.idClienteActual);
        alert(resCompra.message);
      }
    } catch {
      alert("Error de servidor al registrar la compra.");
    }
  }

  function buildUpdateClienteBody(saldoFinal) {
    if (!state.datosClienteActual) return "";
    
    const data = state.datosClienteActual;
    return `action=update&id=${data.id}&cedula=${data.cedula}&nombre=${data.nombre}` +
           `&correo=${data.correo}&telefono=${data.telefono}&lugarResidencia=${data.lugarResidencia}` +
           `&fechaCumpleanos=${data.fechaCumpleanos}&acumulado=${saldoFinal}`;
  }

  async function buscarCliente() {
    const inputId = elements.inputId.value.trim();
    
    try {
      const response = await fetch("/CRM_INT/CRM/controller/ClienteController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=read&id=${encodeURIComponent(inputId)}`,
      });
      
      const { success, data } = await response.json();
      if (!success || !data) {
        return alert("No se encontró un cliente con ese número de tarjeta.");
      }

      updateClienteState(data);
      populateClienteForm(data);
      showClienteForm();
      
      // Mostrar el botón Express después de la primera búsqueda exitosa
      if (elements.btnExpress) {
        elements.btnExpress.style.display = "inline-block";
      }
      
      cargarHistorialCompras(state.idClienteActual);
      handleBirthdayConfetti(data);
      
    } catch {
      alert("Error de conexión con el servidor.");
    }
  }

  function updateClienteState(data) {
    state.datosClienteActual = { ...data, acumulado: data.acumulado || 0 };
    state.idClienteActual = data.id;
    state.nombreClienteActual = data.nombre;
  }

  function populateClienteForm(data) {
    const fields = [
      { id: "id", value: data.id },
      { id: "clienteCedula", value: data.cedula },
      { id: "clienteNombre", value: data.nombre },
      { id: "clienteCorreo", value: data.correo },
      { id: "clienteTelefono", value: data.telefono },
      { id: "clienteLugar", value: data.lugarResidencia },
      { id: "clienteFecha", value: data.fechaCumpleanos },
      { id: "totalActual", value: state.datosClienteActual.acumulado }
    ];

    fields.forEach(field => {
      const element = document.getElementById(field.id);
      if (element) {
        element.value = field.value || "";
      }
    });

    elements.inputAcumulada.value = "";
  }

  function showClienteForm() {
    elements.compraCardForm.style.display = "block";
    elements.compraIndicacion.style.display = "none";
  }

  function handleBirthdayConfetti(data) {
    if (!data.fechaCumpleanos || typeof confetti !== 'function') return;

    if (esCumpleEstaSemana(data.fechaCumpleanos)) {
      // Múltiples efectos de confeti
      const confettiEffects = [
        { particleCount: 100, spread: 120, origin: { y: 0.6, x: 0.5 } },
        { particleCount: 80, spread: 120, origin: { y: 0.6, x: 0.1 } },
        { particleCount: 80, spread: 120, origin: { y: 0.6, x: 0.9 } }
      ];

      confettiEffects.forEach(effect => confetti(effect));
      
      setTimeout(() => {
        mostrarModalCumpleanos(data.nombre);
      }, 1000);
    }
  }

  function esCumpleEstaSemana(fechaCumpleanos) {
    const hoy = new Date();
    const diaSemana = hoy.getDay() === 0 ? 7 : hoy.getDay();
    
    const lunes = new Date(hoy);
    lunes.setHours(0, 0, 0, 0);
    lunes.setDate(hoy.getDate() - (diaSemana - 1));
    
    const domingo = new Date(lunes);
    domingo.setDate(lunes.getDate() + 6);
    domingo.setHours(23, 59, 59, 999);

    const cumple = new Date(fechaCumpleanos);
    cumple.setFullYear(hoy.getFullYear());

    return cumple >= lunes && cumple <= domingo;
  }

  function mostrarModalExpress() {
    const modal = document.getElementById('modalExpress');
    if (!modal) {
      console.log('Modal Express no encontrado en la vista');
      return;
    }
    
    // Actualizar datos del cliente en el modal
    updateModalData('expressNombre', state.datosClienteActual.nombre);
    updateModalData('expressCorreo', state.datosClienteActual.correo);
    
    // Configurar botón de envío
    const btnEnviar = document.getElementById('btnEnviarCorreoExpress');
    if (btnEnviar) {
      btnEnviar.onclick = enviarCorreoExpress;
    }
    
    // Mostrar modal
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
  }

  function updateModalData(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
      element.textContent = value || '';
    }
  }

  // Funciones globales (mantenidas para compatibilidad)
  window.cargarHistorialCompras = async function (idCliente) {
    elements.historialCompras.innerHTML = '<div style="color:var(--gris)">Cargando historial...</div>';
    
    try {
      const response = await fetch("/CRM_INT/CRM/controller/CompraController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=readByCliente&idCliente=${encodeURIComponent(idCliente)}`,
      });
      
      const json = await response.json();
      
      if (!json.success || !json.data || !json.data.length) {
        elements.historialCompras.innerHTML = `<div class="alert alert-info mt-4">No hay compras registradas para este cliente.</div>`;
        return;
      }

      const filas = json.data
        .slice()
        .reverse()
        .map((compra, i, arr) => buildCompraRow(compra, arr.length - i))
        .join("");

      elements.historialCompras.innerHTML = `
        <div class="tabla-scroll">
          <table class="table table-striped table-hover">
            <thead class="table-dark">
              <tr><th>#</th><th>Fecha</th><th>Total</th><th>Nombre Cliente</th><th>Acciones</th></tr>
            </thead>
            <tbody>${filas}</tbody>
          </table>
        </div>
      `;
    } catch {
      elements.historialCompras.innerHTML = `<div style="color:#d43b3b;">Error al cargar historial</div>`;
    }
  };

  function buildCompraRow(compra, numero) {
    return `
      <tr>
        <td>${numero}</td>
        <td>${compra.fechaCompra}</td>
        <td>₡${parseFloat(compra.total).toLocaleString("es-CR")}</td>
        <td>${state.nombreClienteActual || ""}</td>
        <td>
          <button class="btn btn-sm btn-danger" title="Eliminar" onclick="eliminarCompra(${compra.idCompra})">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    `;
  }

  window.eliminarCompra = async function (idCompra) {
    if (!confirm("¿Está seguro de eliminar esta compra?")) return;
    
    try {
      const response = await fetch("/CRM_INT/CRM/controller/CompraController.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=delete&idCompra=${encodeURIComponent(idCompra)}`,
      });
      
      const json = await response.json();
      alert(json.message);
      
      if (json.success && state.idClienteActual) {
        cargarHistorialCompras(state.idClienteActual);
      }
    } catch {
      alert("Error al eliminar la compra.");
    }
  };

  // Función global para modal de cumpleaños
  window.mostrarModalCumpleanos = function (nombreCliente) {
    const modal = document.getElementById('modalCumpleanos');
    if (!modal) {
      console.log('Modal de cumpleaños no encontrado en esta vista');
      return;
    }
    
    updateModalData('nombreCumpleanero', nombreCliente);
    
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
    
    const btnIrCumpleanos = document.getElementById('btnIrCumpleanos');
    if (btnIrCumpleanos) {
      btnIrCumpleanos.onclick = function() {
        window.location.href = '/CRM_INT/CRM/index.php?view=cumple';
      };
    }
  };

  // Función global para envío de correo Express
  window.enviarCorreoExpress = function () {
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalExpress'));
    if (modal) {
      modal.hide();
    }
    
    console.log('Enviando correo express a:', state.datosClienteActual?.correo);
    // Aquí implementar lógica de envío de correo
  };
});