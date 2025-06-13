
<?php
date_default_timezone_set('America/Costa_Rica');
$hora = date('H');
$saludo = ($hora < 12) ? 'Buenos días' : (($hora < 18) ? 'Buenas tardes' : 'Buenas noches');
$fecha = date("d/m/Y");
?>
<div class="dashboard">
    <h2><?= $saludo ?> 👋</h2>
    <p>Bienvenido al CRM de Clientes VIP del Restaurante Bastos.</p>
    <div id="reloj" style="font-size: 1.5rem; margin-top: 1rem;"></div>
    <p>Fecha: <?= $fecha ?></p>
</div>
