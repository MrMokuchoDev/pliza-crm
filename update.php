<?php
/**
 * Pliza CRM - Web Update Script
 *
 * Este script permite actualizar el sistema desde el navegador
 * sin necesidad de acceso SSH.
 *
 * IMPORTANTE: Este archivo debe eliminarse despues de la actualizacion.
 */

// Prevent direct access in production without auth
session_start();

// Configuration
define('UPDATE_TOKEN_FILE', __DIR__ . '/storage/update-token.txt');
define('INSTALLED_LOCK', __DIR__ . '/storage/installed.lock');
define('VERSION_CONFIG', __DIR__ . '/config/version.php');

// Check if system is installed
if (!file_exists(INSTALLED_LOCK)) {
    die('El sistema no esta instalado. Por favor ejecute install.php primero.');
}

// Simple token-based authentication
function generateToken(): string {
    $token = bin2hex(random_bytes(32));
    file_put_contents(UPDATE_TOKEN_FILE, $token);
    return $token;
}

function validateToken(string $token): bool {
    if (!file_exists(UPDATE_TOKEN_FILE)) {
        return false;
    }
    $storedToken = trim(file_get_contents(UPDATE_TOKEN_FILE));
    return hash_equals($storedToken, $token);
}

function deleteToken(): void {
    if (file_exists(UPDATE_TOKEN_FILE)) {
        unlink(UPDATE_TOKEN_FILE);
    }
}

// Get current version
function getCurrentVersion(): string {
    if (!file_exists(VERSION_CONFIG)) {
        return '1.0.0';
    }
    $config = include VERSION_CONFIG;
    return $config['current'] ?? '1.0.0';
}

