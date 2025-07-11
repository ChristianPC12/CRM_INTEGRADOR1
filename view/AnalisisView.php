<!-- view/AnalisisView.php -->
<div class="card shadow mb-4" style="max-width:var(--card-max-w);margin:auto;">
    <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white rounded-top">
        <h5 class="mb-0"><i class="fa-solid fa-chart-bar"></i> Análisis de Clientes</h5>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-analisis active" id="btnClientesFrecuentes">Clientes más frecuentes</button>
<button class="btn btn-analisis" id="btnClientesMayorHistorial">
    <i class="fa-solid fa-crown"></i> Clientes con mayor historial
</button>
<button class="btn btn-analisis" id="btnClientesInactivos">Clientes inactivos</button>

            <!-- Aquí podrás agregar más botones de análisis en el futuro -->
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
