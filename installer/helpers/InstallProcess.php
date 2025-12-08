<?php

/**
 * Proceso de instalación
 */
class InstallProcess
{
    private string $basePath;
    private array $config;
    private array $steps = [];
    private array $errors = [];
    private static $laravelApp = null;
    private static $laravelKernel = null;

    public function __construct(string $basePath, array $config)
    {
        $this->basePath = $basePath;
        $this->config = $config;
    }

    /**
     * Ejecutar instalación completa
     */
    public function run(): array
    {
        $steps = [
            'env' => 'Creando archivo de configuración',
            'key' => 'Generando clave de aplicación',
            'database' => 'Verificando conexión a base de datos',
            'migrations' => 'Ejecutando migraciones',
            'seeders' => 'Creando datos iniciales',
            'admin' => 'Creando usuario administrador',
            'storage' => 'Configurando almacenamiento',
            'cache' => 'Limpiando caché',
            'optimize' => 'Optimizando aplicación',
        ];

        $results = [];

        foreach ($steps as $step => $description) {
            $results[$step] = [
                'description' => $description,
                'status' => 'pending',
            ];
        }

        try {
            // Paso 1: Crear .env
            $results['env']['status'] = 'running';
            $this->createEnvFile();
            $results['env']['status'] = 'completed';

            // Paso 2: Generar APP_KEY
            $results['key']['status'] = 'running';
            $this->generateAppKey();
            $results['key']['status'] = 'completed';

            // Paso 3: Verificar conexión
            $results['database']['status'] = 'running';
            $this->verifyDatabase();
            $results['database']['status'] = 'completed';

            // Paso 4: Ejecutar migraciones
            $results['migrations']['status'] = 'running';
            $this->runMigrations();
            $results['migrations']['status'] = 'completed';

            // Paso 5: Ejecutar seeders
            $results['seeders']['status'] = 'running';
            $this->runSeeders();
            $results['seeders']['status'] = 'completed';

            // Paso 6: Crear admin
            $results['admin']['status'] = 'running';
            $this->createAdmin();
            $results['admin']['status'] = 'completed';

            // Paso 7: Storage link
            $results['storage']['status'] = 'running';
            $this->createStorageLink();
            $results['storage']['status'] = 'completed';

            // Paso 8: Limpiar caché
            $results['cache']['status'] = 'running';
            $this->clearCache();
            $results['cache']['status'] = 'completed';

            // Paso 9: Optimizar
            $results['optimize']['status'] = 'running';
            $this->optimize();
            $results['optimize']['status'] = 'completed';

            return [
                'success' => true,
                'message' => 'Instalación completada exitosamente.',
                'steps' => $results,
                'admin_email' => $this->config['admin']['email'] ?? '',
            ];

        } catch (Exception $e) {
            // Marcar paso actual como fallido
            foreach ($results as $step => &$result) {
                if ($result['status'] === 'running') {
                    $result['status'] = 'failed';
                    $result['error'] = $e->getMessage();
                }
            }

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'steps' => $results,
            ];
        }
    }

    /**
     * Crear archivo .env
     */
    private function createEnvFile(): void
    {
        require_once __DIR__ . '/EnvManager.php';

        $envManager = new EnvManager($this->basePath);

        // Combinar configuración de BD y aplicación
        $envConfig = array_merge(
            [
                'db_driver' => $this->config['database']['driver'] ?? 'mysql',
                'db_host' => $this->config['database']['host'] ?? 'localhost',
                'db_port' => $this->config['database']['port'] ?? '3306',
                'db_database' => $this->config['database']['database'] ?? '',
                'db_username' => $this->config['database']['username'] ?? '',
                'db_password' => $this->config['database']['password'] ?? '',
            ],
            $this->config['application'] ?? []
        );

        if (!$envManager->createEnvFile($envConfig)) {
            throw new Exception('No se pudo crear el archivo .env');
        }
    }

    /**
     * Generar APP_KEY
     */
    private function generateAppKey(): void
    {
        require_once __DIR__ . '/EnvManager.php';

        $envManager = new EnvManager($this->basePath);
        $key = $envManager->generateAppKey();

        if (empty($key)) {
            throw new Exception('No se pudo generar la clave de aplicación.');
        }
    }

    /**
     * Verificar conexión a BD
     */
    private function verifyDatabase(): void
    {
        require_once __DIR__ . '/DatabaseManager.php';

        $dbManager = new DatabaseManager();
        $result = $dbManager->testConnection($this->config['database']);

        if (!$result['success']) {
            throw new Exception($result['message']);
        }
    }

    /**
     * Ejecutar migraciones usando Laravel
     */
    private function runMigrations(): void
    {
        $output = $this->runArtisan('migrate', ['--force' => true]);

        if (strpos($output, 'ERROR') !== false || strpos($output, 'Exception') !== false) {
            throw new Exception('Error en migraciones: ' . $output);
        }
    }

    /**
     * Ejecutar seeders
     */
    private function runSeeders(): void
    {
        // Solo ejecutar SalePhaseSeeder para datos esenciales
        $output = $this->runArtisan('db:seed', [
            '--class' => 'Database\\Seeders\\SalePhaseSeeder',
            '--force' => true
        ]);

        if (strpos($output, 'ERROR') !== false || strpos($output, 'Exception') !== false) {
            throw new Exception('Error en seeders: ' . $output);
        }
    }

    /**
     * Crear usuario administrador
     */
    private function createAdmin(): void
    {
        $admin = $this->config['admin'] ?? [];

        if (empty($admin['email']) || empty($admin['password'])) {
            throw new Exception('Datos del administrador incompletos.');
        }

        // Inicializar Laravel si no está cargado
        $this->bootLaravel();

        // Crear usuario usando Eloquent
        $userClass = 'App\\Models\\User';

        if (!class_exists($userClass)) {
            throw new Exception('Clase User no encontrada.');
        }

        // Verificar si ya existe
        $existing = $userClass::where('email', $admin['email'])->first();

        if ($existing) {
            // Actualizar password si existe
            $existing->password = password_hash($admin['password'], PASSWORD_BCRYPT);
            $existing->name = $admin['name'];
            $existing->save();
        } else {
            // Crear nuevo
            $user = new $userClass();
            $user->name = $admin['name'];
            $user->email = $admin['email'];
            $user->password = password_hash($admin['password'], PASSWORD_BCRYPT);
            $user->email_verified_at = now();
            $user->save();
        }
    }

    /**
     * Inicializar Laravel (una sola vez)
     */
    private function bootLaravel(): void
    {
        if (self::$laravelApp !== null) {
            return;
        }

        // Cargar autoloader
        require_once $this->basePath . '/vendor/autoload.php';

        // Cargar la aplicación Laravel
        self::$laravelApp = require $this->basePath . '/bootstrap/app.php';
        self::$laravelKernel = self::$laravelApp->make(Illuminate\Contracts\Console\Kernel::class);
        self::$laravelKernel->bootstrap();
    }

    /**
     * Crear storage link
     */
    private function createStorageLink(): void
    {
        $target = $this->basePath . '/storage/app/public';
        $link = $this->basePath . '/public/storage';

        // Si ya existe, no hacer nada
        if (file_exists($link) || is_link($link)) {
            return;
        }

        // En Windows usar junction, en Linux/Mac usar symlink
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec("mklink /J \"{$link}\" \"{$target}\"", $output, $return);
        } else {
            if (!@symlink($target, $link)) {
                // Alternativa: copiar en lugar de symlink (hosting compartido)
                $this->copyDirectory($target, $link);
            }
        }
    }

    /**
     * Copiar directorio (alternativa a symlink)
     */
    private function copyDirectory(string $source, string $dest): void
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $srcPath = $source . '/' . $file;
                $destPath = $dest . '/' . $file;

                if (is_dir($srcPath)) {
                    $this->copyDirectory($srcPath, $destPath);
                } else {
                    copy($srcPath, $destPath);
                }
            }
        }
        closedir($dir);
    }

    /**
     * Limpiar caché
     */
    private function clearCache(): void
    {
        $commands = [
            'cache:clear',
            'config:clear',
            'route:clear',
            'view:clear',
        ];

        foreach ($commands as $command) {
            $this->runArtisan($command);
        }
    }

    /**
     * Optimizar aplicación
     */
    private function optimize(): void
    {
        $this->runArtisan('config:cache');
        $this->runArtisan('route:cache');
        $this->runArtisan('view:cache');
    }

    /**
     * Ejecutar comando artisan
     * Usa shell_exec si está disponible, sino usa el Kernel de Laravel directamente
     */
    private function runArtisan(string $command, array $params = []): string
    {
        // Verificar si shell_exec está disponible
        if ($this->isShellExecAvailable()) {
            return $this->runArtisanViaShell($command, $params);
        }

        // Fallback: usar Kernel de Laravel directamente
        return $this->runArtisanViaKernel($command, $params);
    }

    /**
     * Verificar si shell_exec está disponible
     */
    private function isShellExecAvailable(): bool
    {
        // Verificar si la función existe
        if (!function_exists('shell_exec')) {
            return false;
        }

        // Verificar si está en la lista de funciones deshabilitadas
        $disabled = ini_get('disable_functions');
        if (!empty($disabled)) {
            $disabledFunctions = array_map('trim', explode(',', $disabled));
            if (in_array('shell_exec', $disabledFunctions)) {
                return false;
            }
        }

        // Verificar safe_mode (PHP < 8.0)
        if (ini_get('safe_mode')) {
            return false;
        }

        return true;
    }

    /**
     * Ejecutar artisan via shell_exec
     */
    private function runArtisanViaShell(string $command, array $params = []): string
    {
        // Construir parámetros
        $paramStr = '';
        foreach ($params as $key => $value) {
            if ($value === true) {
                $paramStr .= " {$key}";
            } elseif ($value !== false) {
                $paramStr .= " {$key}=\"{$value}\"";
            }
        }

        $fullCommand = "cd \"{$this->basePath}\" && php artisan {$command}{$paramStr} 2>&1";

        $output = shell_exec($fullCommand);

        return $output ?? '';
    }

    /**
     * Ejecutar artisan via Kernel de Laravel (fallback para hosting compartido)
     */
    private function runArtisanViaKernel(string $command, array $params = []): string
    {
        // Inicializar Laravel si no está cargado
        $this->bootLaravel();

        // Preparar argumentos para Artisan (sin incluir 'command' como clave)
        $arguments = [];

        foreach ($params as $key => $value) {
            if ($value === true) {
                $arguments[$key] = true;
            } elseif ($value !== false) {
                $arguments[$key] = $value;
            }
        }

        // Crear output buffer para capturar la salida
        $outputBuffer = new Symfony\Component\Console\Output\BufferedOutput();

        try {
            $exitCode = self::$laravelKernel->call($command, $arguments, $outputBuffer);
            $output = $outputBuffer->fetch();

            if ($exitCode !== 0) {
                return "ERROR (exit code {$exitCode}): " . $output;
            }

            return $output;
        } catch (Exception $e) {
            return "Exception: " . $e->getMessage();
        }
    }

    /**
     * Ejecutar paso individual (para AJAX)
     */
    public function runStep(string $step): array
    {
        try {
            switch ($step) {
                case 'env':
                    $this->createEnvFile();
                    break;
                case 'key':
                    $this->generateAppKey();
                    break;
                case 'database':
                    $this->verifyDatabase();
                    break;
                case 'migrations':
                    $this->runMigrations();
                    break;
                case 'seeders':
                    $this->runSeeders();
                    break;
                case 'admin':
                    $this->createAdmin();
                    break;
                case 'storage':
                    $this->createStorageLink();
                    break;
                case 'cache':
                    $this->clearCache();
                    break;
                case 'optimize':
                    $this->optimize();
                    break;
                default:
                    throw new Exception("Paso desconocido: {$step}");
            }

            return ['success' => true];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