// Check GitHub for latest release
function checkLatestRelease(): ?array {
    $repo = 'MrMokuchoDev/pliza-crm';
    $url = "https://api.github.com/repos/{$repo}/releases";

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'Accept: application/vnd.github.v3+json',
                'User-Agent: Pliza-CRM-Updater'
            ],
            'timeout' => 30
        ]
    ]);

    $response = @file_get_contents($url, false, $context);

    if (!$response) {
        return null;
    }

    $releases = json_decode($response, true);

    if (!is_array($releases) || empty($releases)) {
        return null;
    }

    // Find first non-prerelease, non-draft release
    foreach ($releases as $release) {
        if (!($release['prerelease'] ?? false) && !($release['draft'] ?? false)) {
            return $release;
        }
    }

    return $releases[0] ?? null;
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    $token = $_POST['token'] ?? '';

    if (!validateToken($token)) {
        echo json_encode(['success' => false, 'message' => 'Token invalido o expirado.']);
        exit;
    }

    $action = $_POST['action'];

    switch ($action) {
        case 'check':
            $current = getCurrentVersion();
            $latest = checkLatestRelease();

            if (!$latest) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se pudo obtener informacion de GitHub.'
                ]);
                exit;
            }

            $latestVersion = ltrim($latest['tag_name'] ?? '', 'vV');
            $updateAvailable = version_compare($latestVersion, $current, '>');

            // Get download URL
            $downloadUrl = $latest['zipball_url'] ?? null;
            if (!empty($latest['assets'])) {
                foreach ($latest['assets'] as $asset) {
                    if (str_ends_with($asset['name'], '.zip')) {
                        $downloadUrl = $asset['browser_download_url'];
                        break;
                    }
                }
            }

            echo json_encode([
                'success' => true,
                'current_version' => $current,
                'latest_version' => $latestVersion,
                'update_available' => $updateAvailable,
                'download_url' => $downloadUrl,
                'changelog' => $latest['body'] ?? '',
                'published_at' => $latest['published_at'] ?? ''
            ]);
            break;

        case 'download':
            $url = $_POST['download_url'] ?? '';
            $version = $_POST['version'] ?? '';

            if (!$url || !$version) {
                echo json_encode(['success' => false, 'message' => 'URL o version no especificada.']);
                exit;
            }

            $updatesDir = __DIR__ . '/storage/app/updates';
            if (!is_dir($updatesDir)) {
                mkdir($updatesDir, 0755, true);
            }

            $destPath = "{$updatesDir}/plizacrm-v{$version}.zip";

            // Download file
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => [
                        'Accept: application/octet-stream',
                        'User-Agent: Pliza-CRM-Updater'
                    ],
                    'timeout' => 300
                ]
            ]);

            $content = @file_get_contents($url, false, $context);

            if (!$content) {
                echo json_encode(['success' => false, 'message' => 'Error al descargar el archivo.']);
                exit;
            }

            file_put_contents($destPath, $content);

            echo json_encode([
                'success' => true,
                'message' => 'Descarga completada.',
                'path' => $destPath,
                'size' => filesize($destPath)
            ]);
            break;

        case 'backup':
            $timestamp = date('Y-m-d_His');
            $backupDir = __DIR__ . "/storage/app/backups/{$timestamp}";

            if (!mkdir($backupDir, 0755, true)) {
                echo json_encode(['success' => false, 'message' => 'No se pudo crear directorio de backup.']);
                exit;
            }

            // Backup .env
            if (file_exists(__DIR__ . '/.env')) {
                copy(__DIR__ . '/.env', "{$backupDir}/.env");
            }

            // Backup version.php
            if (file_exists(VERSION_CONFIG)) {
                if (!is_dir("{$backupDir}/config")) {
                    mkdir("{$backupDir}/config", 0755, true);
                }
                copy(VERSION_CONFIG, "{$backupDir}/config/version.php");
            }

            echo json_encode([
                'success' => true,
                'message' => 'Backup creado.',
                'path' => $backupDir
            ]);
            break;

        case 'apply':
            $zipPath = $_POST['zip_path'] ?? '';
            $version = $_POST['version'] ?? '';
            $backupPath = $_POST['backup_path'] ?? '';

            if (!file_exists($zipPath)) {
                echo json_encode(['success' => false, 'message' => 'Archivo de actualizacion no encontrado.']);
                exit;
            }

            // Create maintenance flag
            file_put_contents(__DIR__ . '/storage/framework/down', json_encode([
                'secret' => 'update-in-progress',
                'time' => time()
            ]));

            // Extract ZIP
            $zip = new ZipArchive();
            if ($zip->open($zipPath) !== true) {
                echo json_encode(['success' => false, 'message' => 'No se pudo abrir el archivo ZIP.']);
                exit;
            }

            $tempDir = __DIR__ . '/storage/app/updates/temp_' . time();
            mkdir($tempDir, 0755, true);
            $zip->extractTo($tempDir);
            $zip->close();

            // Find source directory
            $dirs = glob("{$tempDir}/*", GLOB_ONLYDIR);
            $sourceDir = count($dirs) === 1 ? $dirs[0] : $tempDir;

            // Preserved paths
            $preserved = ['.env', 'storage/app/public', 'storage/app/backups', 'storage/app/updates', 'storage/logs'];

            // Copy files
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($sourceDir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                $relativePath = str_replace($sourceDir . '/', '', $item->getPathname());

                // Check preserved
                $skip = false;
                foreach ($preserved as $p) {
                    if (str_starts_with($relativePath, $p)) {
                        $skip = true;
                        break;
                    }
                }

                if ($skip) continue;

                $destPath = __DIR__ . '/' . $relativePath;

                if ($item->isDir()) {
                    if (!is_dir($destPath)) {
                        mkdir($destPath, 0755, true);
                    }
                } else {
                    $destDir = dirname($destPath);
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0755, true);
                    }
                    copy($item->getPathname(), $destPath);
                }
            }

            // Update version in config
            if (file_exists(VERSION_CONFIG)) {
                $content = file_get_contents(VERSION_CONFIG);
                $content = preg_replace(
                    "/'current'\s*=>\s*'[^']+'/",
                    "'current' => '{$version}'",
                    $content
                );
                file_put_contents(VERSION_CONFIG, $content);
            }

            // Run migrations via Laravel
            chdir(__DIR__);

            // Try shell_exec first
            if (function_exists('shell_exec')) {
                shell_exec('php artisan migrate --force 2>&1');
                shell_exec('php artisan config:clear 2>&1');
                shell_exec('php artisan route:clear 2>&1');
                shell_exec('php artisan view:clear 2>&1');
                shell_exec('php artisan cache:clear 2>&1');
            } else {
                // Fallback: bootstrap Laravel and run via Kernel
                require __DIR__ . '/vendor/autoload.php';
                $app = require_once __DIR__ . '/bootstrap/app.php';
                $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
                $kernel->call('migrate', ['--force' => true]);
                $kernel->call('config:clear');
                $kernel->call('route:clear');
                $kernel->call('view:clear');
                $kernel->call('cache:clear');
            }

            // Cleanup
            deleteRecursive($tempDir);
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }

            // Remove maintenance flag
            if (file_exists(__DIR__ . '/storage/framework/down')) {
                unlink(__DIR__ . '/storage/framework/down');
            }

            // Delete token
            deleteToken();

            echo json_encode([
                'success' => true,
                'message' => "Actualizacion a v{$version} completada.",
                'version' => $version
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Accion no valida.']);
    }

    exit;
}

function deleteRecursive(string $dir): void {
    if (!is_dir($dir)) return;

    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = "{$dir}/{$file}";
        is_dir($path) ? deleteRecursive($path) : unlink($path);
    }
    rmdir($dir);
}

