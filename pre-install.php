<?php

/**
 * MiniCRM Pre-Installer para Hosting Compartido
 *
 * Este script prepara la aplicación para funcionar en hosting compartido
 * donde el dominio apunta directamente a public_html (sin subcarpeta public/).
 *
 * Acciones que realiza:
 * 1. Mueve el contenido de public/ a la raíz
 * 2. Modifica index.php para corregir los paths
 * 3. Crea .htaccess de protección para carpetas sensibles
 * 4. Se auto-elimina y redirige al instalador
 *
 * USO: Subir todo el ZIP a public_html y acceder a /pre-install.php
 */

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Constantes
define('BASE_PATH', __DIR__);
define('PUBLIC_PATH', BASE_PATH . '/public');

/**
 * Clase principal del pre-instalador
 */
class PreInstaller
{
    private array $errors = [];
    private array $logs = [];
    private bool $isReady = false;

    public function __construct()
    {
        $this->checkEnvironment();
    }

    /**
     * Verificar el entorno antes de proceder
     */
    private function checkEnvironment(): void
    {
        // Verificar que existe la carpeta public
        if (!is_dir(PUBLIC_PATH)) {
            $this->errors[] = 'La carpeta "public/" no existe. ¿Ya se ejecutó este script?';
            return;
        }

        // Verificar que existe index.php en public
        if (!file_exists(PUBLIC_PATH . '/index.php')) {
            $this->errors[] = 'No se encuentra public/index.php';
            return;
        }

        // Verificar permisos de escritura en la raíz
        if (!is_writable(BASE_PATH)) {
            $this->errors[] = 'No hay permisos de escritura en la carpeta raíz';
            return;
        }

        $this->isReady = true;
    }

    /**
     * Ejecutar el proceso de preparación
     */
    public function run(): bool
    {
        if (!$this->isReady) {
            return false;
        }

        // Paso 1: Mover archivos de public/ a raíz
        if (!$this->movePublicFiles()) {
            return false;
        }

        // Paso 2: Modificar index.php para corregir paths
        if (!$this->modifyIndexPaths()) {
            return false;
        }

        // Paso 3: Modificar install.php para corregir paths
        if (!$this->modifyInstallPaths()) {
            return false;
        }

        // Paso 4: Crear .htaccess de protección
        if (!$this->createProtectionHtaccess()) {
            return false;
        }

        // Paso 4: Eliminar carpeta public vacía
        $this->removeEmptyPublicFolder();

        // Paso 5: Auto-eliminarse
        $this->selfDestruct();

        return true;
    }

    /**
     * Mover archivos de public/ a la raíz
     */
    private function movePublicFiles(): bool
    {
        $this->log('Moviendo archivos de public/ a raíz...');

        $publicFiles = scandir(PUBLIC_PATH);

        foreach ($publicFiles as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $source = PUBLIC_PATH . '/' . $file;
            $destination = BASE_PATH . '/' . $file;

            // Si ya existe en la raíz, hacer backup o saltar
            if (file_exists($destination)) {
                // Para .htaccess, hacemos merge especial
                if ($file === '.htaccess') {
                    $this->log("  - Saltando .htaccess (ya existe en raíz)");
                    continue;
                }

                // Para otros archivos, renombrar el existente
                $backupName = $file . '.backup_' . date('YmdHis');
                rename($destination, BASE_PATH . '/' . $backupName);
                $this->log("  - Backup creado: {$backupName}");
            }

            // Manejar symlinks (como public/storage -> storage/app/public)
            if (is_link($source)) {
                // Eliminar el symlink, no lo necesitamos mover
                unlink($source);
                $this->log("  - Symlink eliminado: {$file} (no necesario en raíz)");
                continue;
            }

            // Mover archivo/carpeta
            if (is_dir($source)) {
                if (!$this->moveDirectory($source, $destination)) {
                    $this->errors[] = "No se pudo mover directorio: {$file}";
                    return false;
                }
            } else {
                if (!rename($source, $destination)) {
                    $this->errors[] = "No se pudo mover archivo: {$file}";
                    return false;
                }
            }

            $this->log("  - Movido: {$file}");
        }

        return true;
    }

