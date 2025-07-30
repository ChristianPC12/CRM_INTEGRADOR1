<!-- Líneas decorativas -->
<div class="decor-line" style="top: 120px; left: 40%;"></div>
<div class="decor-line" style="top: 250px; left: 20%; width: 80px;"></div>

<div class="container mt-4 mover-derecha">

    <!-- Formulario de registro/edición de usuario -->
    <div class="card shadow mb-4">
        <div class="card-header usuarios-header d-flex align-items-center">
            <h5 class="mb-0 flex-grow-1 titulo-usuarios">Registro de Usuario</h5>
        </div>
        <div class="card-body">
            <form id="usuarioForm">
                <input type="hidden" id="usuarioId" value="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="usuario" class="form-label">Usuario *</label>
                        <input type="text" class="form-control" id="usuario" name="usuario"
                            placeholder="Por favor introduzca un nombre" required maxlength="16">

                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="contrasena" class="form-label">Contraseña *</label>
                        <input type="password" class="form-control" id="contrasena" name="contrasena" required
                            maxlength="16">
                        <div class="form-text">
                            La contraseña debe tener entre 6 y 16 caracteres, al menos 1 letra mayúscula, 1 número y 1
                            carácter especial (ej: !, @, #, $).
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="rol" class="form-label">Rol *</label>
                        <select class="form-select" id="rol" name="rol" required>
                            <option value="">Seleccione un rol</option>
                            <option value="Administrador">ADMINISTRADOR</option>
                            <option value="Salonero">SALONERO</option>
                            <option value="Propietario">PROPIETARIO</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="privilegios" class="form-label">Privilegios (descripción)</label>
                        <textarea class="form-control" id="privilegios" name="privilegios" rows="3" readonly></textarea>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="reset" id="cancelBtn" class="btn btn-secondary me-2">Cancelar</button>
                    <button type="submit" id="submitBtn" class="btn btn-primary">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de usuarios -->
    <div class="card shadow mb-4">
        <div class="card-header usuarios-header d-flex align-items-center gap-3 flex-wrap">
            <h5 class="mb-0 flex-grow-1 titulo-usuarios">Lista de Usuarios</h5>
            <!-- Buscador en tiempo real -->
            <input type="text" id="buscadorUsuarios" class="form-control buscador-usuarios"
                placeholder="Buscar por nombre de usuario..." autocomplete="off">
            <button class="btn btn-sm btn-outline-light ms-2" onclick="cargarUsuarios()">
                Actualizar
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div id="usuarioLista" class="p-4 text-center">
                    <!-- Aquí se inyectará la tabla -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script específico -->
<script src="/CRM_INT/CRM/public/js/Usuario.js?v=<?= time() ?>"></script>