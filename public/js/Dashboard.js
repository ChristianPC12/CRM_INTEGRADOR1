$(document).ready(function () {
    // Contador de caracteres para el input de tareas
    const $inputDescripcion = $("#descripcion");
    const $contador = $("#contador-caracteres");

    $inputDescripcion.on("input", function () {
        const max = 220;
        const restante = max - $(this).val().length;
        $contador.text(restante);
    });

    // Obtener clientes
    $.ajax({
        url: "/CRM_INT/CRM/controller/ClienteController.php?action=readAll",
        type: "GET",
        dataType: "json",
        success: function (res) {
            if (res.success && res.data) {
                const clientes = res.data;

                // Total Clientes
                $("#total-clientes").text(clientes.length.toLocaleString());

                // Cliente del Mes
                const clienteMes = clientes
                    .filter(c => c.acumulado && !isNaN(c.acumulado) && parseFloat(c.acumulado) > 0)
                    .reduce((max, cliente) =>
                        cliente.acumulado > max.acumulado ? cliente : max, { acumulado: 0 }
                    );

                if (clienteMes && clienteMes.nombre) {
                    $("#cliente-mes-nombre").text(clienteMes.nombre);
                    $("#cliente-mes-valor").text(formatearColones(clienteMes.acumulado));
                } else {
                    $("#cliente-mes-nombre").text("N/A");
                    $("#cliente-mes-valor").text(formatearColones(0));
                }

                // Clientes que cumplen años este mes
                const mesActual = new Date().getMonth() + 1;
                const cumpleaneros = clientes.filter(cliente => {
                    if (!cliente.fechaCumpleanos) return false;
                    const mes = new Date(cliente.fechaCumpleanos).getMonth() + 1;
                    return mes === mesActual;
                });

                const limite = 3;
                if (cumpleaneros.length > 0) {
                    const nombres = cumpleaneros.slice(0, limite).map(c => c.nombre);
                    const textoFinal = nombres.join(", ") + (cumpleaneros.length > limite ? " y más..." : "");
                    $("#cumple-texto").text(textoFinal).attr("title", cumpleaneros.map(c => c.nombre).join(", "));
                } else {
                    $("#cumple-texto").text("No hay cumpleaños este mes");
                }
            }
        },
        error: function (xhr, status, error) {
            console.error("Error al obtener clientes:");
            console.log("Status:", status);
            console.log("Error:", error);
            console.log("Respuesta del servidor:", xhr.responseText);
        }
    });

    // Obtener total de ventas
    $.ajax({
        url: "/CRM_INT/CRM/controller/CompraController.php?action=readAll",
        type: "GET",
        dataType: "json",
        success: function (res) {
            if (res.success && res.data) {
                const compras = res.data;
                const totalVentas = compras.reduce((total, compra) =>
                    total + parseFloat(compra.total || 0), 0
                );
                $("#total-ventas").text(formatearColones(totalVentas));
            }
        },
        error: function (xhr, status, error) {
            console.error("Error al obtener ventas:");
            console.log("Status:", status);
            console.log("Error:", error);
            console.log("Respuesta del servidor:", xhr.responseText);
        }
    });

    // Función para resetear el contador de caracteres
    function resetearContador() {
        $contador.text(220);
    }

    // Interceptar el envío del formulario de tareas para resetear el contador
    $(document).on('submit', '#formTarea', function() {
        // Cuando el formulario se envía exitosamente, resetear el contador
        setTimeout(function() {
            if ($inputDescripcion.val() === '') {
                // Solo resetear si el input se limpió (indicando éxito)
                resetearContador();
            }
        }, 100);
    });
});

//Función para formatear los numeros a colones y formato local
function formatearColones(valor) {
    return new Intl.NumberFormat('es-CR', {
        style: 'currency',
        currency: 'CRC',
        minimumFractionDigits: 0
    }).format(valor);
    
}
