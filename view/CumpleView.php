<!-- CUMPLEAÑOS DE LA SEMANA -->
<div class="card shadow mb-4">
    <!-- Encabezado con título y botones para cambiar de semana -->
    <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white">
        <h5 class="mb-0">Cumpleaños de la Semana</h5>
        <div class="d-flex align-items-center gap-2">
            <!-- Botón semana anterior -->
            <button id="btnPrevSemana" class="btn-nav-week" title="Semana actual" disabled>&lsaquo;</button>
            <!-- Texto con el rango de la semana -->
            <span id="lblSemana" class="badge bg-warning text-dark">SEMANA ACTUAL</span>
            <!-- Botón semana siguiente -->
            <button id="btnNextSemana" class="btn-nav-week" title="Semana siguiente">&rsaquo;</button>
        </div>
        
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <!-- Aquí se mostrará el rango de fechas de la semana -->
            <div id="rangoSemana" class="alert alert-info text-center fw-bold m-4"></div>

            <!-- Aquí JS inyectará la tabla de clientes con cumpleaños -->
            <div id="cumpleLista" class="p-4"></div>

<<<<<<< Updated upstream
            <!-- RECORDATORIO DE LLAMADAS -->
            <div id="recordatorioLlamadas" class="mt-2 mx-4 mb-4 d-none" hidden>
                   
                    <ul id="listaRecordatorios" class="mb-0 ps-3 small" hidden></ul>
=======
            <!-- Recordatorio de clientes sin correo para llamar -->
            <div id="recordatorioLlamadas" class="mt-2 mx-4 mb-4 d-none">
                <div class="alert alert-warning shadow-sm" role="alert">
                    <h6 class="mb-2 fw-bold">
                        <i class="fa-solid fa-bell text-danger"></i> Clientes sin correo (llamar por teléfono):
                    </h6>
                    <!-- Lista de recordatorios generada con JS -->
                    <ul id="listaRecordatorios" class="mb-0 ps-3 small"></ul>
                </div>
>>>>>>> Stashed changes
            </div>
        </div>
    </div>
</div>

<!-- FORMULARIO DE ENVÍO DE CORREO -->
<div class="card shadow-sm mt-3" style="border-left: 5px solid #f9c41f;">
    <div class="card-header bg-black text-warning py-2">
        <h6 class="mb-0"><i class="fas fa-envelope"></i> Enviar correo de felicitación</h6>
    </div>
    <div class="card-body p-3">
        <form id="formCorreo" class="row g-2">
            <!-- ID oculto del cumpleañero -->
            <input type="hidden" id="idCumple">

            <!-- Datos del cliente que cumple años -->
            <div class="col-12 col-md-6">
                <label class="form-label small">Nombre</label>
                <input type="text" id="nombreCorreo" class="form-control form-control-sm" readonly>
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small">Cédula</label>
                <input type="text" id="cedulaCorreo" class="form-control form-control-sm" readonly>
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small">Teléfono</label>
                <input type="text" id="telefonoCorreo" class="form-control form-control-sm" readonly>
            </div>

            <div class="col-12">
                <label class="form-label small">Correo</label>
                <input type="email" id="correoCorreo" class="form-control form-control-sm" readonly>
            </div>

            <!-- Campos ocultos para mensaje y fecha -->
            <input type="hidden" id="mensajeCorreo">
            <input type="hidden" id="fechaCumple">

            <!-- Botones para enviar WhatsApp o Correo -->
            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                <button id="btnEnviarWhats" type="button" class="btn btn-sm btn-success">
                    <i class="bi bi-whatsapp"></i> Enviar WhatsApp
                </button>
                <button id="btnEnviarCorreo" type="submit" class="btn btn-sm btn-warning text-black">
                    <i class="fas fa-paper-plane"></i> Enviar correo
                </button>
            </div>
        </form>
    </div>
</div>

<!-- HISTORIAL DE ENVÍOS -->
<div class="card shadow-sm mt-3">
    <div class="card-header bg-secondary text-white py-2">
        <h6 class="mb-0"><i class="fas fa-history"></i> Historial de cumpleaños atendidos</h6>
    </div>
    <div class="card-body p-3">
        <div class="table-responsive">
            <!-- Botones para exportar PDF o imprimir historial -->
            <div class="d-flex justify-content-end align-items-center gap-2 mb-2 me-2">
                <button id="btnExportPDF" class="btn border border-danger text-danger">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </button>
                <button id="btnImprimir" class="btn border border-success text-success">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
            </div>

            <!-- Aquí JS mostrará el historial de cumpleaños ya gestionados -->
            <div id="historialCumples" class="p-2"></div>
        </div>
    </div>
</div>

<!-- SCRIPTS NECESARIOS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Librerías para generar PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>