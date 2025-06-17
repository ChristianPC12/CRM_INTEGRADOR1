$(document).ready(function () {
  let indiceActual = 0;
  let tareas = [];

  // Cargar tareas al iniciar
  listarTareas();

  function variables() {
    return {
      descripcion: $("#descripcion").val().trim(),
    };
  }

  function limpiar() {
    $("#descripcion").val("").focus();
  }

  // Renderiza una sola tarea
  function mostrarTarea(index) {
    if (!tareas[index]) return;

    const tarea = tareas[index];
    const estadoTexto =
      tarea.estado === "terminado" ? "Terminado" : "Pendiente";
    const colorClase =
      tarea.estado === "terminado" ? "estado-verde" : "estado-rojo";

    const tarjetaHTML = `
      <div class="tarjeta">
        <div class="fecha-actual">${formatearFecha(tarea.fecha)}</div>
        <div class="contenido-tarea">${tarea.descripcion}</div>
        <button class="btn-estado ${colorClase}">${estadoTexto}</button>
        <button class="btn-cambiar">Cambiar estado</button>
      </div>
    `;

    $("#contenedorTarjetas").html(tarjetaHTML);
  }

  // Cargar tareas desde el servidor
  function listarTareas() {
    $.ajax({
      url: "/CRM_INT/CRM/controller/TareaController.php?action=readAll",
      type: "GET",
      dataType: "json",
      success: function (response) {
        if (response.success && response.data) {
          tareas = response.data.map((t) => ({
            ...t,
            estado: t.estado || "pendiente", // Por defecto
            fecha: t.fecha || new Date().toISOString(),
          }));
          indiceActual = 0;
          mostrarTarea(indiceActual);
        }
      },
      error: function () {
        console.error("❌ Error al cargar tareas.");
      },
    });
  }

  // Insertar nueva tarea
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

  // Cambiar estado
  $("#contenedorTarjetas").on("click", ".btn-cambiar", function () {
    const tarea = tareas[indiceActual];
    tarea.estado = tarea.estado === "terminado" ? "pendiente" : "terminado";
    mostrarTarea(indiceActual);
  });

  // Navegación flechas
  $("#flecha-izquierda").click(function () {
    if (tareas.length === 0) return;
    indiceActual = (indiceActual - 1 + tareas.length) % tareas.length;
    mostrarTarea(indiceActual);
  });

  $("#flecha-derecha").click(function () {
    if (tareas.length === 0) return;
    indiceActual = (indiceActual + 1) % tareas.length;
    mostrarTarea(indiceActual);
  });

  function formatearFecha(fechaISO) {
    const fecha = new Date(fechaISO);
    return fecha.toLocaleDateString("es-CR", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    });
  }

  // Eliminar tarea actual EN VIVO sin refrescar
  $("#btnEliminarTarea").click(function () {
    const tarea = tareas[indiceActual];
    if (!tarea || !tarea.id) return;

    if (confirm("¿Seguro que desea eliminar esta tarea?")) {
      $.ajax({
        url: "/CRM_INT/CRM/controller/TareaController.php?action=delete",
        type: "POST",
        data: { id: tarea.id },
        dataType: "json",
        success: function (response) {
          if (response.success) {
            // Eliminar del array local
            tareas.splice(indiceActual, 1);

            // Ajustar el índice
            if (indiceActual >= tareas.length) {
              indiceActual = tareas.length - 1;
            }

            // Mostrar la nueva tarea, si hay
            if (tareas.length > 0) {
              mostrarTarea(indiceActual);
            } else {
              // Si ya no hay tareas, limpiar el div
              $("#contenedorTarjetas").html(`
                <div class="tarjeta vacia">
                  <div class="contenido-tarea">No hay tareas disponibles.</div>
                </div>
              `);
            }
          } else {
            alert("No se pudo eliminar la tarea.");
          }
        },
        error: function () {
          console.error("❌ Error al eliminar la tarea.");
        },
      });
    }
  });
});
