<!-- ARCHIVO: ClienteView.php -->
<div class="container mt-4">
    <h2 class="text-center mb-4">Gestión de Clientes</h2>

    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white">Registro de Cliente</div>
        <div class="card-body">
            <form id="clienteForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id" class="form-label">ID</label>
                        <input type="text" name="id" id="id" class="form-control" placeholder="Autogenerado" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label">Nombre Completo *</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" required
                            placeholder="Ej: Juan Pérez">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="correo" class="form-label">Correo Electrónico *</label>
                        <input type="email" name="correo" id="correo" class="form-control" required
                            placeholder="ejemplo@correo.com">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="telefono" class="form-label">Teléfono *</label>
                        <input type="tel" name="telefono" id="telefono" class="form-control" required
                            placeholder="0000-0000">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="lugarResidencia" class="form-label">Lugar de Residencia *</label>
                        <input type="text" name="lugarResidencia" id="lugarResidencia" class="form-control" required
                            placeholder="Ej: San José, Costa Rica">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fechaCumpleanos" class="form-label">Fecha de Cumpleaños *</label>
                        <input type="date" name="fechaCumpleanos" id="fechaCumpleanos" class="form-control" required>
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

    <div class="card shadow">
        <div class="card-header bg-dark text-white d-flex justify-content-between">
            <h5 class="mb-0">Lista de Clientes</h5>
            <button class="btn btn-sm btn-outline-light" onclick="cargarClientes()">Actualizar</button>
        </div>
        <div class="card-body p-0">
            <div id="clienteLista" class="p-4 text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2">Cargando clientes...</p>
            </div>
        </div>
    </div>
</div>