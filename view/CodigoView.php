<!-- Tarjeta que contiene la lista de tarjetas -->
<div class="card shadow mb-4">
    
    <!-- Encabezado con título y acciones -->
    <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white">
        <h5 class="mb-0">Lista de Tarjetas</h5>
        <div class="d-flex gap-2">
               <!-- Campo de texto para buscar por número de tarjeta o nombre -->
            <input type="text" id="buscarCodigo" class="form-control form-control-sm w-auto"
                placeholder="N° tarjeta o Nombre">

                <!-- Botón para ejecutar la búsqueda -->
            <button id="btnBuscarTarjeta" class="btn btn-sm btn-warning" title="Ir a Beneficios">
                <i class="bi bi-search"></i> Buscar
            </button>

            
            <!-- Botón para recargar/actualizar la lista -->
            <button id="btnActualizar" class="btn btn-sm btn-outline-light">
                Actualizar
            </button>
        </div>
    </div>

    
    <!-- Cuerpo donde se mostrará la tabla -->
    <div class="card-body p-0">
        <div class="table-responsive">
            <div id="codigoLista" class="p-4">
                     <!-- Aquí el JS dibuja la tabla de tarjetas -->
            </div>
        </div>
    </div>
</div>

<!-- Script propio que maneja la lógica de tarjetas -->
<script src="/CRM_INT/CRM/public/js/Codigo.js?v=<?= time() ?>"></script>

<!-- Librería para generar códigos de barras -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<!-- Librería para exportar a PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<!-- Librería para mostrar animaciones de confeti -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<!-- Iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">