$(document).ready(function () {
  // -------------------------------
  // 🔹 Variable global de tareas
  // -------------------------------
  let tareas = [];

  // -------------------------------
  // 🔹 Listar todas las tareas desde el servidor
  // -------------------------------
  function listarTareas() {
    $.ajax({
      url: "/CRM_INT/CRM/controller/TareaController.php?action=readAll",
      type: "GET",
      dataType: "json",
      success: function (response) {
        if (response.success && response.data) {
          tareas = response.data;  // Guardar tareas en memoria
          renderizarTareas();      // Dibujar tareas en pantalla
        } else {
          console.warn("No se encontraron tareas.");
        }
      },
      error: function (xhr, status, error) {
        console.error(" Error al cargar tareas:", error);
      },
    });
  }

  // -------------------------------
  // 🔹 Renderizar las tareas en la lista del DOM
  // -------------------------------
  function renderizarTareas() {
    const ul = $("#contenedorTarjetas");
    ul.empty(); // Limpiar lista antes de redibujar

    if (tareas.length === 0) {
      ul.append(`<li class="todo-list-item">No hay tareas registradas.</li>`);
      return;
    }

    tareas.forEach((t) => {
      // Estado de la tarea (pendiente o completada)
      const estadoClase = t.estado === "completada" ? "estado-completada" : "estado-pendiente";
      const estadoTexto = t.estado === "completada" ? "Completada" : "Pendiente";

      // Formatear fecha si existe
      let fechaStr = "";
      if (t.fechaCreacion) {
        const fecha = new Date(t.fechaCreacion);
        fechaStr = fecha.toLocaleDateString("es-CR", {
          day: "2-digit",
          month: "2-digit",
          year: "numeric",
        });
      }
     
      // Plantilla HTML de cada tarea
      const item = `
        <li class="todo-list-item">
          <div class="contenido-tarea">
            <div class="descripcion-tarea">${t.descripcion}</div>
            <div class="info-tarea" style="margin-top: 0.5rem; display: flex; align-items: center; gap: 10px;">
              <span class="${estadoClase}">${estadoTexto}</span>
              ${fechaStr ? `<span class="fecha-tarea">${fechaStr}</span>` : ''}
            </div>
          </div>
          <div>
            <button class="btn-cambiar" data-id="${t.id}" data-estado="${t.estado}">Cambiar estado</button>
            <button class="btn-eliminar" data-id="${t.id}">Eliminar</button>
          </div>
        </li>
      `;
      ul.append(item);
    });
  }

  // -------------------------------
  // 🔹 Crear nueva tarea (formulario)
  // -------------------------------
  $("#formTarea").on("submit", function (e) {
    e.preventDefault();
    const descripcion = $("#descripcion").val().trim();
    if (!descripcion) return; // Evita tareas vacías

    $.ajax({
      url: "/CRM_INT/CRM/controller/TareaController.php?action=create",
      type: "POST",
      data: { descripcion },
      dataType: "json",
      success: function (res) {
        if (res.success) {
          $("#descripcion").val(""); // Limpiar input
          listarTareas();            // Recargar lista
        } else {
          alert("No se pudo agregar la tarea.");
        }
      },
      error: function () {
        console.error(" Error al insertar tarea.");
      },
    });
  });

  // -------------------------------
  // 🔹 Cambiar estado de una tarea
  // -------------------------------
  $("#contenedorTarjetas").on("click", ".btn-cambiar", function () {
    const id = $(this).data("id");
    const estado = $(this).data("estado");
    const nuevoEstado = estado === "pendiente" ? "completada" : "pendiente";

    $.ajax({
      url: "/CRM_INT/CRM/controller/TareaController.php?action=update",
      type: "POST",
      data: { id, estado: nuevoEstado },
      dataType: "json",
      success: function (res) {
        if (res.success) listarTareas();
        else alert("Error al cambiar el estado.");
      },
    });
  });

  // -------------------------------
  // 🔹 Eliminar una tarea
  // -------------------------------
  $("#contenedorTarjetas").on("click", ".btn-eliminar", function () {
    const id = $(this).data("id");
    if (!confirm("¿Eliminar esta tarea?")) return;

    $.ajax({
      url: "/CRM_INT/CRM/controller/TareaController.php?action=delete",
      type: "POST",
      data: { id },
      dataType: "json",
      success: function (res) {
        if (res.success) listarTareas();
        else alert("Error al eliminar la tarea.");
      },
    });
  });

  // -------------------------------
  // 🔹 Inicializar cargando las tareas
  // -------------------------------
  listarTareas();
});
