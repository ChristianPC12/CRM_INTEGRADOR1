// Tarea.js
$(document).ready(function () {
  listarTareas();

  // Captura los datos del formulario
  function variables() {
    return {
      descripcion: $("#descripcion").val().trim(),
    };
  }

  // Limpia el input luego de agregar o eliminar
  function limpiar() {
    $("#descripcion").val("").focus();
  }

  // Carga todas las tareas desde el servidor
  function listarTareas() {
    $.ajax({
      url: "/CRM_INT/CRM/controller/TareaController.php?action=readAll",
      type: "GET",
      dataType: "json",
      success: function (response) {
        if (response.success && response.data) {
          const lista = $("#listaTareas");
          lista.empty(); // limpia lista previa

          response.data.forEach((tarea) => {
            const li = `
                            <li>
                                ${tarea.descripcion}
                                <i class="bi bi-x-circle-fill" data-id="${tarea.id}" title="Eliminar tarea"></i>
                            </li>
                        `;
            lista.append(li);
          });
        }
      },
      error: function () {
        console.error("❌ Error al cargar tareas.");
      },
    });
  }

  // Inserta nueva tarea al enviar el formulario
  $("#formTarea").on("submit", function (e) {
    e.preventDefault();
    const datos = variables();

    if (datos.descripcion === "") return;

    $.ajax({
      url: "/CRM_INT/CRM/controller/TareaController.php?action=create",
      type: "POST",
      data: datos,
      dataType: "json",
      success: function (response) {
        if (response.success) {
          listarTareas();
          limpiar();
        } else {
          alert("No se pudo guardar la tarea.");
        }
      },
      error: function () {
        console.error("❌ Error al insertar tarea.");
      },
    });
  });

  // Elimina una tarea por su ID
  $("#listaTareas").on("click", "i[data-id]", function () {
    const id = $(this).data("id");

    $.ajax({
      url: "/CRM_INT/CRM/controller/TareaController.php?action=delete",
      type: "POST",
      data: { id },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          listarTareas();
        } else {
          alert("Error al eliminar la tarea.");
        }
      },
      error: function () {
        console.error("❌ Error al eliminar tarea.");
      },
    });
  });
});
