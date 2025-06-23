<?php
require_once '../config/Database.php';
$pdo = (new Database())->getConnection();

try {
    $stmt = $pdo->query("SELECT Id, Usuario, Rol FROM usuario ORDER BY Id DESC");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($usuarios) === 0) {
        echo "<p class='text-center mt-3'>No hay usuarios registrados.</p>";
        exit;
    }

    echo "<table class='table table-bordered table-hover'>
            <thead class='table-dark'>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>";

    foreach ($usuarios as $usuario) {
        echo "<tr>
                <td>{$usuario['Id']}</td>
                <td>{$usuario['Usuario']}</td>
                <td>{$usuario['Rol']}</td>
                <td>
                    <button class='btn btn-sm btn-danger eliminar-usuario' data-id='{$usuario['Id']}'>
                        <i class='bi bi-trash'></i> Eliminar
                    </button>
                </td>
              </tr>";
    }

    echo "</tbody></table>";

} catch (PDOException $e) {
    echo "<p class='text-danger'>Error al obtener usuarios: {$e->getMessage()}</p>";
}
