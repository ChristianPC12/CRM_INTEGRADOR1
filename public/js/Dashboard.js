/*
document.addEventListener('DOMContentLoaded', () => {
    const ahora = new Date();
    const horas = ahora.getHours();
    let saludo = "Buenas noches";

    if (horas >= 5 && horas < 12) saludo = "Buenos días";
    else if (horas >= 12 && horas < 19) saludo = "Buenas tardes";

    const mensaje = document.getElementById('saludoDashboard');
    if (mensaje) mensaje.innerHTML = saludo + " 👋";

    const fecha = document.getElementById('fechaActual');
    if (fecha) fecha.textContent = ahora.toLocaleDateString();
});
*/
