<?php

/**
 * Verificador de requisitos del sistema
 */
class RequirementsChecker
{
    private string $basePath;
    private array $results = [];
    private array $errors = [];

    /**
     * Requisitos de PHP
     */
    private array $phpRequirements = [
        'version' => '8.2.0',
        'extensions' => [
            'bcmath' => 'BCMath',
            'ctype' => 'Ctype',
            'curl' => 'cURL',
            'dom' => 'DOM',
            'fileinfo' => 'Fileinfo',
            'json' => 'JSON',
            'mbstring' => 'Mbstring',
            'openssl' => 'OpenSSL',
            'pdo' => 'PDO',
            'pdo_mysql' => 'PDO MySQL',
            'tokenizer' => 'Tokenizer',
            'xml' => 'XML',
        ],
    ];

    /**
     * Directorios que necesitan permisos de escritura
     */
    private array $writableDirectories = [
        'storage',
        'storage/app',
        'storage/app/public',
        'storage/framework',
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/logs',
        'bootstrap/cache',
    ];

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Ejecutar todas las verificaciones
     */
    public function checkAll(): bool
    {
        $this->checkPhpVersion();
        $this->checkExtensions();
        $this->checkWritableDirectories();
        $this->checkComposerInstalled();

        return empty($this->errors);
    }

    /**
     * Obtener resultados de todas las verificaciones
     */
    public function getResults(): array
    {
        $this->checkPhpVersion();
        $this->checkExtensions();
        $this->checkWritableDirectories();
        $this->checkComposerInstalled();

        return $this->results;
    }

    /**
     * Obtener errores
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Verificar versión de PHP
     */
    private function checkPhpVersion(): void
    {
        $current = PHP_VERSION;
        $required = $this->phpRequirements['version'];
        $passed = version_compare($current, $required, '>=');

        $this->results['php'] = [
            'name' => "PHP >= {$required}",
            'current' => $current,
            'required' => $required,
            'passed' => $passed,
            'type' => 'version',
        ];

        if (!$passed) {
            $this->errors[] = "Se requiere PHP {$required} o superior. Versión actual: {$current}";
        }
    }

    /**
     * Verificar extensiones de PHP
     */
    private function checkExtensions(): void
    {
        $this->results['extensions'] = [];

        foreach ($this->phpRequirements['extensions'] as $ext => $name) {
            $loaded = extension_loaded($ext);

            $this->results['extensions'][$ext] = [
                'name' => $name,
                'extension' => $ext,
                'passed' => $loaded,
                'type' => 'extension',
            ];

            if (!$loaded) {
                $this->errors[] = "La extensión {$name} ({$ext}) no está instalada.";
            }
        }
    }

    /**
     * Verificar directorios escribibles
     */
    private function checkWritableDirectories(): void
    {
        $this->results['directories'] = [];

        foreach ($this->writableDirectories as $dir) {
            $path = $this->basePath . '/' . $dir;
            $exists = file_exists($path);
            $writable = $exists && is_writable($path);

            // Intentar crear si no existe
            if (!$exists) {
                $created = @mkdir($path, 0775, true);
                $exists = $created;
                $writable = $created;
            }

            $this->results['directories'][$dir] = [
                'name' => $dir,
                'path' => $path,
                'exists' => $exists,
                'writable' => $writable,
                'passed' => $writable,
                'type' => 'directory',
            ];

            if (!$writable) {
                $this->errors[] = "El directorio '{$dir}' no tiene permisos de escritura.";
            }
        }
    }

    /**
     * Verificar que Composer está instalado (vendor existe)
     */
    private function checkComposerInstalled(): void
    {
        $vendorPath = $this->basePath . '/vendor';
        $autoloadPath = $vendorPath . '/autoload.php';

        $passed = file_exists($vendorPath) && file_exists($autoloadPath);

        $this->results['composer'] = [
            'name' => 'Dependencias de Composer',
            'path' => $vendorPath,
            'passed' => $passed,
            'type' => 'composer',
        ];

        if (!$passed) {
            $this->errors[] = "Las dependencias de Composer no están instaladas. Ejecuta 'composer install' o sube la carpeta 'vendor'.";
        }
    }

    /**
     * Verificar si todo pasó
     */
    public function allPassed(): bool
    {
        if (empty($this->results)) {
            $this->getResults();
        }

        foreach ($this->results as $key => $result) {
            if (is_array($result) && isset($result['passed'])) {
                if (!$result['passed']) {
                    return false;
                }
            } elseif (is_array($result)) {
                foreach ($result as $subResult) {
                    if (isset($subResult['passed']) && !$subResult['passed']) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Obtener resumen de requisitos
     */
    public function getSummary(): array
    {
        if (empty($this->results)) {
            $this->getResults();
        }

        $total = 0;
        $passed = 0;

        // Contar PHP version
        $total++;
        if ($this->results['php']['passed']) {
            $passed++;
        }

        // Contar extensiones
        foreach ($this->results['extensions'] as $ext) {
            $total++;
            if ($ext['passed']) {
                $passed++;
            }
        }

        // Contar directorios
        foreach ($this->results['directories'] as $dir) {
            $total++;
            if ($dir['passed']) {
                $passed++;
            }
        }

        // Contar composer
        $total++;
        if ($this->results['composer']['passed']) {
            $passed++;
        }

        return [
            'total' => $total,
            'passed' => $passed,
            'failed' => $total - $passed,
            'percentage' => round(($passed / $total) * 100),
        ];
    }
}