    /**
     * Mover directorio recursivamente
     */
    private function moveDirectory(string $source, string $destination): bool
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $files = scandir($source);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $srcPath = $source . '/' . $file;
            $dstPath = $destination . '/' . $file;

            // Manejar symlinks
            if (is_link($srcPath)) {
                unlink($srcPath);
                continue;
            }

            if (is_dir($srcPath)) {
                if (!$this->moveDirectory($srcPath, $dstPath)) {
                    return false;
                }
            } else {
                if (!rename($srcPath, $dstPath)) {
                    return false;
                }
            }
        }

        // Eliminar directorio vacío (verificar que no sea symlink)
        if (is_dir($source) && !is_link($source)) {
            @rmdir($source);
        }

        return true;
    }

    /**
     * Modificar index.php para que los paths apunten correctamente
     */
    private function modifyIndexPaths(): bool
    {
        $this->log('Modificando paths en index.php...');

        $indexPath = BASE_PATH . '/index.php';

        if (!file_exists($indexPath)) {
            $this->errors[] = 'index.php no encontrado después de mover archivos';
            return false;
        }

        $content = file_get_contents($indexPath);
        $originalContent = $content;

        // Reemplazar los paths relativos
        // De: __DIR__.'/../folder' a __DIR__.'/folder'
        $replacements = [
            "__DIR__.'/../storage/framework/maintenance.php'" => "__DIR__.'/storage/framework/maintenance.php'",
            "__DIR__.'/../vendor/autoload.php'" => "__DIR__.'/vendor/autoload.php'",
            "__DIR__.'/../bootstrap/app.php'" => "__DIR__.'/bootstrap/app.php'",
            // Variantes con comillas dobles por si acaso
            '__DIR__."/../storage/framework/maintenance.php"' => '__DIR__."/storage/framework/maintenance.php"',
            '__DIR__."/../vendor/autoload.php"' => '__DIR__."/vendor/autoload.php"',
            '__DIR__."/../bootstrap/app.php"' => '__DIR__."/bootstrap/app.php"',
        ];

        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        if ($content === $originalContent) {
            $this->log('  - No se encontraron paths para modificar (posiblemente ya modificado)');
            return true;
        }

        if (file_put_contents($indexPath, $content) === false) {
            $this->errors[] = 'No se pudo escribir index.php modificado';
            return false;
        }

        $this->log('  - Paths actualizados correctamente');

        return true;
    }

    /**
     * Modificar install.php para que los paths apunten correctamente
     */
    private function modifyInstallPaths(): bool
    {
        $this->log('Modificando paths en install.php...');

        $installPath = BASE_PATH . '/install.php';

        if (!file_exists($installPath)) {
            $this->log('  - install.php no encontrado (saltando)');
            return true;
        }

        $content = file_get_contents($installPath);
        $originalContent = $content;

        // Cambiar dirname(__DIR__) por __DIR__ ya que install.php ahora está en la raíz
        $content = str_replace(
            '$basePath = dirname(__DIR__);',
            '$basePath = __DIR__;',
            $content
        );

        if ($content === $originalContent) {
            $this->log('  - No se encontraron paths para modificar (posiblemente ya modificado)');
            return true;
        }

        if (file_put_contents($installPath, $content) === false) {
            $this->errors[] = 'No se pudo escribir install.php modificado';
            return false;
        }

        $this->log('  - Paths actualizados correctamente');

        return true;
    }

    /**
     * Crear .htaccess para proteger carpetas sensibles
     */
    private function createProtectionHtaccess(): bool
    {
        $this->log('Configurando protección .htaccess...');

        // Contenido del .htaccess principal
        $htaccessContent = <<<'HTACCESS'
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Proteger archivos sensibles
    RewriteRule ^\.env$ - [F,L]
    RewriteRule ^\.env\..*$ - [F,L]
    RewriteRule ^composer\.(json|lock)$ - [F,L]
    RewriteRule ^package(-lock)?\.json$ - [F,L]
    RewriteRule ^phpunit\.xml$ - [F,L]
    RewriteRule ^artisan$ - [F,L]

    # Proteger directorios sensibles
    RewriteRule ^app/.*$ - [F,L]
    RewriteRule ^bootstrap/.*$ - [F,L]
    RewriteRule ^config/.*$ - [F,L]
    RewriteRule ^database/.*$ - [F,L]
    RewriteRule ^installer/.*\.php$ - [F,L]
    RewriteRule ^resources/.*$ - [F,L]
    RewriteRule ^routes/.*$ - [F,L]
    RewriteRule ^storage/(?!app/public/).*$ - [F,L]
    RewriteRule ^tests/.*$ - [F,L]
    RewriteRule ^vendor/.*$ - [F,L]

    # Permitir install.php durante instalación
    RewriteCond %{REQUEST_URI} ^/install\.php
    RewriteRule ^ - [L]

    # Redirigir todo a index.php (Laravel)
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Deshabilitar listado de directorios
Options -Indexes

# Proteger archivos ocultos
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

# Headers de seguridad
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Configuración PHP
<IfModule mod_php.c>
    php_flag display_errors Off
    php_flag log_errors On
</IfModule>
HTACCESS;

        $htaccessPath = BASE_PATH . '/.htaccess';

        // Si ya existe, hacer backup
        if (file_exists($htaccessPath)) {
            $backupPath = BASE_PATH . '/.htaccess.backup_' . date('YmdHis');
            copy($htaccessPath, $backupPath);
            $this->log("  - Backup de .htaccess existente creado");
        }

        if (file_put_contents($htaccessPath, $htaccessContent) === false) {
            $this->errors[] = 'No se pudo crear .htaccess';
            return false;
        }

        $this->log('  - .htaccess de protección creado');

        return true;
    }

    /**
     * Eliminar carpeta public vacía
     */
    private function removeEmptyPublicFolder(): void
    {
        if (is_dir(PUBLIC_PATH)) {
            $files = array_diff(scandir(PUBLIC_PATH), ['.', '..']);

            if (empty($files)) {
                rmdir(PUBLIC_PATH);
                $this->log('Carpeta public/ eliminada (vacía)');
            } else {
                $this->log('Carpeta public/ no eliminada (contiene archivos)');
            }
        }
    }

    /**
     * Auto-eliminarse
     */
    private function selfDestruct(): void
    {
        // Marcar para eliminación al final del script
        register_shutdown_function(function () {
            @unlink(__FILE__);
        });

        $this->log('pre-install.php marcado para auto-eliminación');
    }

    /**
     * Agregar mensaje al log
     */
    private function log(string $message): void
    {
        $this->logs[] = $message;
    }

    /**
     * Obtener errores
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Obtener logs
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    /**
     * Verificar si está listo para ejecutar
     */
    public function isReady(): bool
    {
        return $this->isReady;
    }
}

