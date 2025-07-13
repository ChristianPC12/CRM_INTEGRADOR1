<div class="card-header bg-dark text-white rounded-top p-4">
    <!-- Título (línea 1) -->
    <div class="d-flex align-items-center mb-2"> <!-- <== AJUSTA ESTE mb-2 ó mb-1 -->
        <h5 class="mb-0 header-title">
            <i class="fa-solid fa-chart-bar"></i> Análisis de Clientes
        </h5>
        <div class="flex-grow-1 ms-3 divider-header"></div>
    </div>

    <!-- Fila 1 de botones -->
    <div class="d-flex flex-wrap gap-2 w-100 botones-analisis mb-2">
        <button class="btn btn-analisis active flex-fill" id="btnClientesFrecuentes">Clientes más frecuentes</button>
        <button class="btn btn-analisis flex-fill" id="btnClientesMayorHistorial">
            <i class="fa-solid fa-crown"></i> Clientes con mayor historial
        </button>
        <button class="btn btn-analisis flex-fill" id="btnClientesInactivos">Clientes inactivos</button>
        <button class="btn btn-analisis flex-fill" id="btnResidenciasFrecuentes">Residencias más frecuentes</button>
    </div>
    <!-- Fila 2 de botones + buscador -->
    <div class="d-flex flex-wrap gap-2 w-100 align-items-center">
        <button class="btn btn-analisis btn-outline-dark flex-fill" id="btnClientesAntiguos">
            Clientes más antiguos
        </button>
        <button class="btn btn-analisis btn-outline-dark flex-fill" id="btnVentasPorMes">Ventas por mes</button>
        <button class="btn btn-analisis flex-fill" id="btnVentasPorAnio">
            <i class="fa-solid fa-calendar-alt"></i> Ventas por año
        </button>
        <button class="btn btn-analisis btn-outline-white flex-fill" id="btnActualizarAnalisis">
            <i class="fa-solid fa-rotate"></i> Actualizar
        </button>
        <!-- Buscador: siempre alineado a la derecha -->
        <div class="input-group buscador-analisis ms-auto" style="min-width:220px;max-width:300px;">
            <span class="input-group-text bg-dark text-white border-0 rounded-start px-3" id="labelBuscador">
                <i class="fa fa-search"></i>
            </span>
            <input type="text" class="form-control form-control-sm border-0 rounded-end" placeholder="Buscar..."
                id="analisisBuscadorGeneral" autocomplete="off" aria-label="Buscar"
                style="background:#fff; color:#222; font-weight:500;">
        </div>
    </div>
</div>





<div class="card-body p-0 bg-white rounded-bottom">
    <div class="table-responsive px-4 pt-4 pb-2" id="analisisTablaCont">
        <!-- Aquí irá la tabla dinámica -->
    </div>
    <div class="px-4 pb-4" id="analisisGraficoCont" style="min-height:300px;">
        <!-- Aquí irá el gráfico dinámico -->
    </div>
</div>
</div>
<link rel="stylesheet" href="/CRM_INT/CRM/public/css/Analisis.css?v=<?= time() ?>">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/CRM_INT/CRM/public/js/Analisis.js?v=<?= time() ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>