<!-- Líneas decorativas -->
<div class="decor-line" style="top: 120px; left: 40%;"></div>
<div class="decor-line" style="top: 250px; left: 20%; width: 80px;"></div>

<div class="container mt-4 mover-derecha">
    <h2 class="text-center mb-4">Gestión de Clientes</h2>

    <!-- Formulario -->
    <div class="card shadow mb-4">
<div class="card-header clientes-header d-flex align-items-center">
    <h5 class="mb-0 flex-grow-1 titulo-clientes">Registro de Cliente</h5>
</div>
        <div class="card-body">
            <form id="clienteForm">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="clienteId" class="form-label">ID</label>
                        <input type="text" name="id" id="id" class="form-control" placeholder="Autogenerado" readonly>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="clienteCedula" class="form-label">Cédula *</label>
                        <input type="text" name="cedula" id="clienteCedula" class="form-control" required
                            placeholder="Ej: 123456789"
                            maxlength="14" pattern="\d{9}|\d{14}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="clienteNombre" class="form-label">Nombre Completo *</label>
                        <input type="text" name="nombre" id="clienteNombre" class="form-control" required maxlength="45"
                            placeholder="Ej: JUAN PÉREZ GONZÁLEZ">

                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="clienteCorreo" class="form-label">Correo Electrónico *</label>
                        <input type="email" name="correo" id="clienteCorreo" class="form-control"
                            placeholder="ejemplo@correo.com">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="clienteTelefono" class="form-label">Teléfono *</label>
                        <input type="tel" name="telefono" id="clienteTelefono" class="form-control" required
                            placeholder="0000-0000">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="clienteLugar" class="form-label">Lugar de Residencia *</label>
                        <input list="listaCantones" name="lugarResidencia" id="clienteLugar" class="form-control" required
                        placeholder="Ej: San José, Costa Rica">
                        <datalist id="listaCantones"></datalist>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="clienteFecha" class="form-label">Fecha de Cumpleaños *</label>
                        <input type="date" name="fechaCumpleanos" id="clienteFecha" class="form-control" required>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="button" id="cancelBtn" class="btn btn-secondary me-2"
                        onclick="cancelarEdicion()">Cancelar</button>
                    <button type="submit" id="submitBtn" class="btn btn-primary">Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de clientes -->
    <div class="card shadow mb-4">
        <div class="card-header clientes-header d-flex align-items-center gap-3 flex-wrap">
            <h5 class="mb-0 flex-grow-1 titulo-clientes">Lista de Clientes</h5>
            <!-- Buscador en tiempo real -->
            <input type="text" id="buscadorClientes" class="form-control buscador-clientes"
                placeholder="Buscar por n° de tarjeta o nombre..." autocomplete="off">
            <button class="btn btn-sm btn-outline-light ms-2" onclick="cargarClientes()">
                Actualizar
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div id="clienteLista" class="p-4 text-center">
                    <!-- Aquí se inyectará la tabla -->
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Reasignaciones -->
    <div class="card shadow mb-4">
        <div class="card-header clientes-header d-flex align-items-center gap-3 flex-wrap">
            <h5 class="mb-0 flex-grow-1 titulo-clientes">
                <i class="fas fa-history me-2"></i>Historial de Reasignaciones
            </h5>
            <small class="text-light me-2">
                <i class="fas fa-sync-alt me-1"></i>
                <span id="estadoActualizacion">Actualización automática activa</span>
            </small>
            <button class="btn btn-sm btn-outline-light ms-2" onclick="cargarHistorialReasignaciones(true)" title="Actualizar manualmente">
                <i class="fas fa-refresh me-1"></i>Actualizar
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div id="historialReasignaciones" class="p-4 text-center">
                    <!-- Aquí se inyectará la tabla del historial -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para reasignación de código -->
    <div class="modal fade" id="modalReasignar" tabindex="-1" aria-labelledby="modalReasignarLabel" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header reasignar-header">
                    <h6 class="modal-title" id="modalReasignarLabel">
                        <i class="fas fa-sync-alt me-2"></i>Reasignar Código
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="cliente-info-card-compact">
                        <div class="text-center mb-3">
                            <h6 class="mb-2"><i class="fas fa-user me-2"></i><span id="modalClienteNombre">-</span></h6>
                            <div class="cliente-datos-inline">
                                <span class="badge bg-secondary me-2">ID: <span id="modalClienteId">-</span></span>
                                <span class="badge bg-secondary">Cédula: <span id="modalClienteCedula">-</span></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <label for="motivoReasignacion" class="form-label">
                            <strong><i class="fas fa-edit me-1"></i>Motivo:</strong> *
                        </label>
                        <select class="form-select mb-2" id="motivoSelect" onchange="manejarSeleccionMotivo()">
                            <option value="">Seleccionar motivo...</option>
                            <option value="Tarjeta dañada">Tarjeta dañada</option>
                            <option value="Tarjeta perdida">Tarjeta perdida</option>
                            <option value="Tarjeta robada">Tarjeta robada</option>
                            <option value="Tarjeta deteriorada">Tarjeta deteriorada</option>
                            <option value="Reemplazo por desgaste">Reemplazo por desgaste</option>
                            <option value="Solicitud del cliente">Solicitud del cliente</option>
                            <option value="Otro">Otro (especificar)</option>
                        </select>
                        <textarea 
                            class="form-control motivo-textarea-compact" 
                            id="motivoReasignacion" 
                            rows="2" 
                            placeholder="Descripción del motivo..."
                            maxlength="100"
                            style="display: none;"
                            required>
                        </textarea>
                        <div class="form-text">Máximo 100 caracteres</div>
                    </div>
                    
                    <div class="alert alert-info alert-compact mt-3" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <small><strong>Proceso:</strong> Se generará un nuevo código VIP automáticamente.</small>
                        <br>
                        <small><i class="fas fa-keyboard me-1"></i><em>Tip: Puedes cerrar este modal presionando ESC o usando el botón X</em></small>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-warning btn-sm" id="btnConfirmarReasignacion" onclick="procesarReasignacion()">
                        <i class="fas fa-sync-alt me-1"></i>Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>



    <!-- Script exclusivo de esta vista -->
    <script src="/CRM_INT/CRM/public/js/Cliente.js?v=<?= time() ?>"></script>