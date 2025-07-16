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
<script>
document.addEventListener("DOMContentLoaded", () => {
  const cantonesCR = [
  "LIBERIA, GUANACASTE",
  "NICOYA, GUANACASTE",
  "SANTA CRUZ, GUANACASTE",
  "BAGACES, GUANACASTE",
  "CARRILLO, GUANACASTE",
  "CAÑAS, GUANACASTE",
  "ABANGARES, GUANACASTE",
  "TILARÁN, GUANACASTE",
  "NANDAYURE, GUANACASTE",
  "LA CRUZ, GUANACASTE",
  "HOJANCHA, GUANACASTE",
  "PUNTARENAS, PUNTARENAS",
  "ESPARZA, PUNTARENAS",
  "BUENOS AIRES, PUNTARENAS",
  "MONTES DE ORO, PUNTARENAS",
  "OSA, PUNTARENAS",
  "AGUIRRE, PUNTARENAS",
  "GOLFITO, PUNTARENAS",
  "COTO BRUS, PUNTARENAS",
  "PARAÍSO, PUNTARENAS",
  "CORREDORES, PUNTARENAS",
  "GARABITO, PUNTARENAS",
  "SAN JOSÉ, SAN JOSÉ",
  "ESCAZÚ, SAN JOSÉ",
  "DESAMPARADOS, SAN JOSÉ",
  "PURISCAL, SAN JOSÉ",
  "TARAZU, SAN JOSÉ",
  "ASERRÍ, SAN JOSÉ",
  "MORA, SAN JOSÉ",
  "GOICOECHEA, SAN JOSÉ",
  "SANTA ANA, SAN JOSÉ",
  "ALAJUELITA, SAN JOSÉ",
  "VÁSQUEZ DE CORONADO, SAN JOSÉ",
  "ACOSTA, SAN JOSÉ",
  "TIBÁS, SAN JOSÉ",
  "MORAVIA, SAN JOSÉ",
  "MONTES DE OCA, SAN JOSÉ",
  "TURRUBARES, SAN JOSÉ",
  "DOTA, SAN JOSÉ",
  "CURRIDABAT, SAN JOSÉ",
  "PÉREZ ZELEDÓN, SAN JOSÉ",
  "LEÓN CORTÉS, SAN JOSÉ",
  "ALAJUELA, ALAJUELA",
  "SAN RAMÓN, ALAJUELA",
  "GRECIA, ALAJUELA",
  "SAN MATEO, ALAJUELA",
  "ATENAS, ALAJUELA",
  "NARANJO, ALAJUELA",
  "PALMARES, ALAJUELA",
  "POÁS, ALAJUELA",
  "OROTINA, ALAJUELA",
  "SAN CARLOS, ALAJUELA",
  "ZARCERO, ALAJUELA",
  "VALVERDE VEGA, ALAJUELA",
  "UPALA, ALAJUELA",
  "LOS CHILES, ALAJUELA",
  "GUATUSO, ALAJUELA",
  "RIO CUARTO, ALAJUELA",
  "HEREDIA, HEREDIA",
  "BARVA, HEREDIA",
  "SANTO DOMINGO, HEREDIA",
  "SANTA BÁRBARA, HEREDIA",
  "SAN RAFAEL, HEREDIA",
  "SAN ISIDRO, HEREDIA",
  "BELÉN, HEREDIA",
  "FLORES, HEREDIA",
  "SARAPIQUÍ, HEREDIA",
  "CARTAGO, CARTAGO",
  "PARAÍSO, CARTAGO",
  "LA UNIÓN, CARTAGO",
  "JIMÉNEZ, CARTAGO",
  "TURRIALBA, CARTAGO",
  "ALVARADO, CARTAGO",
  "OREAMUNO, CARTAGO",
  "EL GUARCO, CARTAGO",
  "LIMÓN, LIMÓN",
  "POCOCÍ, LIMÓN",
  "SIQUIRRES, LIMÓN",
  "TALAMANCA, LIMÓN",
  "MATINA, LIMÓN",
  "GUÁCIMO, LIMÓN"
];

  const listaCantones = document.getElementById("listaCantones");
  cantonesCR.forEach(canton => {
    const option = document.createElement("option");
    option.value = canton;
    listaCantones.appendChild(option);
  });
});
</script>


    <!-- Script exclusivo de esta vista -->
    <script src="/CRM_INT/CRM/public/js/Cliente.js?v=<?= time() ?>"></script>