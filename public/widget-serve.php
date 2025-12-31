<?php
/**
 * Servidor de Widget con CORS explícito y custom fields dinámicos
 * Este archivo sirve widget.js con los headers CORS correctos
 * e inyecta los custom fields del sitio como variable global
 */

// Headers CORS - enviar antes de cualquier output
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Site-Key, X-Requested-With');
header('Content-Type: application/javascript; charset=utf-8');
header('Cache-Control: public, max-age=300'); // Cache de 5 minutos

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Cargar custom fields de tipo 'lead' (no depende de site_id)
$customFields = [];

// Intentar cargar Laravel bootstrap
$laravelBootstrap = __DIR__ . '/../vendor/autoload.php';

if (file_exists($laravelBootstrap)) {
    try {
        require_once $laravelBootstrap;
        $app = require_once __DIR__ . '/../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        // Consultar custom fields obligatorios de tipo 'lead' (incluir campos del sistema)
        $fields = \Illuminate\Support\Facades\DB::table('custom_fields')
            ->where('entity_type', 'lead')
            ->where('is_active', 1)
            ->where('is_required', 1)
            ->orderBy('order', 'asc')
            ->get(['id', 'name', 'label', 'type', 'is_required', 'validation_rules', 'default_value'])
            ->map(fn($field) => (array) $field)
            ->toArray();

        foreach ($fields as $field) {
            $options = null;

            // Si el campo requiere opciones, consultar tabla dinámica
            if (in_array($field['type'], ['select', 'radio', 'multiselect'])) {
                $optionsTableName = $field['name'] . '_options';
                if (\Illuminate\Support\Facades\Schema::hasTable($optionsTableName)) {
                    $optionsData = \Illuminate\Support\Facades\DB::table($optionsTableName)
                        ->orderBy('order')
                        ->get(['label', 'value'])
                        ->toArray();

                    if (!empty($optionsData)) {
                        $options = array_map(fn($opt) => [
                            'label' => $opt->label,
                            'value' => $opt->value
                        ], $optionsData);
                    }
                }
            }

            $customFields[] = [
                'name' => $field['name'],
                'label' => $field['label'],
                'type' => $field['type'],
                'required' => (bool) $field['is_required'],
                'validation' => $field['validation_rules'],
                'options' => $options,
                'default_value' => $field['default_value'],
            ];
        }
    } catch (\Exception $e) {
        // Silenciar errores - el widget usará campos por defecto
    }
}

// Inyectar custom fields como variable global antes del widget
echo "// MiniCRM Widget - Custom Fields\n";
echo "window.MCW_CUSTOM_FIELDS = " . json_encode($customFields) . ";\n\n";

// Servir el archivo widget.js
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
