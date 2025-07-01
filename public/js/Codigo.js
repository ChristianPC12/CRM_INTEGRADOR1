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