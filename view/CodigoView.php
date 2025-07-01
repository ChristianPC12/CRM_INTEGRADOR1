<div class="card shadow mb-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white">
        <h5 class="mb-0">Lista de Tarjetas</h5>
        <div class="d-flex gap-2">
            <input type="text" id="buscarCodigo" class="form-control form-control-sm w-auto"
                   placeholder="Buscar por # tarjeta">
            <button id="btnActualizar" class="btn btn-sm btn-outline-light">
                Actualizar
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <div id="codigoLista" class="p-4">
                <!-- Aquí se inyectará la tabla -->
            </div>
        </div>
    </div>
</div>
<!-- Scripts existentes -->
<script src="/CRM_INT/CRM/public/js/Codigo.js?v=<?= time() ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<!-- Nuevas librerías para generar PDF y Word -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<!-- Alternativa más compatible para Word -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

<!-- Font Awesome para los iconos (si no lo tienes ya) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">