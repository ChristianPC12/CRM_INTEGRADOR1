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
