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
    historialCompras: document.getElementById("historialCompras"),
  };

  // Estado global
  const state = {
    idClienteActual: null,
    nombreClienteActual: null,
    datosClienteActual: null,
    expressTimer: null, // Para manejar el temporizador
    expressValidado: false, // Para saber si Express ya fue validado
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
      readonly: false,
    },
    descuento: {
      buscarText: "Aplicar",
      background: "#198754",
      color: "white",
      showIcon: false,
      showBuscar: true,
      readonly: true,
    },
    express: {
      buscarText: "Buscar",
      background: "var(--amarillo)",
      color: "var(--negro)",
      showIcon: false,
      showBuscar: false,
      readonly: true,
    },
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
    const idAuto = params.get("idCliente");
    const buscarAuto = params.get("buscar");

    console.log("Parámetros URL detectados:", { idAuto, buscarAuto });

    if (idAuto) {
      console.log("Configurando búsqueda automática para tarjeta:", idAuto);

      selectButton("compra");
      elements.inputId.value = idAuto;

      if (buscarAuto === "auto") {
        console.log("Ejecutando búsqueda automática...");
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
        selectButton("compra");
        e.preventDefault();
        elements.btnBuscar.click();
      }
    });

    // Botones de opciones
    elements.btns.forEach((btn) => {
      btn.addEventListener("click", function () {
        const buttonType = getButtonType(this);

        // Si es Express y ya está seleccionado, deseleccionar y volver al estado inicial
        if (buttonType === "express" && this.classList.contains("selected")) {
          deselectAllButtons();
          resetToInitialState();
          return;
        }

        selectButton(buttonType);

        // Si es Express y hay cliente, mostrar modal inmediatamente
        if (
          buttonType === "express" &&
          state.idClienteActual &&
          state.datosClienteActual
        ) {
          mostrarModalExpress();
        }
      });
    });

    // Eventos de búsqueda
    elements.btnBuscar.addEventListener("click", manejarAccion);
    elements.btnBuscarIcon.addEventListener("click", buscarCliente);
  }

  function getButtonType(button) {
    if (button === elements.btnCompra) return "compra";
    if (button === elements.btnDescuento) return "descuento";
    if (button === elements.btnExpress) return "express";
    return null;
  }

  function selectButton(type) {
    // Si Express ya está validado, no deseleccionarlo nunca
    if (state.expressValidado) {
      // Solo deseleccionar los otros botones (no Express)
      elements.btns.forEach((b) => {
        if (getButtonType(b) !== "express") {
          b.classList.remove("selected");
        }
      });
      
      // Asegurar que Express se mantenga seleccionado
      elements.btnExpress.classList.add("selected");
    } else {
      deselectAllButtons();
    }

    const button =
      type === "compra"
        ? elements.btnCompra
        : type === "descuento"
        ? elements.btnDescuento
        : elements.btnExpress;

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
    state.expressValidado = false; // Resetear estado Express

    // Limpiar temporizador si existe
    if (state.expressTimer) {
      clearInterval(state.expressTimer);
      state.expressTimer = null;
    }

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

    // Mostrar/ocultar botón buscar según configuración
    elements.btnBuscar.style.display = config.showBuscar
      ? "inline-block"
      : "none";

    // Solo actualizar botón si es visible
    if (config.showBuscar) {
      // Para compra, verificar si hay cliente para mostrar "Acumular"
      if (type === "compra" && state.idClienteActual) {
        elements.btnBuscar.textContent = config.acumularText;
        elements.btnBuscar.style.background = config.acumularBackground;
        elements.btnBuscar.style.color = config.acumularColor;
        elements.btnBuscarIcon.style.display = "block";
      } else if (type === "descuento" && state.expressValidado) {
        // Cambiar texto del botón cuando Express está validado
        elements.btnBuscar.textContent = "Aplicar";
        elements.btnBuscar.style.background = "#28a745"; // Verde más brillante
        elements.btnBuscar.style.color = "white";
        elements.btnBuscarIcon.style.display = config.showIcon ? "block" : "none";
      } else {
        elements.btnBuscar.textContent = config.buscarText;
        elements.btnBuscar.style.background = config.background;
        elements.btnBuscar.style.color = config.color;
        elements.btnBuscarIcon.style.display = config.showIcon
          ? "block"
          : "none";
      }
    }
  }

  function getSelectedOption() {
    const opciones = [];
    if (elements.btnCompra.classList.contains("selected")) opciones.push("compra");
    if (elements.btnDescuento.classList.contains("selected")) opciones.push("descuento");
    if (elements.btnExpress.classList.contains("selected")) opciones.push("express");
    
    // Para compatibilidad con código existente, devolver la primera opción no-express
    // o express si es la única seleccionada
    const nonExpress = opciones.find(op => op !== "express");
    return nonExpress || (opciones.includes("express") ? "express" : null);
  }

  async function manejarAccion() {
    const inputId = elements.inputId.value.trim();
    const opcion = getSelectedOption();

    if (!opcion || !inputId) {
      return alert("Seleccione una opción y digite la tarjeta.");
    }

    // Manejo específico por opción
    switch (opcion) {
      case "descuento":
        await handleDescuento();
        break;
      case "compra":
        if (elements.btnBuscar.textContent === "Acumular") {
          await handleCompra();
          return;
        }
        break;
      case "express":
        // Para Express, solo buscar cliente y mostrar modal
        break;
    }

    await buscarCliente();

    // Actualizar estado después de la búsqueda
    if (opcion === "compra" && state.idClienteActual) {
      updateButtonState("compra");
    } else if (
      opcion === "express" &&
      state.idClienteActual &&
      state.datosClienteActual
    ) {
      mostrarModalExpress();
    }
  }

  async function handleDescuento() {
    const isExpressMode = state.expressValidado && elements.btnExpress.classList.contains("selected");
    const descuentoAplicar = isExpressMode ? 20000 : 50000;
    const nombreDescuento = isExpressMode ? "Express" : "VIP";
    
    if (elements.btnBuscar.textContent !== "Aplicar" && elements.btnBuscar.textContent !== "Aplicar Express") return;

    let saldo = parseFloat(document.getElementById("totalActual").value) || 0;
    
    if (saldo < descuentoAplicar) {
      return alert(
        `El saldo actual (₡${saldo.toLocaleString("es-CR")}) es insuficiente para aplicar el descuento ${nombreDescuento} de ₡${descuentoAplicar.toLocaleString("es-CR")}.`
      );
    }

    let saldoFinal = saldo - descuentoAplicar;

    try {
      const response = await fetch(
        "/CRM_INT/CRM/controller/ClienteController.php",
        {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: buildUpdateClienteBody(saldoFinal),
        }
      );

      const json = await response.json();
      if (!json.success) {
        return alert(
          "Ocurrió un error al actualizar el saldo en la base de datos."
        );
      }

      document.getElementById("totalActual").value = saldoFinal;
      state.datosClienteActual.acumulado = saldoFinal;

      // Mensaje personalizado según el tipo de descuento
      let mensaje = "";
      if (isExpressMode) {
        mensaje = `¡Descuento Express aplicado exitosamente!\n\n` +
                 `✅ Se descontaron ₡${descuentoAplicar.toLocaleString("es-CR")} de tu saldo\n` +
                 `💰 Saldo anterior: ₡${saldo.toLocaleString("es-CR")}\n` +
                 `💰 Saldo actual: ₡${saldoFinal.toLocaleString("es-CR")}\n\n` +
                 `🎉 ¡El Express ahora es GRATIS para ${state.nombreClienteActual}!`;
      } else {
        mensaje = `Descuento VIP exitoso, puedes aplicar el 15% a nombre del cliente VIP: ${state.nombreClienteActual}.\n` +
                 `El saldo actual cambió de ₡${saldo.toLocaleString("es-CR")} a ₡${saldoFinal.toLocaleString("es-CR")}.`;
      }
      
      alert(mensaje);
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
      const response = await fetch(
        "/CRM_INT/CRM/controller/CompraController.php",
        {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `action=create&total=${monto}&idCliente=${state.idClienteActual}`,
        }
      );

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
    return (
      `action=update&id=${data.id}&cedula=${data.cedula}&nombre=${data.nombre}` +
      `&correo=${data.correo}&telefono=${data.telefono}&lugarResidencia=${data.lugarResidencia}` +
      `&fechaCumpleanos=${data.fechaCumpleanos}&acumulado=${saldoFinal}`
    );
  }

  async function buscarCliente() {
    const inputId = elements.inputId.value.trim();
 console.log("Valor que se envía al servidor:", inputId);

    try {
      const response = await fetch(
        "/CRM_INT/CRM/controller/ClienteController.php",
        {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `action=read&id=${encodeURIComponent(inputId)}`,
        }
      );

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
      { id: "totalActual", value: state.datosClienteActual.acumulado },
    ];

    fields.forEach((field) => {
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
    if (!data.fechaCumpleanos || typeof confetti !== "function") return;

    if (esCumpleEstaSemana(data.fechaCumpleanos)) {
      // Múltiples efectos de confeti
      const confettiEffects = [
        { particleCount: 100, spread: 120, origin: { y: 0.6, x: 0.5 } },
        { particleCount: 80, spread: 120, origin: { y: 0.6, x: 0.1 } },
        { particleCount: 80, spread: 120, origin: { y: 0.6, x: 0.9 } },
      ];

      confettiEffects.forEach((effect) => confetti(effect));

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
    const modal = document.getElementById("modalExpress");
    if (!modal) {
      console.log("Modal Express no encontrado en la vista");
      return;
    }

    // Resetear el modal al estado inicial
    resetModalExpress();

    // Actualizar datos del cliente en el modal
    updateModalData("expressNombre", state.datosClienteActual.nombre);
    updateModalData("expressCorreo", state.datosClienteActual.correo);

    // Configurar botón de envío
    const btnEnviar = document.getElementById("btnEnviarCorreoExpress");
    if (btnEnviar) {
      btnEnviar.onclick = enviarCorreoExpress;
    }

    // Mostrar modal
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
  }

  function resetModalExpress() {
    // Limpiar temporizador anterior si existe
    if (state.expressTimer) {
      clearInterval(state.expressTimer);
      state.expressTimer = null;
    }

    // Restaurar contenido inicial del modal
    const modalBody = document.querySelector('#modalExpress .modal-body');
    if (modalBody) {
      modalBody.innerHTML = `
        <p class="mb-3">Se enviará un código de verificación al correo: <strong id="expressCorreo"></strong></p>
        <p class="text-muted">El código expirará en 5 minutos.</p>
      `;
    }

    // Restaurar botón del footer
    const btnEnviar = document.getElementById("btnEnviarCorreoExpress");
    if (btnEnviar) {
      btnEnviar.textContent = "Enviar Correo";
      btnEnviar.disabled = false;
      btnEnviar.className = "btn btn-primary";
    }
  }

  async function enviarCorreoExpress() {
    console.log("Enviando correo Express...");
    
    if (!state.idClienteActual || !state.datosClienteActual) {
      alert("Error: No hay cliente seleccionado");
      return;
    }

    try {
      // Deshabilitar botón y mostrar estado de carga
      const btnEnviar = document.getElementById("btnEnviarCorreoExpress");
      btnEnviar.disabled = true;
      btnEnviar.textContent = "Enviando...";

      // 1) Generar y enviar código
      const body = `action=generarCodigoExpress&idCliente=${state.idClienteActual}` +
                   `&correo=${encodeURIComponent(state.datosClienteActual.correo)}` +
                   `&nombre=${encodeURIComponent(state.datosClienteActual.nombre)}`;

      const response = await fetch('/CRM_INT/CRM/controller/ExpressController.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: body
      });

      const json = await response.json();
      
      if (!json.success) {
        btnEnviar.disabled = false;
        btnEnviar.textContent = "Enviar Correo";
        alert('Error al enviar correo Express: ' + (json.message || 'Error desconocido'));
        return;
      }

      console.log("Correo enviado exitosamente, cambiando modal...");

      // 2) Cambiar modal a modo validación
      cambiarModalAValidacion();

    } catch (error) {
      console.error("Error al enviar correo:", error);
      const btnEnviar = document.getElementById("btnEnviarCorreoExpress");
      btnEnviar.disabled = false;
      btnEnviar.textContent = "Enviar Correo";
      alert('Error de conexión al enviar el correo');
    }
  }

  function cambiarModalAValidacion() {
    const modalBody = document.querySelector('#modalExpress .modal-body');
    if (!modalBody) return;

    modalBody.innerHTML = `
      <div class="text-center mb-3">
        <p class="mb-2">Código enviado a: <strong>${state.datosClienteActual.correo}</strong></p>
        <div class="h4 mb-3 text-primary" id="relojExpress">05:00</div>
      </div>
      <div class="input-group mb-3">
        <input type="number" class="form-control" id="inputCodigoExpress" 
               placeholder="Ingresa el código de 6 dígitos" maxlength="6">
        <button class="btn btn-success" id="btnValidarExpress">Validar</button>
      </div>
      <div id="msgExpress" class="text-danger text-center"></div>
    `;

    // Cambiar el botón del footer
    const btnEnviar = document.getElementById("btnEnviarCorreoExpress");
    if (btnEnviar) {
      btnEnviar.textContent = "Reenviar Código";
      btnEnviar.disabled = false;
      btnEnviar.className = "btn btn-outline-secondary";
      btnEnviar.onclick = enviarCorreoExpress; // Permitir reenvío
    }

    // 3) Iniciar temporizador de 5 minutos
    iniciarTemporizador();

    // 4) Configurar validación
    document.getElementById('btnValidarExpress').onclick = validarCodigoExpress;
    
    // Auto-focus en el input
    document.getElementById('inputCodigoExpress').focus();
  }

  function iniciarTemporizador() {
    let restantes = 300; // 5 minutos en segundos
    const reloj = document.getElementById('relojExpress');
    
    if (!reloj) return;

    // Limpiar temporizador anterior si existe
    if (state.expressTimer) {
      clearInterval(state.expressTimer);
    }

    state.expressTimer = setInterval(() => {
      restantes--;
      const min = String(Math.floor(restantes / 60)).padStart(2, '0');
      const seg = String(restantes % 60).padStart(2, '0');
      reloj.textContent = `${min}:${seg}`;

      if (restantes <= 0) {
        clearInterval(state.expressTimer);
        state.expressTimer = null;
        reloj.textContent = '00:00';
        reloj.className = 'h4 mb-3 text-danger';
        
        const msgExpress = document.getElementById('msgExpress');
        if (msgExpress) {
          msgExpress.textContent = 'Código expirado. Solicita un nuevo código.';
        }
        
        // Deshabilitar botón de validar
        const btnValidar = document.getElementById('btnValidarExpress');
        if (btnValidar) {
          btnValidar.disabled = true;
        }
      }
    }, 1000);
  }

  async function validarCodigoExpress() {
    const inputCodigo = document.getElementById('inputCodigoExpress');
    const codigo = inputCodigo.value.trim();
    const msgExpress = document.getElementById('msgExpress');
    
    if (!codigo) {
      msgExpress.textContent = 'Por favor, digite el código';
      inputCodigo.focus();
      return;
    }

    if (codigo.length !== 6) {
      msgExpress.textContent = 'El código debe tener 6 dígitos';
      inputCodigo.focus();
      return;
    }

    try {
      const btnValidar = document.getElementById('btnValidarExpress');
      btnValidar.disabled = true;
      btnValidar.textContent = 'Validando...';

      const response = await fetch('/CRM_INT/CRM/controller/ExpressController.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=validarCodigoExpress&idCliente=${state.idClienteActual}&codigo=${codigo}`
      });

      const json = await response.json();
      
      if (json.success) {
        // Código validado correctamente
        clearInterval(state.expressTimer);
        state.expressTimer = null;
        
        // MARCAR EXPRESS COMO VALIDADO
        state.expressValidado = true;
        
        // Cerrar modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalExpress'));
        if (modal) {
          modal.hide();
        }
        
        // Mostrar confirmación
        alert('¡Código validado correctamente! Express activado.');
        
        // MANTENER EXPRESS SELECCIONADO y actualizar estado visual
        elements.btnExpress.classList.add("selected");
        
        // Agregar indicador visual de Express validado
        if (!elements.btnExpress.querySelector('.express-validado')) {
          const indicator = document.createElement('span');
          indicator.className = 'express-validado';
          indicator.innerHTML = ' ✓';
          indicator.style.color = '#28a745';
          indicator.style.fontWeight = 'bold';
          elements.btnExpress.appendChild(indicator);
        }
        
        // Si hay otra opción seleccionada, actualizar su estado
        const otherSelected = getSelectedOption();
        if (otherSelected && otherSelected !== "express") {
          updateButtonState(otherSelected);
        }
        
        console.log("Express validado y persistente para combinar con otras opciones");
        
      } else {
        msgExpress.textContent = json.message || 'Código incorrecto';
        btnValidar.disabled = false;
        btnValidar.textContent = 'Validar';
        inputCodigo.focus();
        inputCodigo.select();
      }
    } catch (error) {
      console.error("Error al validar código:", error);
      msgExpress.textContent = 'Error de conexión al validar el código';
      const btnValidar = document.getElementById('btnValidarExpress');
      btnValidar.disabled = false;
      btnValidar.textContent = 'Validar';
    }
  }

  function updateModalData(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
      element.textContent = value || "";
    }
  }

  // Funciones globales (mantenidas para compatibilidad)
  window.cargarHistorialCompras = async function (idCliente) {
    elements.historialCompras.innerHTML =
      '<div style="color:var(--gris)">Cargando historial...</div>';

    try {
      const response = await fetch(
        "/CRM_INT/CRM/controller/CompraController.php",
        {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `action=readByCliente&idCliente=${encodeURIComponent(
            idCliente
          )}`,
        }
      );

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
          <button class="btn btn-sm btn-danger" title="Eliminar" onclick="eliminarCompra(${
            compra.idCompra
          })">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    `;
  }

  window.eliminarCompra = async function (idCompra) {
    if (!confirm("¿Está seguro de eliminar esta compra?")) return;

    try {
      const response = await fetch(
        "/CRM_INT/CRM/controller/CompraController.php",
        {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `action=delete&idCompra=${encodeURIComponent(idCompra)}`,
        }
      );

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
    const modal = document.getElementById("modalCumpleanos");
    if (!modal) {
      console.log("Modal de cumpleaños no encontrado en esta vista");
      return;
    }

    updateModalData("nombreCumpleanero", nombreCliente);

    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();

    const btnIrCumpleanos = document.getElementById("btnIrCumpleanos");
    if (btnIrCumpleanos) {
      btnIrCumpleanos.onclick = function () {
        window.location.href = "/CRM_INT/CRM/index.php?view=cumple";
      };
    }
  };

  // Limpiar temporizador cuando se cierra el modal Express
  const modalExpress = document.getElementById("modalExpress");
  if (modalExpress) {
    modalExpress.addEventListener('hidden.bs.modal', function () {
      if (state.expressTimer) {
        clearInterval(state.expressTimer);
        state.expressTimer = null;
      }
    });
  }
});