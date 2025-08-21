<?php
// Controlador de mensajes (DEPRECATED)
// Este archivo ya no se utiliza. La lógica fue movida a public/index.php

http_response_code(410); // Indica que el recurso ya no está disponible
header('Content-Type: application/json; charset=utf-8'); // Respuesta en formato JSON
echo json_encode(['error'=>'MessageController eliminado. Usa index.php']); // Mensaje de error descriptivo
