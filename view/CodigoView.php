<!-- Líneas decorativas -->
<div class="decor-line" style="top: 120px; left: 40%;"></div>
<div class="decor-line" style="top: 250px; left: 20%; width: 80px;"></div>

<!-- Título y buscador -->
<div class="container my-4">
    <div class="card shadow border-start border-5 border-warning mb-4">
        <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white">
            <h5 class="mb-0">Lista de Tarjetas</h5>
            <button id="btnActualizar" class="btn btn-sm btn-outline-light">
  Actualizar
</button>

            <input type="text" id="buscarCodigo" class="form-control form-control-sm w-auto"
                placeholder="Buscar por # tarjeta">
        </div>
        <div class="card-body p-4">
            <div id="codigoLista" class="table-responsive">
                <!-- Aquí se inyectará la tabla de códigos o el mensaje si está vacía -->
            </div>
        </div>
    </div>

</div>

<!-- Script específico -->
<script src="/CRM_INT/CRM/public/js/Codigo.js?v=<?= time() ?>"></script>