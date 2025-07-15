<!-- CUMPLEAÑOS DE LA SEMANA -->
<div class="card shadow mb-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white">
        <h5 class="mb-0">Cumpleaños de la Semana</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <div id="rangoSemana" class="alert alert-info text-center fw-bold m-4">
                <!-- Aquí se mostrará el rango de la semana -->
            </div>

            <div id="cumpleLista" class="p-4">
                <!-- Aquí se inyectará la tabla desde JS -->
            </div>

            <!-- RECORDATORIO DE LLAMADAS -->
            <div id="recordatorioLlamadas" class="mt-2 mx-4 mb-4 d-none">
                <div class="alert alert-warning shadow-sm" role="alert">
                    <h6 class="mb-2 fw-bold">
                        <i class="fa-solid fa-bell text-danger"></i> Clientes sin correo (llamar por teléfono):
                    </h6>
                    <ul id="listaRecordatorios" class="mb-0 ps-3 small"></ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FORMULARIO DE ENVÍO DE CORREO -->
<div class="card shadow-sm mt-3" style="max-width: 1100px; margin: 0 auto; border-left: 5px solid #f9c41f;">
    <div class="card-header bg-black text-warning py-2">
        <h6 class="mb-0"><i class="fas fa-envelope"></i> Enviar correo de felicitación</h6>
    </div>
    <div class="card-body p-3">
        <form id="formCorreo" class="row g-2">
            <input type="hidden" id="idCumple">

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

            <input type="hidden" id="mensajeCorreo">
            <input type="hidden" id="fechaCumple">

            <div class="col-12 text-end mt-2">
                <button id="btnEnviarCorreo" type="submit" class="btn btn-sm btn-warning text-black">
                    <i class="fas fa-paper-plane"></i> Enviar correo
                </button>
            </div>
        </form>
    </div>
</div>

<!-- HISTORIAL DE ENVÍOS -->
<div class="card shadow-sm mt-3" style="max-width: 1100px; margin: 0 auto;">
    <div class="card-header bg-secondary text-white py-2">
        <h6 class="mb-0"><i class="fas fa-history"></i> Historial de cumpleaños atendidos</h6>
    </div>
    <div class="card-body p-3">
        <div class="table-responsive">
            <div id="historialCumples" class="p-2">
                <!-- Aquí se inyectará el historial desde JS -->
            </div>
        </div>
    </div>
</div>

<!-- SCRIPTS NECESARIOS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/CRM_INT/CRM/public/js/Cumple.js"></script>
