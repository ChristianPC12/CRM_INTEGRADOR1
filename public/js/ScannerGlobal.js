// Scanner Global para códigos de barras - Funciona en todo el sistema
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
      console.log(`🚫 Scanner deshabilitado - usuario escribiendo en: ${activeElement.tagName}`);
      return; // Salir sin procesar
    }

    // DEBUG: Confirmar que el scanner está activo
    console.log(`🔍 Scanner ACTIVO en: ${currentView || 'dashboard'} - Tecla: ${e.key}`);

    const now = Date.now();
    const diff = now - lastKeyTime;
    lastKeyTime = now;

    // LÓGICA MEJORADA: Distinguir entre scanner y tecleo manual
    // - Scanner: teclas muy rápidas (<30ms) y consistentes
    // - Manual: más lento y errático
    if (diff < 30) {
      isScan = true; // Definitivamente es scanner
    } else if (diff > 300) {
      reset(); // Pausa muy larga = tecleo manual o nueva secuencia
      return;
    }

    if (e.key === 'Enter') {
      e.preventDefault();
      // SOLO procesar si realmente parece código de scanner
      if (buffer && isScan && buffer.length >= 8) { // Códigos de barras suelen ser 8+ dígitos
        console.log(` Procesando código de scanner: ${buffer}`);
        procesar(buffer);
      } else {
        console.log(` Ignorado - no parece código válido: "${buffer}" (len=${buffer.length}, isScan=${isScan})`);
      }
      reset();
      return;
    }

    if (e.key.length === 1 && /[0-9]/.test(e.key)) { // SOLO números para códigos de barras
      buffer += e.key;
      clearTimeout(timer);
      timer = setTimeout(() => {
        // Solo procesar si parece realmente un código de scanner
        if (buffer.length >= 8 && isScan) {
          console.log(`Timeout - procesando código: ${buffer}`);
          procesar(buffer);
        } else {
          console.log(`Timeout - ignorado: "${buffer}" (len=${buffer.length}, isScan=${isScan})`);
        }
        reset();
      }, 150);
    } else if (e.key.length === 1) {
      // Si hay letras mezcladas, probablemente es tecleo manual
      console.log(` Letra detectada "${e.key}" - probablemente tecleo manual`);
      reset();
    }
  });

  // Limpia si se pierde el foco de la pestaña
  window.addEventListener('blur', reset);
})();
