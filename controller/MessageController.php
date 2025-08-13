<?php
// Controlador deprecado. La lógica ahora está integrada directamente en public/index.php
http_response_code(410);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['error'=>'MessageController eliminado. Usa index.php']);
