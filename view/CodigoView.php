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

<!-- Modal de Cumpleaños -->
<div class="modal fade" id="modalCumpleanos" tabindex="-1" aria-labelledby="modalCumpleanosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: 3px solid #f9c41f; border-radius: 15px;">
            <div class="modal-header"
                style="background: linear-gradient(135deg, #f9c41f 0%, #e6b619 100%); color: #000;">
                <h5 class="modal-title fw-bold" id="modalCumpleanosLabel">
                    🎉 ¡CUMPLEAÑOS ESPECIAL! 🎂
                </h5>
            </div>
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-birthday-cake" style="font-size: 3rem; color: #f9c41f;"></i>
                </div>
                <h4 class="fw-bold mb-3" id="nombreCumpleanero" style="color: #000;"></h4>
                <p class="lead mb-4" style="color: #555;">
                    ¡Está celebrando su cumpleaños esta semana! 🎊
                </p>
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-gift me-2"></i>
                    <strong>¡Recordale que puede reclamar su regalo especial!</strong>
                </div>
            </div>
            <div class="modal-footer justify-content-center" style="border-top: 2px solid #f9c41f;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
                <button type="button" class="btn btn-primary" id="btnIrCumpleanos"
                    style="background: #f9c41f; border-color: #f9c41f; color: #000; font-weight: bold;">
                    <i class="fas fa-birthday-cake"></i> Ir a Cumpleaños
                </button>
            </div>
        </div>
    </div>
</div>

<script src="/CRM_INT/CRM/public/js/Codigo.js?v=<?= time() ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">