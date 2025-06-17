<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes</title>
    <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Cliente.css?v=<?= time() ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <!-- Líneas decorativas -->
    <div class="decor-line" style="top: 120px; left: 40%;"></div>
    <div class="decor-line" style="top: 250px; left: 20%; width: 80px;"></div>

    <div class="container mt-4">
        <h2 class="text-center mb-4">Gestión de Clientes</h2>

        <!-- Formulario -->
        <div class="card shadow mb-4">
            <div class="card-header">Registro de Cliente</div>
            <div class="card-body">
                <form id="clienteForm">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="clienteId" class="form-label">ID</label>
                            <input type="text" name="id" id="id" class="form-control" placeholder="Autogenerado"
                                readonly>
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
                            <input type="email" name="correo" id="clienteCorreo" class="form-control" required
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
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between">
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

    <script src="/CRM_INT/CRM/public/js/Cliente.js?v=<?= time() ?>"></script>
</body>

</html>