document.addEventListener("DOMContentLoaded", () => {
    cargarCumples();
});

const cargarCumples = async () => {
    const contenedor = document.getElementById("cumpleLista");
    contenedor.innerHTML = `
        <div class="text-center p-3">
            <div class="spinner-border text-warning" role="status"></div>
            <p class="mt-2">Cargando cumpleaños...</p>
        </div>
    `;

    try {
        const res = await fetch("/CRM_INT/CRM/controller/CumpleController.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "action=readSemana"
        });

        const data = await res.json();

        if (data.success) {
            renderizarTabla(data.data);
        } else {
            contenedor.innerHTML = `<div class="alert alert-danger text-center">${data.message}</div>`;
        }
    } catch (error) {
        console.error("Error cargando cumpleaños:", error);
        contenedor.innerHTML = `<div class="alert alert-danger text-center">Error al cargar los cumpleaños.</div>`;
    }
};

const renderizarTabla = (cumples) => {
    const contenedor = document.getElementById("cumpleLista");

    if (cumples.length === 0) {
        contenedor.innerHTML = `<div class="alert alert-info text-center">No hay cumpleaños esta semana.</div>`;
        return;
    }

    let html = `
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Cédula</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Fecha de Cumpleaños</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
    `;

    cumples.forEach(c => {
        const botonesEstado = `
            <button class="btn btn-sm btn-warning me-1" onclick="cambiarEstado(${c.id}, 'PENDIENTE', this)">Pendiente</button>
            <button class="btn btn-sm btn-success" onclick="cambiarEstado(${c.id}, 'LISTA', this)">Lista</button>
        `;

        const mensajeAccion = c.estado === 'LISTA'
            ? `<span class="text-success fw-bold">Cliente atendido</span>`
            : `<span class="text-warning fw-bold">Cliente pendiente</span>`;

        html += `
            <tr>
                <td>${c.nombre}</td>
                <td>${c.cedula}</td>
                <td>${c.correo}</td>
                <td>${c.telefono}</td>
                <td>${formatearFecha(c.fechaCumpleanos)}</td>
                <td>${botonesEstado}</td>
                <td class="mensaje-accion">${mensajeAccion}</td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    contenedor.innerHTML = html;
};

const cambiarEstado = async (id, nuevoEstado, boton) => {
    const formData = new URLSearchParams();
    formData.append('action', 'cambiarEstado');
    formData.append('id', id);
    formData.append('estado', nuevoEstado);

    try {
        const res = await fetch("/CRM_INT/CRM/controller/CumpleController.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: formData.toString()
        });

        const data = await res.json();

        if (data.success) {
            // Cambiar el mensaje de acción en el DOM sin recargar la tabla
            const fila = boton.closest("tr");
            const celdaAccion = fila.querySelector(".mensaje-accion");

            if (nuevoEstado === "LISTA") {
                celdaAccion.innerHTML = `<span class="text-success fw-bold">Cliente atendido</span>`;
            } else {
                celdaAccion.innerHTML = `<span class="text-warning fw-bold">Cliente pendiente</span>`;
            }
        } else {
            alert("Error al cambiar estado: " + data.message);
        }
    } catch (error) {
        console.error("Error en cambiarEstado:", error);
        alert("Error en la solicitud");
    }
};

const formatearFecha = (fechaStr) => {
    if (!fechaStr) return "Fecha no disponible";

    const partes = fechaStr.split("-");
    if (partes.length !== 3) return "Fecha inválida";

    const [anio, mes, dia] = partes;
    const fecha = new Date(anio, mes - 1, dia);

    if (isNaN(fecha)) return "Fecha inválida";

    return fecha.toLocaleDateString("es-CR", {
        day: "2-digit",
        month: "long",
        year: "numeric"
    });
};
