<?php

/**
 * Gestor de archivo .env
 */
class EnvManager
{
    private string $basePath;
    private string $envPath;
    private string $envExamplePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->envPath = $basePath . '/.env';
        $this->envExamplePath = $basePath . '/.env.example';
    }

    /**
     * Crear archivo .env desde .env.example
     */
    public function createEnvFile(array $config): bool
    {
        // Leer .env.example como base
        if (!file_exists($this->envExamplePath)) {
            return false;
        }

        $content = file_get_contents($this->envExamplePath);

        // Reemplazar valores
        $replacements = [
            // Aplicación
            'APP_NAME' => $config['app_name'] ?? 'MiniCRM',
            'APP_ENV' => $config['app_env'] ?? 'production',
            'APP_DEBUG' => ($config['app_env'] ?? 'production') === 'local' ? 'true' : 'false',
            'APP_URL' => $config['app_url'] ?? 'http://localhost',
            'APP_TIMEZONE' => $config['app_timezone'] ?? 'America/Bogota',
            'APP_LOCALE' => $config['app_locale'] ?? 'es',
            'APP_FALLBACK_LOCALE' => $config['app_locale'] ?? 'es',

            // Base de datos
            'DB_CONNECTION' => $config['db_driver'] ?? 'mysql',
            'DB_HOST' => $config['db_host'] ?? '127.0.0.1',
            'DB_PORT' => $config['db_port'] ?? '3306',
            'DB_DATABASE' => $config['db_database'] ?? 'minicrm',
            'DB_USERNAME' => $config['db_username'] ?? 'root',
            'DB_PASSWORD' => $config['db_password'] ?? '',

            // Sesión y caché para hosting compartido
            'SESSION_DRIVER' => 'database',
            'CACHE_STORE' => 'database',
            'QUEUE_CONNECTION' => 'database',
        ];

        foreach ($replacements as $key => $value) {
            // Escapar caracteres especiales en el valor
            $escapedValue = $this->escapeEnvValue($value);

            // Buscar la línea (comentada o no)
            // Primero intentar reemplazar línea no comentada
            if (preg_match("/^{$key}=/m", $content)) {
                $content = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$escapedValue}",
                    $content
                );
            }
            // Si está comentada, descomentar y establecer valor
            elseif (preg_match("/^#\s*{$key}=/m", $content)) {
                $content = preg_replace(
                    "/^#\s*{$key}=.*/m",
                    "{$key}={$escapedValue}",
                    $content
                );
            }
            // Si no existe, agregarla al final
            else {
                $content .= "\n{$key}={$escapedValue}";
            }
        }

        // Escribir archivo
        return file_put_contents($this->envPath, $content) !== false;
    }

    /**
     * Generar APP_KEY
     */
    public function generateAppKey(): string
    {
        $key = 'base64:' . base64_encode(random_bytes(32));
        $this->setEnvValue('APP_KEY', $key);
        return $key;
    }

    /**
     * Establecer un valor en .env
     */
    public function setEnvValue(string $key, string $value): bool
    {
        if (!file_exists($this->envPath)) {
            return false;
        }

        $content = file_get_contents($this->envPath);
        $escapedValue = $this->escapeEnvValue($value);

        // Verificar si la clave existe
        if (preg_match("/^{$key}=/m", $content)) {
            // Reemplazar valor existente
            $content = preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$escapedValue}",
                $content
            );
        } else {
            // Agregar nueva clave
            $content .= "\n{$key}={$escapedValue}";
        }

        return file_put_contents($this->envPath, $content) !== false;
    }

    /**
     * Obtener un valor de .env
     */
    public function getEnvValue(string $key): ?string
    {
        if (!file_exists($this->envPath)) {
            return null;
        }

        $content = file_get_contents($this->envPath);

        if (preg_match("/^{$key}=(.*)$/m", $content, $matches)) {
            $value = trim($matches[1]);
            // Remover comillas si las tiene
            $value = trim($value, '"\'');
            return $value;
        }

        return null;
    }

    /**
     * Escapar valor para .env
     */
    private function escapeEnvValue(string $value): string
    {
        // Si contiene espacios o caracteres especiales, envolver en comillas
        if (preg_match('/[\s"\'#]/', $value) || empty($value)) {
            // Escapar comillas dobles internas
            $value = str_replace('"', '\\"', $value);
            return '"' . $value . '"';
        }

        return $value;
    }

    /**
     * Verificar si .env existe
     */
    public function envExists(): bool
    {
        return file_exists($this->envPath);
    }

    /**
     * Verificar si APP_KEY está configurado
     */
    public function hasAppKey(): bool
    {
        $key = $this->getEnvValue('APP_KEY');
        return !empty($key) && strpos($key, 'base64:') === 0;
    }

    /**
     * Hacer backup del .env actual
     */
    public function backupEnv(): bool
    {
        if (!file_exists($this->envPath)) {
            return false;
        }

        $backupPath = $this->envPath . '.backup.' . date('YmdHis');
        return copy($this->envPath, $backupPath);
    }
}
