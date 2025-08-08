/*  Captura de lector de código de barras – Global  */
(() => {
  const REDIRECT = '/CRM_INT/CRM/index.php?view=compras&idCliente=';
  let buffer = '';
  let lastKeyTime = Date.now();
  let isScan = false;
  let timer;

  function reset() {
    buffer = '';
    isScan = false;
    clearTimeout(timer);
  }

  function procesar(code) {
    if (!code) return;
    const limpio = parseInt(code, 10).toString();          // quita ceros a la izquierda
    window.location.href = `${REDIRECT}${encodeURIComponent(limpio)}&buscar=auto`;
  }

  document.addEventListener('keydown', e => {
    // DEBUG: Mostrar información sobre el contexto actual
    const currentView = new URLSearchParams(window.location.search).get('view');
    const currentPath = window.location.pathname;
    
    // SOLO PROCESAR en módulos específicos donde el scanner debe funcionar
    const allowedViews = ['compras']; // Solo en el módulo de compras
    
    // Si no estamos en un módulo permitido, NO procesar
    if (currentView && !allowedViews.includes(currentView)) {
      console.log(`🚫 Scanner deshabilitado en módulo: ${currentView}`);
      return; 
    }
    
    // Si no hay view (dashboard), también deshabilitar
    if (!currentView) {
      console.log(`🚫 Scanner deshabilitado en dashboard`);
      return; // Salir completamente
    }

    // NO PROCESAR si el usuario está escribiendo en un input, textarea, o elemento editable
    const activeElement = document.activeElement;
    if (activeElement && (
      activeElement.tagName === 'INPUT' ||
      activeElement.tagName === 'TEXTAREA' ||
      activeElement.tagName === 'SELECT' ||
      activeElement.isContentEditable ||
      activeElement.closest('[contenteditable="true"]') ||
      activeElement.closest('.modal') // No procesar en modales
    )) {
      console.log(` Scanner deshabilitado - elemento activo: ${activeElement.tagName}`);
      return; // Salir sin procesar
    }

    // DEBUG: Confirmar que el scanner está activo
    console.log(` Scanner ACTIVO en: ${currentView || 'dashboard'} - Tecla: ${e.key}`);

    const now = Date.now();
    const diff = now - lastKeyTime;
    lastKeyTime = now;

    // Entrada ultra‑rápida ⇒ seguramente lector
    if (diff < 50) isScan = true;
    if (diff > 200) reset();               // pausa larga ⇒ descarta

    if (e.key === 'Enter') {               // lector manda Enter al final
      e.preventDefault();
      if (buffer) procesar(buffer);
      reset();
      return;
    }

    if (e.key.length === 1) {              // solo caracteres imprimibles
      buffer += e.key;
      clearTimeout(timer);
      timer = setTimeout(() => {           // respaldo por si no llegó Enter
        if (buffer.length >= 4 && isScan) {
          procesar(buffer);
          reset();
        }
      }, 100);
    }
  });

  // Limpia si se pierde el foco de la pestaña
  window.addEventListener('blur', reset);
})();
