let codigosGlobal = []; // Para filtro de búsqueda en vivo

/**
 * Carga todos los códigos desde el backend 
 */
const cargarCodigos = async () => {
    const contenedor = document.getElementById("codigoLista");
    contenedor.innerHTML = `<div class="text-center p-3">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2">Cargando códigos...</p>
    </div>`;
    try {
        const res = await fetch("/CRM_INT/CRM/controller/CodigoController.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "action=readAll"
        });
        const response = await res.json();
        if (response.success && response.data) {
            codigosGlobal = response.data;
            mostrarCodigos(response.data);
        } else {
            contenedor.innerHTML = '<div class="alert alert-warning">No se pudieron cargar los códigos</div>';
        }
    } catch {
        contenedor.innerHTML = '<div class="alert alert-danger">Error de conexión</div>';
    }
};

/**
 * Genera un código de barras usando el número de tarjeta
 */
function generarCodigoBarras(numeroTarjeta, elementoId) {
    try {
        const codigoParaBarras = String(numeroTarjeta).padStart(8, '0'); // Mínimo 8 dígitos
        JsBarcode(`#${elementoId}`, codigoParaBarras, {
            format: "CODE128",
            width: 2,
            height: 50,
            displayValue: true,
            fontSize: 12,
            margin: 5
        });
    } catch (error) {
        console.error('Error generando código de barras:', error);
        document.getElementById(elementoId).innerHTML = `<span class="text-danger">Error: ${numeroTarjeta}</span>`;
    }
}

/**
 * Genera un código de barras para impresión (tamaño tarjeta) con mayor calidad
 */
function generarCodigoBarrasParaImpresion(numeroTarjeta) {
    const canvas = document.createElement('canvas');
    const codigoParaBarras = String(numeroTarjeta).padStart(8, '0');
    JsBarcode(canvas, codigoParaBarras, {
        format: "CODE128",
        width: 2,
        height: 40,
        displayValue: true,
        fontSize: 12,
        margin: 3,
        background: "#ffffff",
        lineColor: "#000000",
        textAlign: "center",
        textPosition: "bottom",
        fontOptions: "bold"
    });
    const highResCanvas = document.createElement('canvas');
    const ctx = highResCanvas.getContext('2d');
    const scale = 2;
    highResCanvas.width = canvas.width * scale;
    highResCanvas.height = canvas.height * scale;
    ctx.imageSmoothingEnabled = false;
    ctx.webkitImageSmoothingEnabled = false;
    ctx.mozImageSmoothingEnabled = false;
    ctx.msImageSmoothingEnabled = false;
    ctx.drawImage(canvas, 0, 0, canvas.width * scale, canvas.height * scale);
    return highResCanvas;
}

/**
 * Imprime código de barras en PDF con alta calidad
 */
async function imprimirPDF(numeroTarjeta, barcodeId) {
    try {
        const canvas = generarCodigoBarrasParaImpresion(numeroTarjeta);
        const imgData = canvas.toDataURL('image/png', 1.0);
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({
            orientation: 'landscape',
            unit: 'mm',
            format: [85.6, 53.98]
        });
        const imgWidth = 70;
        const imgHeight = 25;
        const x = (85.6 - imgWidth) / 2;
        const y = (53.98 - imgHeight) / 2;
        doc.addImage(imgData, 'PNG', x, y, imgWidth, imgHeight, '', 'FAST');
        doc.save(`tarjeta_${numeroTarjeta}.pdf`);
        await actualizarContadorImpresiones(numeroTarjeta);
    } catch (error) {
        console.error('Error generando PDF:', error);
        alert('Error al generar PDF');
    }
}

/**
 * Imprimir directamente (botón Imprimir)
 */
