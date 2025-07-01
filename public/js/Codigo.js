// public/js/Codigo.js 
 
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
        // Usar el número de tarjeta como base para el código de barras
        // Se puede formatear o procesar según necesidades específicas
        const codigoParaBarras = String(numeroTarjeta).padStart(8, '0'); // Asegura mínimo 8 dígitos
        
        JsBarcode(`#${elementoId}`, codigoParaBarras, {
            format: "CODE128", // Formato del código de barras
            width: 2,
            height: 50,
            displayValue: true, // Muestra el número debajo del código
            fontSize: 12,
            margin: 5
        });
    } catch (error) {
        console.error('Error generando código de barras:', error);
        // Si hay error, mostrar el número como texto plano
        document.getElementById(elementoId).innerHTML = `<span class="text-danger">Error: ${numeroTarjeta}</span>`;
    }
}

/**
 * Genera un código de barras para impresión (tamaño tarjeta) con mayor calidad
 */
function generarCodigoBarrasParaImpresion(numeroTarjeta) {
    const canvas = document.createElement('canvas');
    const codigoParaBarras = String(numeroTarjeta).padStart(8, '0');
    
    // Configuración para mayor calidad
    JsBarcode(canvas, codigoParaBarras, {
        format: "CODE128",
        width: 2, // Aumentado para mejor calidad
        height: 40, // Altura óptima para lectura
        displayValue: true,
        fontSize: 12,
        margin: 3,
        background: "#ffffff", // Fondo blanco explícito
        lineColor: "#000000", // Líneas negras sólidas
        textAlign: "center",
        textPosition: "bottom",
        fontOptions: "bold"
    });
    
    // Crear un canvas de mayor resolución para mejor calidad
    const highResCanvas = document.createElement('canvas');
    const ctx = highResCanvas.getContext('2d');
    
    // Escalar 2x para mayor nitidez
    const scale = 2;
    highResCanvas.width = canvas.width * scale;
    highResCanvas.height = canvas.height * scale;
    
    // Configurar para imágenes nítidas
    ctx.imageSmoothingEnabled = false;
    ctx.webkitImageSmoothingEnabled = false;
    ctx.mozImageSmoothingEnabled = false;
    ctx.msImageSmoothingEnabled = false;
    
    // Dibujar el código escalado
    ctx.drawImage(canvas, 0, 0, canvas.width * scale, canvas.height * scale);
    
    return highResCanvas;
}

/**
 * Imprime código de barras en PDF con alta calidad
 */
async function imprimirPDF(numeroTarjeta, barcodeId) {
    try {
        // Generar código de barras para impresión
        const canvas = generarCodigoBarrasParaImpresion(numeroTarjeta);
        const imgData = canvas.toDataURL('image/png', 1.0); // Máxima calidad PNG
        
        // Crear PDF con jsPDF
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({
            orientation: 'landscape',
            unit: 'mm',
            format: [85.6, 53.98] // Tamaño tarjeta de crédito en mm
        });
        
        // Agregar el código de barras centrado con mejor calidad
        const imgWidth = 70; // Aumentado para mejor legibilidad
        const imgHeight = 25; // Proporción ajustada
        const x = (85.6 - imgWidth) / 2; // Centrar horizontalmente
        const y = (53.98 - imgHeight) / 2; // Centrar verticalmente
        
        doc.addImage(imgData, 'PNG', x, y, imgWidth, imgHeight, '', 'FAST'); // Compresión rápida
        
        // Descargar
        doc.save(`tarjeta_${numeroTarjeta}.pdf`);
        
        // Actualizar contador de impresiones
        await actualizarContadorImpresiones(numeroTarjeta);
        
    } catch (error) {
        console.error('Error generando PDF:', error);
        alert('Error al generar PDF');
    }
}

/**
 * Imprime código de barras en Word (usando HTML convertido)
 */
async function imprimirWord(numeroTarjeta, barcodeId) {
    try {
        // Generar código de barras para impresión
        const canvas = generarCodigoBarrasParaImpresion(numeroTarjeta);
        const imgData = canvas.toDataURL('image/png');
        
        // Crear contenido HTML que se puede abrir en Word
        const htmlContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Tarjeta ${numeroTarjeta}</title>
                <style>
                    @page {
                        size: 85.6mm 53.98mm;
                        margin: 5mm;
                    }
                    body {
                        margin: 0;
                        padding: 0;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        height: 43.98mm;
                        font-family: Arial, sans-serif;
                    }
                    .barcode-container {
                        text-align: center;
                    }
                    .barcode-image {
                        max-width: 60mm;
                        height: auto;
                    }
                </style>
            </head>
            <body>
                <div class="barcode-container">
                    <img src="${imgData}" alt="Código de barras ${numeroTarjeta}" class="barcode-image">
                </div>
            </body>
            </html>
        `;
        
        // Crear blob y descargar como archivo .doc (HTML que Word puede abrir)
        const blob = new Blob([htmlContent], { type: 'application/msword' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `tarjeta_${numeroTarjeta}.doc`;
        link.click();
        URL.revokeObjectURL(url);
        
        // Actualizar contador de impresiones
        await actualizarContadorImpresiones(numeroTarjeta);
        
    } catch (error) {
        console.error('Error generando Word:', error);
        alert('Error al generar documento Word');
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
        
        // Recargar la lista para mostrar el contador actualizado
        cargarCodigos();
    } catch (error) {
        console.error('Error actualizando contador:', error);
    }
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
        const barcodeId = `barcode-${index}`; // ID único para cada código de barras
        return ` 
            <tr> 
                <td>${codigo.idCliente}</td> 
                <td>${codigo.cedula ?? '-'}</td> 
                <td>${codigo.nombre ?? '-'}</td> 
                <td>${codigo.fechaRegistro ?? '-'}</td> 
                <td>
                    <div class="text-center">
                        <canvas id="${barcodeId}"></canvas>
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
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                onclick="imprimirWord('${codigo.idCliente}', '${barcodeId}')"
                                title="Descargar Word">
                            <i class="fas fa-file-word"></i> Word
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
                    <th class="text-center">CantImpresiones</th> 
                    <th class="text-center">Imprimir</th> 
                </tr> 
            </thead> 
            <tbody> 
                ${filas} 
            </tbody> 
        </table> 
    `; 

    // Generar códigos de barras después de insertar el HTML
    setTimeout(() => {
        codigos.forEach((codigo, index) => {
            const barcodeId = `barcode-${index}`;
            generarCodigoBarras(codigo.idCliente, barcodeId);
        });
    }, 100); // Pequeño delay para asegurar que el DOM esté listo
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
    // Filtro por idCliente (número de tarjeta) 
    const filtrados = codigosGlobal.filter(c => 
        String(c.idCliente).toLowerCase().includes(valor.toLowerCase()) 
    ); 
    mostrarCodigos(filtrados); 
}); 
 
/** 
 * Botón actualizar recarga la lista 
 */ 
document.getElementById('btnActualizar').addEventListener('click', cargarCodigos); 
 
// Carga inicial 
document.addEventListener("DOMContentLoaded", cargarCodigos);