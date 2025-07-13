document.addEventListener("DOMContentLoaded", () => {
    mostrarSemanaActual(); // ⬅️ NUEVO: Mostrar el rango apenas cargue
    cargarCumples();
    document.getElementById("btnEnviarCorreo").disabled = true;

    // Función para actualizar el badge de cumpleaños pendientes en el sidebar
    function actualizarCumpleBadgeSidebar() {
        fetch('/CRM_INT/CRM/controller/CumpleController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=hayPendientes'
        })
        .then(res => res.json())
        .then(data => {
            if (window.mostrarCumpleBadge) {
                window.mostrarCumpleBadge(data.success && data.hayPendientes);
            }
        });
    }

    document.getElementById("formCorreo").addEventListener("submit", async (e) => {
        e.preventDefault();

        const id = document.getElementById("idCumple").value;
        const nombre = document.getElementById("nombreCorreo").value;
        const correo = document.getElementById("correoCorreo").value;

        if (!correo || correo.trim() === "") {
            Swal.fire({
                icon: "info",
                title: "¡No tiene correo!",
                text: `${nombre} no tiene correo registrado. Contactalo por teléfono para felicitarlo.`,
            });
            return;
        }

        if (!id || !nombre || !correo) return;

        const mensaje = `¡Feliz cumpleaños ${nombre}! 🎉 En nuestro restaurante queremos celebrarte con una comida, bebida o postre totalmente gratis para vos. Te esperamos hoy mismo para consentirte como te merecés.`;

        const formData = new URLSearchParams();
        formData.append("action", "enviarCorreoCumple");
        formData.append("nombre", nombre);
        formData.append("correo", correo);
        formData.append("mensaje", mensaje);

        try {
            const res = await fetch("/CRM_INT/CRM/controller/CumpleController.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: formData.toString()
            });

            const data = await res.json();

            if (data.success) {
                await cambiarEstado(id, 'LISTA');
                Swal.fire("¡Éxito!", data.message, "success");
                document.getElementById("formCorreo").reset();
                document.getElementById("btnEnviarCorreo").disabled = true;
                cargarCumples();
                actualizarCumpleBadgeSidebar(); // <--- Actualiza el badge
            } else {
                Swal.fire("Error", data.message, "error");
            }
        } catch (err) {
            console.error("Error al enviar correo:", err);
            Swal.fire("Error", "No se pudo conectar con el servidor", "error");
        }
    });
});

const mostrarSemanaActual = () => {
    const hoy = new Date();
    const diaActual = hoy.getDay(); // 0 (Domingo) a 6 (Sábado)
    const diffInicio = hoy.getDate() - diaActual + (diaActual === 0 ? -6 : 1);
    const lunes = new Date(hoy.setDate(diffInicio));
    const domingo = new Date(lunes);
    domingo.setDate(lunes.getDate() + 6);

    const opciones = { day: '2-digit', month: 'long' };

    const formatoLunes = lunes.toLocaleDateString('es-CR', opciones);
    const formatoDomingo = domingo.toLocaleDateString('es-CR', opciones);

    const div = document.getElementById("rangoSemana");
    if (div) {
        div.innerHTML = `📆 Semana actual: <strong>${formatoLunes}</strong> al <strong>${formatoDomingo}</strong>`;
    }
};

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

    const pendientes = cumples.filter(c => c.estado === 'PENDIENTE');
    pendientes.sort((a, b) => {
        const fechaA = new Date(a.fechaCumpleanos);
        const fechaB = new Date(b.fechaCumpleanos);
        return fechaB - fechaA;
    });

    if (pendientes.length === 0) {
        contenedor.innerHTML = `<div class="alert alert-info text-center">No hay cumpleaños pendientes esta semana.</div>`;
        return;
    }

    let html = `
        <div class="table-responsive">
            <table class="table table-bordered table-hover" style="background: var(--amarillo); color: var(--negro);">
                <thead style="background: var(--negro); color: var(--amarillo);">
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

    let recordatorios = '';
    let tieneSinCorreo = false;

    pendientes.forEach(c => {
        let badge = `<span class="badge bg-danger">PENDIENTE</span>`;
        if (c.estado === 'LISTA') {
            badge = `<span class="badge bg-success">LISTO</span>`;
        }

        const botonCorreo = `
            <button class="btn btn-sm btn-primary"
                onclick="seleccionarCumple('${c.id}', '${c.nombre}', '${c.cedula}', '${c.correo}', '${c.telefono}')">
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

        if (!c.correo || c.correo.trim() === '') {
            tieneSinCorreo = true;
            recordatorios += `
                <li>
                    <i class="fa-solid fa-phone text-warning me-1"></i>
                    <strong>${c.cedula}</strong> - ${c.nombre} → 
                    <span class="text-primary fw-bold">Llamar al ${c.telefono}</span>
                </li>
            `;
        }
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    contenedor.innerHTML = html;

    const divRecordatorio = document.getElementById("recordatorioLlamadas");
    const ulRecordatorio = document.getElementById("listaRecordatorios");

    if (tieneSinCorreo) {
        ulRecordatorio.innerHTML = recordatorios;
        divRecordatorio.classList.remove("d-none");
    } else {
        divRecordatorio.classList.add("d-none");
        ulRecordatorio.innerHTML = '';
    }
};

const seleccionarCumple = (id, nombre, cedula, correo, telefono) => {
    document.getElementById("idCumple").value = id;
    document.getElementById("nombreCorreo").value = nombre;
    document.getElementById("cedulaCorreo").value = cedula;
    document.getElementById("correoCorreo").value = correo;
    document.getElementById("telefonoCorreo").value = telefono;

    const btn = document.getElementById("btnEnviarCorreo");
    if (!correo) {
        Swal.fire({
            icon: "warning",
            title: "¡Este cliente no tiene correo!",
            text: "Recordá llamarlo o escribirle un mensaje.",
            confirmButtonText: "Entendido"
        });
        btn.disabled = true;
    } else {
        btn.disabled = false;
    }
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
