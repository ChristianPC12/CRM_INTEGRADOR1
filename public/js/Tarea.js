$(document).ready(function () {
  let tareas = [];

  function listarTareas() {
    $.ajax({
      url: "/CRM_INT/CRM/controller/TareaController.php?action=readAll",
      type: "GET",
      dataType: "json",
      success: function (response) {
        if (response.success && response.data) {
          tareas = response.data;
          renderizarTareas();
        } else {
          console.warn("No se encontraron tareas.");
        }
      },
      error: function (xhr, status, error) {
        console.error(" Error al cargar tareas:", error);
      },
    });
  }

  function renderizarTareas() {
const ul = $("#contenedorTarjetas");
  ul.empty();

  if (tareas.length === 0) {
    ul.append(`<li class="todo-list-item">No hay tareas registradas.</li>`);
    return;
  }

  tareas.forEach((t) => {
    const estadoClase = t.estado === "completada" ? "estado-completada" : "estado-pendiente";
    const estadoTexto = t.estado === "completada" ? "Completada" : "Pendiente";

    const item = `
      <li class="todo-list-item">
        <div class="contenido-tarea">
          ${t.descripcion}
          <div class="${estadoClase}" style="margin-top: 0.5rem;">${estadoTexto}</div>
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

  $("#formTarea").on("submit", function (e) {
    e.preventDefault();
    const descripcion = $("#descripcion").val().trim();
    if (!descripcion) return;

    $.ajax({
      url: "/CRM_INT/CRM/controller/TareaController.php?action=create",
      type: "POST",
      data: { descripcion },
      dataType: "json",
      success: function (res) {
        if (res.success) {
          $("#descripcion").val("");
          listarTareas();
        } else {
          alert("No se pudo agregar la tarea.");
        }
      },
      error: function () {
        console.error(" Error al insertar tarea.");
      },
    });
  });

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

  listarTareas();
});