// ============================================================================
// EJECUCIÓN
// ============================================================================

$preInstaller = new PreInstaller();
$success = false;
$redirectUrl = '/install.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['execute'])) {
    $success = $preInstaller->run();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiniCRM - Preparación para Hosting Compartido</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="min-h-screen gradient-bg flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-white">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold">MiniCRM - Pre-Instalador</h1>
                    <p class="text-blue-100">Preparación para Hosting Compartido</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <?php if ($success): ?>
                <!-- Éxito -->
                <div class="text-center">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-900 mb-2">¡Preparación Completada!</h2>
                    <p class="text-gray-600 mb-6">
                        Los archivos han sido reorganizados correctamente.
                        Ahora puedes continuar con la instalación.
                    </p>

                    <!-- Log de acciones -->
                    <div class="bg-gray-50 rounded-xl p-4 mb-6 text-left">
                        <h3 class="font-semibold text-gray-700 mb-3">Acciones realizadas:</h3>
                        <ul class="space-y-1 text-sm text-gray-600">
                            <?php foreach ($preInstaller->getLogs() as $log): ?>
                                <li class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <?= htmlspecialchars($log) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <a href="<?= $redirectUrl ?>"
                       class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-indigo-700 transition shadow-lg">
                        Continuar con la Instalación
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>

            <?php elseif (!empty($preInstaller->getErrors())): ?>
                <!-- Errores -->
                <div class="text-center">
                    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Error en la Preparación</h2>
                    <p class="text-gray-600 mb-6">
                        Se encontraron problemas que impiden continuar.
                    </p>

                    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 text-left">
                        <h3 class="font-semibold text-red-700 mb-3">Errores encontrados:</h3>
                        <ul class="space-y-2 text-sm text-red-600">
                            <?php foreach ($preInstaller->getErrors() as $error): ?>
                                <li class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <?= htmlspecialchars($error) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <?php if (in_array('La carpeta "public/" no existe. ¿Ya se ejecutó este script?', $preInstaller->getErrors())): ?>
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 text-left">
                            <p class="text-sm text-blue-700">
                                <strong>Nota:</strong> Si ya ejecutaste este script anteriormente, la preparación ya está completa.
                                Intenta acceder directamente al instalador.
                            </p>
                        </div>
                        <a href="<?= $redirectUrl ?>"
                           class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-indigo-700 transition">
                            Ir al Instalador
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <!-- Formulario inicial -->
                <div class="mb-6">
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-amber-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <h3 class="font-semibold text-amber-800">¿Qué hace este script?</h3>
                                <p class="text-sm text-amber-700 mt-1">
                                    Este script prepara MiniCRM para funcionar en hosting compartido donde
                                    el dominio apunta directamente a <code class="bg-amber-100 px-1 rounded">public_html</code>.
                                </p>
                            </div>
                        </div>
                    </div>

                    <h2 class="text-xl font-bold text-gray-900 mb-4">Acciones que realizará:</h2>

                    <ul class="space-y-3 mb-6">
                        <li class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-bold text-blue-600">1</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Mover archivos públicos</p>
                                <p class="text-sm text-gray-500">
                                    Mueve el contenido de <code class="bg-gray-200 px-1 rounded">public/</code> a la raíz
                                </p>
                            </div>
                        </li>
                        <li class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-bold text-blue-600">2</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Corregir rutas de Laravel</p>
                                <p class="text-sm text-gray-500">
                                    Ajusta <code class="bg-gray-200 px-1 rounded">index.php</code> para encontrar las carpetas del framework
                                </p>
                            </div>
                        </li>
                        <li class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-bold text-blue-600">3</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Crear protección .htaccess</p>
                                <p class="text-sm text-gray-500">
                                    Protege carpetas sensibles como <code class="bg-gray-200 px-1 rounded">vendor/</code>,
                                    <code class="bg-gray-200 px-1 rounded">config/</code>, etc.
                                </p>
                            </div>
                        </li>
                        <li class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-bold text-blue-600">4</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Auto-eliminarse</p>
                                <p class="text-sm text-gray-500">
                                    Por seguridad, este script se elimina después de ejecutarse
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>

                <form method="POST" class="text-center">
                    <input type="hidden" name="execute" value="1">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-indigo-700 transition shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Preparar Instalación
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 text-center text-sm text-gray-500">
            MiniCRM &copy; <?= date('Y') ?> - Sistema de Gestión de Leads
        </div>
    </div>
</body>
</html>
