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
                    <div class="compra-titulo">Elije una opción</div>
                    <button class="compra-btn-opcion" id="compraOpcionCompra">Compra</button>
                    <button class="compra-btn-opcion" id="compraOpcionDescuento">Descuento</button>
                    <input type="text" class="compra-input-id" id="compraInputId" placeholder="Número de tarjeta">
                    <button class="compra-btn-buscar" id="compraBtnBuscar">Buscar</button>
                </div>
                <div class="compra-indicacion" id="compraIndicacion">
                    <i class="bi bi-info-circle"></i>
                    Selecciona una opción para aplicar una compra o descuento al cliente.
                </div>
            </div>
        </div>

        <div class="compra-barra-derecha"></div>
    </div>
</body>

</html>