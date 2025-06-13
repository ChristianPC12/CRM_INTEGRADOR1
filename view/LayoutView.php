<?php
$vista = $_GET['view'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CRM Bastos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Layout.css">
</head>
<body>
    <aside class="sidebar">
        <h3><i class="bi bi-grid"></i> CRM Bastos</h3>
        <ul>
            <li><a href="LayoutView.php?view=dashboard"><i class="bi bi-house"></i> Dashboard</a></li>
            <li><a href="LayoutView.php?view=clientes"><i class="bi bi-person"></i> Clientes VIP</a></li>
            <li><a href="#"><i class="bi bi-gift"></i> Recompensas</a></li>
            <li><a href="#"><i class="bi bi-geo-alt"></i> Ubicaciones</a></li>
        </ul>
    </aside>

    <div class="content">
        <header class="topbar">
            <h4>Panel de Administración</h4>
        </header>

        <main class="main-content">
            <?php
            if ($vista === 'clientes') {
                include 'ClienteView.php';
            } else {
                include 'DashboardView.php';
            }
            ?>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($vista === 'dashboard'): ?>
        <script src="/CRM_INT/CRM/public/js/Dashboard.js"></script>
    <?php elseif ($vista === 'clientes'): ?>
        <script src="/CRM_INT/CRM/public/js/Cliente.js"></script>
    <?php endif; ?>
</body>
</html>
