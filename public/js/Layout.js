// Archivo: public/js/Layout.js
// Detectar cuando se cierra la ventana/pestaña y cerrar sesión

document.addEventListener("DOMContentLoaded", function () {
  // Detectar cierre de ventana/pestaña
  window.addEventListener("beforeunload", function (e) {
    // Enviar petición síncrona para cerrar sesión
    navigator.sendBeacon(
      "/CRM_INT/CRM/controller/SessionController.php",
      JSON.stringify({ action: "close_session" })
    );
  });

  // También detectar cuando se abandona la página
  window.addEventListener("unload", function (e) {
    // Backup por si beforeunload no funciona
    navigator.sendBeacon(
      "/CRM_INT/CRM/controller/SessionController.php",
      JSON.stringify({ action: "close_session" })
    );
  });

  // Detectar cambio de visibilidad de la página (opcional, más agresivo)
  document.addEventListener("visibilitychange", function () {
    if (document.visibilityState === "hidden") {
      // Opcional: cerrar sesión cuando se oculta la página
      // Descomenta la siguiente línea si quieres que sea más estricto
      // navigator.sendBeacon('/CRM_INT/CRM/controller/SessionController.php', JSON.stringify({action: 'close_session'}));
    }
  });
});

// Función para confirmar cierre de sesión manual
function confirmarCerrarSesion() {
  if (confirm("¿Cerrar sesión?")) {
    window.location.href = "index.php?logout=1";
  }
}
