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
                            placeholder="Ej: 123456789">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="clienteNombre" class="form-label">Nombre Completo *</label>
                        <input type="text" name="nombre" id="clienteNombre" class="form-control" required
                            placeholder="Ej: Juan Pérez">
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
                        <input type="text" name="lugarResidencia" id="clienteLugar" class="form-control" required
                            placeholder="Ej: San José, Costa Rica">
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


    <!-- Script exclusivo de esta vista -->
    <script src="/CRM_INT/CRM/public/js/Cliente.js?v=<?= time() ?>"></script>