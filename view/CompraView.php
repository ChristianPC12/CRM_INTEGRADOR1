<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CompraView</title>
    <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Compra.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>

<body>
    <div class="compra-layout">
        <div class="compra-barra-amarilla"></div>
        <div class="template-container">

            <div class="compra-bloque-inicio">
                <div class="compra-header-linea">
                    <div id="compraMensaje" class="mensaje-error"></div>
                    <div class="compra-titulo">Elije una opción</div>
                    <button class="compra-btn-opcion" id="compraOpcionCompra">Compra</button>
                    <button class="compra-btn-opcion" id="compraOpcionDescuento">Descuento</button>
                    <div class="compra-input-container">
                        <input type="text" class="compra-input-id" id="compraInputId" placeholder="Número de tarjeta">
                        <!-- Ícono de búsqueda que aparece solo cuando se está en modo "Acumular" -->
                        <button class="compra-btn-buscar-icon" id="compraBtnBuscarIcon" style="display: none;"
                            title="Buscar otra tarjeta">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <button class="compra-btn-buscar" id="compraBtnBuscar">Buscar</button>
                </div>
                <div class="compra-indicacion" id="compraIndicacion">
                    <i class="bi bi-info-circle"></i>
                    Selecciona una opción para aplicar una compra o descuento al cliente.
                </div>
                <!-- CARD/FORMULARIO SOLO SE MUESTRA SI HAY DATOS -->
                <div class="card shadow mb-4" id="compraCardForm" style="display:none;">
                    <div class="card-body">
                        <form id="compraForm">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">ID</label>
                                    <input type="text" id="id" class="form-control" readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Cédula</label>
                                    <input type="text" id="clienteCedula" class="form-control" readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nombre Completo</label>
                                    <input type="text" id="clienteNombre" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Correo</label>
                                    <input type="email" id="clienteCorreo" class="form-control" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Teléfono</label>
                                    <input type="tel" id="clienteTelefono" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Lugar de Residencia</label>
                                    <input type="text" id="clienteLugar" class="form-control" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha de Cumpleaños</label>
                                    <input type="date" id="clienteFecha" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Acumular Compra</label>
                                    <input type="text" id="cantidadAcumulada" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Total Actual</label>
                                    <input type="number" id="totalActual" class="form-control" readonly>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- HISTORIAL DE COMPRAS DEL CLIENTE -->
                <div id="historialCompras" style="margin-top: -1.5em; width:100% ; 
                    height: 100px; min-height: 100px; 
                    overflow-y: auto; overflow-x: auto; 
                    scrollbar-width: none; 
                    -ms-overflow-style: none;">
                </div>
            </div>
        </div>
        <div class="compra-barra-derecha"></div>
    </div>
    <script src="/CRM_INT/CRM/public/js/Compra.js"></script>
</body>

</html>