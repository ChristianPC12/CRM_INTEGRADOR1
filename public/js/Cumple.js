document.addEventListener("DOMContentLoaded", () => {
    cargarCumples();
    document.getElementById("btnEnviarCorreo").disabled = true;

    document.getElementById("formCorreo").addEventListener("submit", async (e) => {
        e.preventDefault();

        const id = document.getElementById("idCumple").value;
        if (!id) return;

        const actualizado = await cambiarEstado(id, 'LISTA');

        if (actualizado) {
            document.getElementById("btnEnviarCorreo").disabled = true;
            document.getElementById("formCorreo").reset();

            const fila = document.getElementById(`fila-${id}`);
            if (fila) {
                fila.querySelector("td:nth-child(6)").innerHTML = `<span class="badge bg-success">LISTA</span>`;
                const botonCorreo = fila.querySelector("td:last-child button");
                botonCorreo.disabled = true;
                botonCorreo.classList.add("disabled");
            }
        }
    });
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
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
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
                        <th>Correo</th>
                    </tr>
                </thead>
                <tbody>
    `;

    cumples.forEach(c => {
        const esLista = c.estado === 'LISTA';

        const badge = esLista
            ? `<span class="badge bg-success">LISTA</span>`
            : `<span class="badge bg-danger">PENDIENTE</span>`;

        const botonCorreo = `
            <button class="btn btn-sm btn-primary ${esLista ? 'disabled' : ''}"
                onclick="seleccionarCumple('${c.id}', '${c.nombre}', '${c.cedula}', '${c.correo}', '${c.telefono}')"
                ${esLista ? 'disabled' : ''}>
                <i class="fas fa-paper-plane"></i>
            </button>
        `;

        html += `
            <tr id="fila-${c.id}">
                <td>${c.nombre}</td>
                <td>${c.cedula}</td>
                <td>${c.correo}</td>
                <td>${c.telefono}</td>
                <td>${formatearFecha(c.fechaCumpleanos)}</td>
                <td>${badge}</td>
                <td class="text-center">${botonCorreo}</td>
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

const seleccionarCumple = (id, nombre, cedula, correo, telefono) => {
    document.getElementById("idCumple").value = id;
    document.getElementById("nombreCorreo").value = nombre;
    document.getElementById("cedulaCorreo").value = cedula;
    document.getElementById("correoCorreo").value = correo;
    document.getElementById("telefonoCorreo").value = telefono;
    document.getElementById("mensajeCorreo").value = `¡Feliz cumpleaños ${nombre}! Te esperamos para celebrarte 🎉`;

    document.getElementById("btnEnviarCorreo").disabled = false;
};

const cambiarEstado = async (id, nuevoEstado) => {
    const formData = new URLSearchParams();
    formData.append('action', 'cambiarEstado');
    formData.append('id', id);
    formData.append('estado', nuevoEstado);

    try {
        const res = await fetch("/CRM_INT/CRM/controller/CumpleController.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: formData.toString()
        });

        const data = await res.json();
        return data.success;
    } catch (error) {
        console.error("Error en cambiarEstado:", error);
        return false;
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