async function imprimirCodigo(numeroTarjeta, barcodeId) {
    try {
        // 1. Crear ventana popup temporal
        const printWindow = window.open('', '', 'width=700,height=400');

        // 2. Generar código de barras en canvas y convertirlo en imagen
        const canvas = generarCodigoBarrasParaImpresion(numeroTarjeta);
        const imgData = canvas.toDataURL('image/png');

        // 3. Escribir HTML de impresión con CSS de tamaño etiqueta y horizontal
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Imprimir código de barras</title>
                <style>
                    @media print {
                        @page {
                            size: 62mm 29mm;
                            margin: 0;
                        }
                        body {
                            width: 62mm;
                            height: 29mm;
                            margin: 0;
                            padding: 0;
                            box-sizing: border-box;
                        }
                    }
                    html, body {
                        width: 62mm;
                        height: 29mm;
                        margin: 0;
                        padding: 0;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        background: #fff;
                    }
                    .barcode-container {
                        text-align: center;
                        width: 100%;
                    }
                    .barcode-image {
                        width: 58mm;
                        max-width: 100%;
                        height: 20mm;
                        object-fit: contain;
                    }
                </style>
            </head>
            <body>
                <div class="barcode-container">
                    <img src="${imgData}" alt="Código de barras ${numeroTarjeta}" class="barcode-image">
                </div>
                <script>
                    window.onload = function() {
                        window.print();
                        window.onafterprint = function() { window.close(); };
                    };
                </script>
            </body>
            </html>
        `);
        await actualizarContadorImpresiones(numeroTarjeta);
    } catch (error) {
        console.error('Error imprimiendo:', error);
        alert('Error al imprimir el código de barras');
    }
}


/**
 * Actualiza el contador de impresiones en la base de datos
 */
async function actualizarContadorImpresiones(numeroTarjeta) {
    try {
        await fetch("/CRM_INT/CRM/controller/CodigoController.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `action=incrementarImpresiones&idCliente=${numeroTarjeta}`
        });
        cargarCodigos();
    } catch (error) {
        console.error('Error actualizando contador:', error);
    }
}

/**
 * Redirige a CompraView con el idCliente (botón invisible sobre el código de barras)
 */
window.redirigirCompra = function(idCliente) {
    window.location.href = `/CRM_INT/CRM/index.php?view=compras&idCliente=${encodeURIComponent(idCliente)}`;
}

/** 
 * Muestra la tabla de códigos 
 */
function mostrarCodigos(codigos) {
    const contenedor = document.getElementById("codigoLista");
    if (!codigos || codigos.length === 0) {
        contenedor.innerHTML = '<div class="alert alert-info">No hay códigos registrados</div>';
        return;
    }

    let filas = codigos.map((codigo, index) => {
        const barcodeId = `barcode-${index}`;
        return `
            <tr>
                <td>${codigo.idCliente}</td>
                <td>${codigo.cedula ?? '-'}</td>
                <td>${codigo.nombre ?? '-'}</td>
                <td>${codigo.fechaRegistro ?? '-'}</td>
                <td>
                    <div class="text-center position-relative" style="width:120px; height:60px; margin:auto;">
    <canvas id="${barcodeId}" style="display:block; margin:0 auto; width:100%; height:auto;"></canvas>
                        <button 
                            class="btn-barcode-invisible" 
                            title="Ir a Beneficio"
                            onclick="redirigirCompra('${codigo.idCliente}')"
                            tabindex="-1"
                            style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;cursor:pointer;border:none;padding:0;margin:0;z-index:2;">
                        </button>
                    </div>
                </td>
                <td class="text-center">${codigo.cantImpresiones}</td>
                <td class="text-center">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="imprimirPDF('${codigo.idCliente}', '${barcodeId}')"
                                title="Descargar PDF">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success" 
                                onclick="imprimirCodigo('${codigo.idCliente}', '${barcodeId}')"
                                title="Imprimir">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join("");

    contenedor.innerHTML = `
        <table class="table table-striped table-hover mt-4">
            <thead class="table-dark">
                <tr>
                    <th>Número de Tarjeta</th>
                    <th>Cédula</th>
                    <th>Nombre</th>
                    <th>Fecha Registro</th>
                    <th class="text-center">Código de barras</th>
                    <th class="text-center">N° Impresiones</th>
                    <th class="text-center">Imprimir</th>
                </tr>
            </thead>
            <tbody>
                ${filas}
            </tbody>
        </table>
    `;

    setTimeout(() => {
        codigos.forEach((codigo, index) => {
            const barcodeId = `barcode-${index}`;
            generarCodigoBarras(codigo.idCliente, barcodeId);
        });
    }, 100);
}

/** 
 * Filtro de búsqueda en vivo 
 */
document.getElementById('buscarCodigo').addEventListener('input', function () {
    const valor = this.value.trim();
    if (!valor) {
        mostrarCodigos(codigosGlobal);
        return;
    }
    const filtrados = codigosGlobal.filter(c =>
        String(c.idCliente).toLowerCase().includes(valor.toLowerCase())
    );
    mostrarCodigos(filtrados);
});

/** 
 * Botón actualizar recarga la lista 
 */
document.getElementById('btnActualizar').addEventListener('click', cargarCodigos);

// --------- DETECCIÓN MEJORADA DE LECTOR DE CÓDIGO DE BARRAS ---------

document.addEventListener("DOMContentLoaded", function() {
    cargarCodigos();

    // Variables para la detección del lector
    const inputBusqueda = document.getElementById("buscarCodigo");
    let buffer = '';
    let lastKeyTime = Date.now();
    let isScanning = false;
    let scanTimeout;

    // Función para procesar el código escaneado
    function procesarCodigoEscaneado(codigo) {
        console.log('Código escaneado detectado:', codigo);
        
        // Quitar ceros a la izquierda (00000081 → 81)
        const codigoLimpio = parseInt(codigo, 10).toString();
        console.log('Código limpio (sin ceros):', codigoLimpio);
        
        // Limpiar el input
        inputBusqueda.value = '';
        
        // Redirigir a compras con el código limpio
        const url = `/CRM_INT/CRM/index.php?view=compras&idCliente=${encodeURIComponent(codigoLimpio)}`;
        console.log('Redirigiendo a:', url);
        window.location.href = url;
    }

    // Función para resetear el buffer
    function resetBuffer() {
        buffer = '';
        isScanning = false;
        if (scanTimeout) {
            clearTimeout(scanTimeout);
        }
    }

    // Evento keydown mejorado
    inputBusqueda.addEventListener('keydown', function(e) {
        const currentTime = Date.now();
        const timeDiff = currentTime - lastKeyTime;
        
        // Detectar si es entrada rápida (típico de lector de código de barras)
        if (timeDiff < 50) {
            isScanning = true;
        }
        
        lastKeyTime = currentTime;

        // Si pasa mucho tiempo entre teclas, resetear buffer
        if (timeDiff > 200) {
            resetBuffer();
        }

        // Manejar Enter
        if (e.key === "Enter") {
            e.preventDefault();
            
            if (buffer.length > 0) {
                procesarCodigoEscaneado(buffer);
                resetBuffer();
                return;
            }
        }
        
        // Manejar caracteres normales
        if (e.key.length === 1) {
            buffer += e.key;
            
            // Timeout para procesar automáticamente después de cierto tiempo
            clearTimeout(scanTimeout);
            scanTimeout = setTimeout(() => {
                if (buffer.length >= 4 && isScanning) {
                    procesarCodigoEscaneado(buffer);
                    resetBuffer();
                }
            }, 100);
        }
    });

    // Evento keyup para capturar casos donde el Enter no se detecta bien
    inputBusqueda.addEventListener('keyup', function(e) {
        if (e.key === "Enter" && buffer.length > 0) {
            procesarCodigoEscaneado(buffer);
            resetBuffer();
        }
    });

    // Evento input como respaldo
    inputBusqueda.addEventListener('input', function(e) {
        const valor = this.value.trim();
        
        // Si el valor cambió rápidamente y es largo, podría ser un escaneo
        if (valor.length >= 4 && isScanning) {
            setTimeout(() => {
                if (this.value === valor) {
                    procesarCodigoEscaneado(valor);
                    resetBuffer();
                }
            }, 200);
        }
    });

    // Resetear buffer cuando se enfoca el input
    inputBusqueda.addEventListener('focus', function() {
        resetBuffer();
    });

    // Asegurar que el input esté siempre enfocado para capturar la entrada del lector
    inputBusqueda.focus();
    
    // Re-enfocar el input periódicamente
    setInterval(() => {
        if (document.activeElement !== inputBusqueda) {
            inputBusqueda.focus();
        }
    }, 1000);
});

// Función de respaldo para probar manualmente
window.testRedireccion = function(idCliente) {
    const url = `/CRM_INT/CRM/index.php?view=compras&idCliente=${encodeURIComponent(idCliente)}`;
    console.log('URL de prueba:', url);
    window.location.href = url;
}