<?php
/**
 * Servidor de Widget con CORS explícito
 * Este archivo sirve widget.js con los headers CORS correctos
 * para evitar bloqueos de WAF/ModSecurity en hosting compartido
 */

// Headers CORS - enviar antes de cualquier output
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Site-Key, X-Requested-With');
header('Content-Type: application/javascript; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Servir el archivo widget.js
// Buscar en raíz (producción post pre-install) o en public/ (desarrollo)
$possiblePaths = [
    __DIR__ . '/widget.js',
    __DIR__ . '/public/widget.js',
];

$widgetPath = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        $widgetPath = $path;
        break;
    }
}

if ($widgetPath) {
    readfile($widgetPath);
} else {
    http_response_code(404);
    echo '// Widget not found at: ' . implode(', ', $possiblePaths);
}
