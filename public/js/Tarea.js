$(document).ready(function () {
  let indiceActual = 0;
  let tareas = [];
  const label = $("#descripcionInfo");

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

  function mostrarTarea(index) {
    if (!tareas[index]) return;

    const tarea = tareas[index];
    const estadoTexto =
      tarea.estado === "terminado" ? "Terminado" : "Pendiente";
    const colorClase =
      tarea.estado === "terminado" ? "estado-verde" : "estado-rojo";

    const tarjetaHTML = `
  <div class="tarjeta">
    <label class="fecha-creacion">${formatearFecha(tarea.fechaCreacion)}</label>
    <div class="contenido-tarea">
      ${tarea.descripcion}
    </div>
    <button class="btn-estado ${colorClase}">${estadoTexto}</button>
    <button class="btn-cambiar">Cambiar estado</button>
  </div>
`;

    $("#contenedorTarjetas").html(tarjetaHTML);
  }

  function listarTareas() {
    $.ajax({
      url: "/CRM_INT/CRM/controller/TareaController.php?action=readAll",
      type: "GET",
      dataType: "json",
      success: function (response) {
        if (response.success && response.data) {
          tareas = response.data.map((t) => ({
            ...t,
            estado: t.estado || "pendiente",
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

     if (datos.descripcion.length === 0) return;

    if (datos.descripcion.length > 220) {
      alert(
        `Has escrito ${datos.descripcion.length} caracteres.\nEl máximo permitido es 220.`
      );
      $("#descripcion").val("").focus();
      return;
    }

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
          alert(
            "Has excedido el límite de 220 caracteres. \nNo se registró la descripción."
          );
          $("#descripcion").val("").focus();
        }
      },
      error: function () {
        console.error("❌ Error al insertar tarea.");
      },
    });
  });

  // Validación y etiqueta dinámica
  $("#descripcion").on("focus", function () {
    const texto = $(this).val().trim();
    if (texto.length > 220) {
      label
        .text("Has excedido el límite de 220 caracteres.")
        .addClass("visible");
    } else {
      label.text("Podés escribir hasta 220 caracteres.").addClass("visible");
    }
  });

  $("#descripcion").on("input", function () {
    const texto = $(this).val().trim();
    if (texto.length > 220) {
      label
        .text("Has excedido el límite de 220 caracteres.")
        .addClass("visible");
    } else {
      label.text("Podés escribir hasta 220 caracteres.").addClass("visible");
    }
  });

  $("#descripcion").on("blur", function () {
    label.removeClass("visible").text("");
  });

  $("#contenedorTarjetas").on("click", ".btn-cambiar", function () {
    const tarea = tareas[indiceActual];
    tarea.estado = tarea.estado === "terminado" ? "pendiente" : "terminado";
    mostrarTarea(indiceActual);
  });

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
            tareas.splice(indiceActual, 1);
            if (indiceActual >= tareas.length) {
              indiceActual = tareas.length - 1;
            }

            if (tareas.length > 0) {
              mostrarTarea(indiceActual);
            } else {
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

  function formatearFecha(fechaISO) {
    const fecha = new Date(fechaISO);
    return fecha.toLocaleDateString("es-CR", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    });
  }

  const contador = $("#contadorCaracteres");

  $("#descripcion").on("input", function () {
    const texto = $(this).val();
    contador.text(`${texto.length}/220`).addClass("visible");
  });

  $("#descripcion").on("focus", function () {
    const texto = $(this).val();
    contador.text(`${texto.length}/220`).addClass("visible");
  });

  $("#descripcion").on("blur", function () {
    contador.removeClass("visible").text("");
  });
});
