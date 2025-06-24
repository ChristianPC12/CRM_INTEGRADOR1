 <h2 class="text-center mb-4">Gestión de Usuarios</h2>

    <!-- Formulario de registro de usuario -->
    <div class="card shadow mb-4">
        <div class="card-header">Registrar Usuario</div>
        <div class="card-body">
            <form id="usuarioForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="usuario" class="form-label">Usuario *</label>
                        <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Por favor introduzca un nombre" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="contrasena" class="form-label">Contraseña *</label>
                        <div class="position-relative">
                            <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                            <button type="button" id="togglePassword" class="toggle-password-btn">
                                <i class="bi bi-eye" id="passwordIcon"></i>
                            </button>
                        </div>
                        <div class="form-text">
                            Mínimo 6 caracteres, al menos 1 letra y 1 número.
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="rol" class="form-label">Rol *</label>
                        <select class="form-select" id="rol" name="rol" required>
                            <option value="">Seleccione un rol</option>
                            <option value="Administrador">Administrador</option>
                            <option value="Salonero">Salonero</option>
                            <option value="Propietario">Propietario</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="privilegios" class="form-label">Privilegios (descripción)</label>
                        <textarea class="form-control" id="privilegios" name="privilegios" rows="3"
                                  placeholder="Ej: Puede agregar, editar y ver clientes. No puede eliminar usuarios."></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="reset" class="btn btn-secondary me-2">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de usuarios -->
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Lista de Usuarios</h5>
            <button class="btn btn-sm btn-outline-primary" onclick="cargarUsuarios()">Actualizar</button>
        </div>
        <div class="card-body p-0">
            <div id="usuarioLista" class="table-responsive p-3">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Cargando usuarios...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script específico -->
<script src="/CRM_INT/CRM/public/js/Usuario.js?v=<?= time() ?>"></script>
