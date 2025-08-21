<?php
/**
 * Clase WhatsApiService
 *
 * Servicio para interactuar con una API de WhatsApp a través de peticiones HTTP.
 * Permite verificar el estado del servicio y enviar mensajes.
 * 
 * Usa cURL para realizar solicitudes GET y POST, y maneja respuestas en formato JSON.
 */
class WhatsApiService {
  /**
   * @var string $base URL base del servicio de WhatsApp API
   */
  private $base;
  
  /**
   * Constructor de la clase
   *
   * @param string $base URL base de la API (ejemplo: http://localhost:3000)
   */
  public function __construct($base) { 
    $this->base = rtrim($base, '/'); 
  }

  /**
   * Verifica el estado del servicio WhatsApp API
   *
   * Realiza una petición GET al endpoint `/status-json`.
   *
   * @return array Respuesta de la API decodificada como array asociativo
   * @throws Exception Si ocurre un error en la petición
   */
  public function status() {
    return $this->request('GET', '/status-json');
  }

  /**
   * Envía un mensaje a un número específico
   *
   * Realiza una petición POST al endpoint `/send`.
   *
   * @param string $to Número de teléfono destino en formato válido para la API
   * @param string $message Contenido del mensaje a enviar
   * @return array Respuesta de la API en formato array asociativo
   * @throws Exception Si ocurre un error en la petición
   */
  public function send($to, $message) {
    return $this->request('POST', '/send', ['to' => $to, 'message' => $message]);
  }

  /**
   * Método privado para realizar las solicitudes HTTP a la API
   *
   * Maneja tanto GET como POST. Para POST se envían los datos como JSON.
   *
   * @param string $method Método HTTP a usar ("GET" o "POST")
   * @param string $path Ruta del endpoint de la API (ejemplo: "/send")
   * @param array|null $payload Datos a enviar en el cuerpo de la petición (solo para POST)
   * @return array Respuesta de la API decodificada en array asociativo
   * @throws Exception Si ocurre un error de conexión o si la API devuelve un código >= 400
   */
  private function request($method, $path, $payload = null) {
    $url = $this->base . $path;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($method === 'POST') {
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
    }
    $res = curl_exec($ch);
    if ($res === false) { throw new Exception(curl_error($ch)); }
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($res, true);
    if ($code >= 400) { throw new Exception(($data['error'] ?? 'HTTP '.$code)); }
    return $data ?: [];
  }
}