// Generate token for this session
$token = generateToken();
$currentVersion = getCurrentVersion();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Pliza CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Actualizar Pliza CRM</h1>
                <p class="text-gray-600 mt-2">Version actual: <strong id="currentVersion"><?= htmlspecialchars($currentVersion) ?></strong></p>
            </div>

            <div id="alertContainer" class="mb-6 hidden"></div>

            <div class="space-y-4">
                <!-- Step 1: Check for updates -->
                <div class="border rounded-lg p-4" id="step1">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-800">1. Verificar Actualizaciones</h3>
                            <p class="text-sm text-gray-500">Consultar GitHub por nuevas versiones</p>
                        </div>
                        <button onclick="checkUpdates()" id="checkBtn" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            Verificar
                        </button>
                    </div>
                    <div id="checkResult" class="mt-4 hidden"></div>
                </div>

                <!-- Step 2: Download -->
                <div class="border rounded-lg p-4 opacity-50" id="step2">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-800">2. Descargar Actualizacion</h3>
                            <p class="text-sm text-gray-500">Descargar archivos de la nueva version</p>
                        </div>
                        <button onclick="downloadUpdate()" id="downloadBtn" class="px-4 py-2 bg-gray-400 text-white rounded-lg cursor-not-allowed" disabled>
                            Descargar
                        </button>
                    </div>
                    <div id="downloadResult" class="mt-4 hidden"></div>
                </div>

                <!-- Step 3: Backup -->
                <div class="border rounded-lg p-4 opacity-50" id="step3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-800">3. Crear Backup</h3>
                            <p class="text-sm text-gray-500">Respaldar configuracion actual</p>
                        </div>
                        <button onclick="createBackup()" id="backupBtn" class="px-4 py-2 bg-gray-400 text-white rounded-lg cursor-not-allowed" disabled>
                            Crear Backup
                        </button>
                    </div>
                    <div id="backupResult" class="mt-4 hidden"></div>
                </div>

                <!-- Step 4: Apply -->
                <div class="border rounded-lg p-4 opacity-50" id="step4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-800">4. Aplicar Actualizacion</h3>
                            <p class="text-sm text-gray-500">Instalar nueva version y ejecutar migraciones</p>
                        </div>
                        <button onclick="applyUpdate()" id="applyBtn" class="px-4 py-2 bg-gray-400 text-white rounded-lg cursor-not-allowed" disabled>
                            Actualizar
                        </button>
                    </div>
                    <div id="applyResult" class="mt-4 hidden"></div>
                </div>
            </div>

            <!-- Console -->
            <div class="mt-8">
                <h3 class="font-semibold text-gray-800 mb-2">Consola</h3>
                <div id="console" class="bg-gray-900 text-green-400 rounded-lg p-4 font-mono text-sm h-48 overflow-y-auto">
                    <div>[<?= date('H:i:s') ?>] Sistema listo para actualizar.</div>
                </div>
            </div>

            <div class="mt-6 text-center text-sm text-gray-500">
                <p>Este archivo se eliminara automaticamente despues de la actualizacion.</p>
            </div>
        </div>
    </div>

    <script>
        const token = '<?= $token ?>';
        let updateData = {};

        function log(message, isError = false) {
            const console = document.getElementById('console');
            const time = new Date().toLocaleTimeString('es-ES', { hour12: false });
            const color = isError ? 'text-red-400' : 'text-green-400';
            console.innerHTML += `<div class="${color}">[${time}] ${message}</div>`;
            console.scrollTop = console.scrollHeight;
        }

        function showAlert(message, type = 'info') {
            const container = document.getElementById('alertContainer');
            const colors = {
                success: 'bg-green-100 border-green-400 text-green-700',
                error: 'bg-red-100 border-red-400 text-red-700',
                info: 'bg-blue-100 border-blue-400 text-blue-700'
            };
            container.className = `mb-6 p-4 rounded-lg border ${colors[type]}`;
            container.textContent = message;
            container.classList.remove('hidden');
        }

        function enableStep(stepId) {
            const step = document.getElementById(stepId);
            step.classList.remove('opacity-50');
            const btn = step.querySelector('button');
            btn.disabled = false;
            btn.classList.remove('bg-gray-400', 'cursor-not-allowed');
            btn.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
        }

        async function checkUpdates() {
            const btn = document.getElementById('checkBtn');
            btn.disabled = true;
            btn.textContent = 'Verificando...';
            log('Consultando GitHub por actualizaciones...');

            try {
                const response = await fetch('update.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=check&token=${token}`
                });

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.message);
                }

                const resultDiv = document.getElementById('checkResult');
                resultDiv.classList.remove('hidden');

                if (data.update_available) {
                    log(`Nueva version encontrada: v${data.latest_version}`);
                    resultDiv.innerHTML = `
                        <div class="bg-green-50 border border-green-200 rounded p-3">
                            <p class="font-semibold text-green-800">Nueva version disponible: v${data.latest_version}</p>
                            ${data.changelog ? `<p class="text-sm text-green-600 mt-2">${data.changelog.substring(0, 200)}...</p>` : ''}
                        </div>
                    `;
                    updateData = data;
                    enableStep('step2');
                } else {
                    log('Ya tienes la ultima version.');
                    resultDiv.innerHTML = `
                        <div class="bg-gray-50 border border-gray-200 rounded p-3">
                            <p class="text-gray-700">Ya tienes la ultima version (v${data.current_version}).</p>
                        </div>
                    `;
                }
            } catch (error) {
                log('Error: ' + error.message, true);
                showAlert(error.message, 'error');
            }

            btn.disabled = false;
            btn.textContent = 'Verificar';
        }

        async function downloadUpdate() {
            if (!updateData.download_url) {
                log('No hay URL de descarga disponible.', true);
                return;
            }

            const btn = document.getElementById('downloadBtn');
            btn.disabled = true;
            btn.textContent = 'Descargando...';
            log(`Descargando v${updateData.latest_version}...`);

            try {
                const response = await fetch('update.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=download&token=${token}&download_url=${encodeURIComponent(updateData.download_url)}&version=${updateData.latest_version}`
                });

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.message);
                }

                const size = (data.size / 1024 / 1024).toFixed(2);
                log(`Descarga completada (${size} MB).`);

                const resultDiv = document.getElementById('downloadResult');
                resultDiv.classList.remove('hidden');
                resultDiv.innerHTML = `
                    <div class="bg-green-50 border border-green-200 rounded p-3">
                        <p class="text-green-800">Archivo descargado correctamente (${size} MB)</p>
                    </div>
                `;

                updateData.zip_path = data.path;
                enableStep('step3');
            } catch (error) {
                log('Error: ' + error.message, true);
                showAlert(error.message, 'error');
            }

            btn.disabled = false;
            btn.textContent = 'Descargar';
        }

        async function createBackup() {
            const btn = document.getElementById('backupBtn');
            btn.disabled = true;
            btn.textContent = 'Creando...';
            log('Creando backup de seguridad...');

            try {
                const response = await fetch('update.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=backup&token=${token}`
                });

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.message);
                }

                log('Backup creado correctamente.');

                const resultDiv = document.getElementById('backupResult');
                resultDiv.classList.remove('hidden');
                resultDiv.innerHTML = `
                    <div class="bg-green-50 border border-green-200 rounded p-3">
                        <p class="text-green-800">Backup creado correctamente</p>
                    </div>
                `;

                updateData.backup_path = data.path;
                enableStep('step4');
            } catch (error) {
                log('Error: ' + error.message, true);
                showAlert(error.message, 'error');
            }

            btn.disabled = false;
            btn.textContent = 'Crear Backup';
        }

        async function applyUpdate() {
            if (!confirm('¿Iniciar la actualizacion? El sistema entrara en modo mantenimiento temporalmente.')) {
                return;
            }

            const btn = document.getElementById('applyBtn');
            btn.disabled = true;
            btn.textContent = 'Actualizando...';
            log('Iniciando actualizacion...');
            log('Activando modo mantenimiento...');

            try {
                const response = await fetch('update.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=apply&token=${token}&zip_path=${encodeURIComponent(updateData.zip_path)}&version=${updateData.latest_version}&backup_path=${encodeURIComponent(updateData.backup_path || '')}`
                });

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.message);
                }

                log('Archivos actualizados.');
                log('Migraciones ejecutadas.');
                log('Caches limpiadas.');
                log('Modo mantenimiento desactivado.');
                log(`Actualizacion a v${data.version} completada exitosamente!`);

                const resultDiv = document.getElementById('applyResult');
                resultDiv.classList.remove('hidden');
                resultDiv.innerHTML = `
                    <div class="bg-green-50 border border-green-200 rounded p-3">
                        <p class="font-semibold text-green-800">Actualizacion completada!</p>
                        <p class="text-green-600 mt-1">Nueva version: v${data.version}</p>
                        <a href="/" class="inline-block mt-3 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                            Ir al Sistema
                        </a>
                    </div>
                `;

                document.getElementById('currentVersion').textContent = data.version;
                showAlert('Actualizacion completada exitosamente!', 'success');
            } catch (error) {
                log('Error: ' + error.message, true);
                showAlert('Error durante la actualizacion: ' + error.message, 'error');
            }

            btn.textContent = 'Actualizar';
        }
    </script>
</body>
</html>
