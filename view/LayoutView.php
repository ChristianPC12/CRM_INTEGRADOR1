<?php
// ARCHIVO: LayoutView.php
$vista = $_GET['view'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>CRM Bastos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap y Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- CSS por vista -->
    <?php if ($vista === 'dashboard'): ?>
        <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Dashboard.css">
    <?php elseif ($vista === 'clientes'): ?>
        <link rel="stylesheet" href="/CRM_INT/CRM/public/css/Cliente.css">
    <?php endif; ?>

    <!-- CSS general del layout -->
    <link rel="stylesheet" href="public/css/Layout.css?v=<?= time() ?>">
</head>

<body>
    <aside class="sidebar">

        <ul>
            <?php
            $vista = $_GET['view'] ?? 'dashboard';
            ?>

            <!-- Sidebar -->
            <aside class="sidebar">
                <div class="sidebar-header">
                    <img src="/CRM_INT/CRM/public/img/Principal_Amarillo.png" alt="Logo" class="img-header">
                    <h3>CRM Bastos</h3>
                </div>
                <ul>
                    <li><a href="LayoutView.php?view=dashboard" class="<?= $vista === 'dashboard' ? 'active' : '' ?>">
                            <i class="bi bi-house"></i> Dashboard</a></li>

                    <li><a id="btnClientesVip" href="LayoutView.php?view=clientes"
                            class="<?= $vista === 'clientes' ? 'active' : '' ?>">
                            <i class="bi bi-award-fill"></i> Clientes VIP</a></li>

                    <li><a id="btnUsuariosVip" href="LayoutView.php?view=recompensas"
                            class="<?= $vista === 'recompensas' ? 'active' : '' ?>">
                            <i class="bi bi-person"></i> Usuarios</a></li>
                </ul>

            </aside>

        </ul>
    </aside>

    <div class="content">

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