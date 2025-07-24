<div class="card shadow mb-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white">
        <h5 class="mb-0">Lista de Tarjetas</h5>
        <div class="d-flex gap-2">
            <input type="text" id="buscarCodigo" class="form-control form-control-sm w-auto"
                placeholder="Buscar por # tarjeta">
            <button id="btnBuscarTarjeta" class="btn btn-sm btn-warning" title="Ir a Beneficios">
                <i class="bi bi-search"></i> Buscar
            </button>
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

<script src="/CRM_INT/CRM/public/js/Codigo.js?v=<?= time() ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">