<?php
/**
 * Proxy de API con CORS explícito
 * Este archivo actúa como intermediario para las llamadas API
 * evitando bloqueos de WAF/ModSecurity en hosting compartido
 *
 * Uso: api-proxy.php?endpoint=sites/{id}/status
 *      api-proxy.php?endpoint=leads/capture (POST)
 */

// ============================================
// CONFIGURACIÓN DE SEGURIDAD
// ============================================
define('RATE_LIMIT_REQUESTS', 30);      // Máximo de peticiones
define('RATE_LIMIT_WINDOW', 60);         // Ventana de tiempo en segundos
define('ALLOWED_ENDPOINTS', [            // Endpoints permitidos (whitelist)
    'leads/capture',                     // POST - captura de leads
    'sites/*/status',                    // GET - verificar estado del sitio
]);

// ============================================
// RATE LIMITING (basado en IP + archivos)
// ============================================
function getRateLimitFile($ip) {
    $dir = sys_get_temp_dir() . '/api_proxy_limits';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    return $dir . '/' . md5($ip) . '.json';
}

function checkRateLimit($ip) {
    $file = getRateLimitFile($ip);
    $now = time();
    $data = ['requests' => [], 'blocked_until' => 0];

    if (file_exists($file)) {
        $content = @file_get_contents($file);
        if ($content) {
            $data = json_decode($content, true) ?: $data;
        }
    }

    // Si está bloqueado temporalmente
    if ($data['blocked_until'] > $now) {
        return false;
    }

    // Limpiar peticiones antiguas (fuera de la ventana)
    $data['requests'] = array_filter($data['requests'], function($time) use ($now) {
        return $time > ($now - RATE_LIMIT_WINDOW);
    });

    // Verificar límite
    if (count($data['requests']) >= RATE_LIMIT_REQUESTS) {
        // Bloquear por el doble de la ventana si excede
        $data['blocked_until'] = $now + (RATE_LIMIT_WINDOW * 2);
        @file_put_contents($file, json_encode($data));
        return false;
    }

    // Registrar esta petición
    $data['requests'][] = $now;
    @file_put_contents($file, json_encode($data));

    return true;
}

// ============================================
// VALIDACIÓN DE ENDPOINT (whitelist)
// ============================================
function isEndpointAllowed($endpoint) {
    foreach (ALLOWED_ENDPOINTS as $pattern) {
        // Convertir patrón con * a regex
        $regex = '/^' . str_replace(['/', '*'], ['\/', '[a-f0-9\-]+'], $pattern) . '$/';
        if (preg_match($regex, $endpoint)) {
            return true;
        }
    }
    return false;
}

// ============================================
// INICIO DEL PROXY
// ============================================

// Headers CORS - enviar ANTES de cualquier output
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Site-Key, X-Requested-With, Accept');
header('Access-Control-Max-Age: 86400');
header('Content-Type: application/json; charset=utf-8');

// Manejar preflight OPTIONS (sin rate limit para OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Obtener IP del cliente
$clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$clientIp = explode(',', $clientIp)[0]; // Tomar primera IP si hay varias

// Verificar rate limit
if (!checkRateLimit($clientIp)) {
    http_response_code(429);
    echo json_encode([
        'error' => 'Demasiadas peticiones. Intenta de nuevo más tarde.',
        'retry_after' => RATE_LIMIT_WINDOW * 2
    ]);
    exit;
}

// Obtener el endpoint solicitado
$endpoint = $_GET['endpoint'] ?? '';

if (empty($endpoint)) {
    http_response_code(400);
    echo json_encode(['error' => 'Endpoint no especificado']);
    exit;
}

// Sanitizar endpoint (solo permitir caracteres seguros)
$endpoint = preg_replace('/[^a-zA-Z0-9\-_\/]/', '', $endpoint);

// Validar que el endpoint esté en la whitelist
if (!isEndpointAllowed($endpoint)) {
    http_response_code(403);
    echo json_encode(['error' => 'Endpoint no permitido']);
    exit;
}

// Construir la URL interna
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
         . '://' . $_SERVER['HTTP_HOST'];

$apiUrl = $baseUrl . '/api/v1/' . $endpoint;

// Preparar contexto para la solicitud interna
$method = $_SERVER['REQUEST_METHOD'];
$headers = [
    'Accept: application/json',
    'Content-Type: application/json',
];

// Agregar X-Site-Key si viene en los headers originales
if (isset($_SERVER['HTTP_X_SITE_KEY'])) {
    $headers[] = 'X-Site-Key: ' . $_SERVER['HTTP_X_SITE_KEY'];
}

// Pasar Origin y Referer para validación de dominio en el backend
if (isset($_SERVER['HTTP_ORIGIN'])) {
    $headers[] = 'Origin: ' . $_SERVER['HTTP_ORIGIN'];
}
if (isset($_SERVER['HTTP_REFERER'])) {
    $headers[] = 'Referer: ' . $_SERVER['HTTP_REFERER'];
}

$options = [
    'http' => [
        'method' => $method,
        'header' => implode("\r\n", $headers),
        'ignore_errors' => true,
        'timeout' => 30,
    ]
];

// Si es POST, agregar el body
if ($method === 'POST') {
    $input = file_get_contents('php://input');
    $options['http']['content'] = $input;
}

// Realizar la solicitud
$context = stream_context_create($options);
$response = @file_get_contents($apiUrl, false, $context);

// Obtener el código de respuesta
$statusCode = 200;
if (isset($http_response_header)) {
    foreach ($http_response_header as $header) {
        if (preg_match('/^HTTP\/\d+\.\d+\s+(\d+)/', $header, $matches)) {
            $statusCode = (int) $matches[1];
        }
    }
}

http_response_code($statusCode);

if ($response === false) {
    echo json_encode([
        'error' => 'Error al conectar con la API',
        'endpoint' => $endpoint,
        'url' => $apiUrl
    ]);
} else {
    echo $response;
}
