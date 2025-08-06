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
                    <button class="compra-btn-opcion" id="compraOpcionExpress" style="display:none;">Express</button>

                    <div class="compra-input-container">
                        <input type="number" class="compra-input-id" id="compraInputId" placeholder="# de Tarjeta">
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
                    -ms-overflow-style: none;
                    border-radius: 0.5em;">
                </div>
            </div>
        </div>
        <div class="compra-barra-derecha"></div>
    </div>

    <!-- Modal de Cumpleaños -->
    <div class="modal fade" id="modalCumpleanos" tabindex="-1" aria-labelledby="modalCumpleanosLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border: 3px solid #f9c41f; border-radius: 15px;">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #f9c41f 0%, #e6b619 100%); color: #000;">
                    <h5 class="modal-title fw-bold" id="modalCumpleanosLabel">
                        🎉 ¡CUMPLEAÑOS ESPECIAL! 🎂
                    </h5>
                </div>
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-birthday-cake" style="font-size: 3rem; color: #f9c41f;"></i>
                    </div>
                    <h4 class="fw-bold mb-3" id="nombreCumpleanero" style="color: #000;"></h4>
                    <p class="lead mb-4" style="color: #555;">
                        ¡Está celebrando su cumpleaños esta semana! 🎊
                    </p>
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-gift me-2"></i>
                        <strong>¡Recordale que puede reclamar su regalo especial!</strong>
                    </div>
                </div>
                <div class="modal-footer justify-content-center" style="border-top: 2px solid #f9c41f;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                    <button type="button" class="btn btn-primary" id="btnIrCumpleanos"
                        style="background: #f9c41f; border-color: #f9c41f; color: #000; font-weight: bold;">
                        <i class="fas fa-birthday-cake"></i> Ir a Cumpleaños
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ⬇️ Modal Express – colócalo después del modal de cumpleaños -->
    <div class="modal fade" id="modalExpress" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered">
            <div class="modal-content" style="border:3px solid var(--amarillo);border-radius:15px;">
                <div class="modal-header"
                    style="background:linear-gradient(135deg,#f9c41f 0%,#e6b619 100%);color:#000;">
                    <h5 class="modal-title fw-bold">🚀 EXPRESS</h5>
                </div>

                <div class="modal-body text-center p-4">
                    <p class="lead mb-3">Estás por usar el apartado <strong>Express</strong></p>
                    <p><strong>Cliente:</strong> <span id="expressNombre"></span></p>
                    <p><strong>Correo:</strong> <span id="expressCorreo"></span></p>
                </div>

                <div class="modal-footer justify-content-center" style="border-top:2px solid var(--amarillo);">
                    <button type="button" class="btn btn-primary" id="btnEnviarCorreoExpress"
                        style="background:var(--amarillo);border-color:var(--amarillo);color:#000;font-weight:bold;">
                        <i class="bi bi-envelope-fill"></i> Enviar correo
                    </button>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script src="/CRM_INT/CRM/public/js/Compra.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</body>

</html>