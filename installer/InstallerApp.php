<?php

/**
 * MiniCRM Web Installer
 *
 * Aplicación independiente en PHP puro para instalar MiniCRM
 * sin necesidad de que Laravel esté configurado.
 */

class InstallerApp
{
    private string $basePath;
    private string $step;
    private array $data = [];
    private array $errors = [];
    private array $session = [];

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->initSession();
        $this->step = $_GET['step'] ?? 'welcome';
    }

    /**
     * Inicializar sesión de forma segura
     */
    private function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Configurar sesión segura
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                ini_set('session.cookie_secure', 1);
            }
            session_start();
        }

        if (!isset($_SESSION['installer'])) {
            $_SESSION['installer'] = [
                'started_at' => time(),
                'completed_steps' => [],
                'data' => []
            ];
        }

        $this->session = &$_SESSION['installer'];
    }

    /**
     * Ejecutar el instalador
     */
    public function run(): void
    {
        // Verificar si ya está instalado
        if ($this->isInstalled() && $this->step !== 'complete') {
            $this->redirect('?step=already-installed');
            return;
        }

        // Procesar POST si existe
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost();
            return;
        }

        // Renderizar step actual
        $this->render();
    }

    /**
     * Verificar si la aplicación ya está instalada
     */
    public function isInstalled(): bool
    {
        $envPath = $this->basePath . '/.env';

        if (!file_exists($envPath)) {
            return false;
        }

        $envContent = file_get_contents($envPath);

        // Verificar que tiene APP_KEY configurado
        if (!preg_match('/^APP_KEY=base64:.+$/m', $envContent)) {
            return false;
        }

        // Verificar que existe el archivo de lock de instalación
        $lockFile = $this->basePath . '/storage/installed.lock';

        return file_exists($lockFile);
    }

    /**
     * Manejar peticiones POST
     */
    private function handlePost(): void
    {
        $action = $_POST['action'] ?? '';

        switch ($this->step) {
            case 'requirements':
                $this->handleRequirements();
                break;
            case 'database':
                $this->handleDatabase();
                break;
            case 'application':
                $this->handleApplication();
                break;
            case 'admin':
                $this->handleAdmin();
                break;
            case 'install':
                $this->handleInstall();
                break;
        }
    }

    /**
     * Manejar paso de requisitos
     */
    private function handleRequirements(): void
    {
        $checker = new RequirementsChecker($this->basePath);

        if ($checker->checkAll()) {
            $this->markStepComplete('requirements');
            $this->redirect('?step=database');
        } else {
            $this->errors = $checker->getErrors();
            $this->render();
        }
    }

    /**
     * Manejar paso de base de datos
     */
    private function handleDatabase(): void
    {
        $config = [
            'driver' => $_POST['db_driver'] ?? 'mysql',
            'host' => $_POST['db_host'] ?? 'localhost',
            'port' => $_POST['db_port'] ?? '3306',
            'database' => $_POST['db_database'] ?? '',
            'username' => $_POST['db_username'] ?? '',
            'password' => $_POST['db_password'] ?? '',
        ];

        // Validar campos requeridos
        if (empty($config['database']) || empty($config['username'])) {
            $this->errors[] = 'La base de datos y el usuario son requeridos.';
            $this->data = $config;
            $this->render();
            return;
        }

        // Probar conexión
        $dbManager = new DatabaseManager();
        $result = $dbManager->testConnection($config);

        if ($result['success']) {
            $this->session['data']['database'] = $config;
            $this->markStepComplete('database');
            $this->redirect('?step=application');
        } else {
            $this->errors[] = $result['message'];
            $this->data = $config;
            $this->render();
        }
    }

    /**
     * Manejar paso de configuración de aplicación
     */
    private function handleApplication(): void
    {
        $config = [
            'app_name' => $_POST['app_name'] ?? 'MiniCRM',
            'app_url' => rtrim($_POST['app_url'] ?? '', '/'),
            'app_timezone' => $_POST['app_timezone'] ?? 'America/Bogota',
            'app_locale' => $_POST['app_locale'] ?? 'es',
            'app_env' => $_POST['app_env'] ?? 'production',
        ];

        // Validar URL
        if (empty($config['app_url']) || !filter_var($config['app_url'], FILTER_VALIDATE_URL)) {
            $this->errors[] = 'La URL de la aplicación no es válida.';
            $this->data = $config;
            $this->render();
            return;
        }

        $this->session['data']['application'] = $config;
        $this->markStepComplete('application');
        $this->redirect('?step=admin');
    }

    /**
     * Manejar paso de creación de admin
     */
    private function handleAdmin(): void
    {
        $config = [
            'name' => $_POST['admin_name'] ?? '',
            'email' => $_POST['admin_email'] ?? '',
            'password' => $_POST['admin_password'] ?? '',
            'password_confirmation' => $_POST['admin_password_confirmation'] ?? '',
        ];

        // Validaciones
        if (empty($config['name']) || empty($config['email']) || empty($config['password'])) {
            $this->errors[] = 'Todos los campos son requeridos.';
            $this->data = $config;
            $this->render();
            return;
        }

        if (!filter_var($config['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'El email no es válido.';
            $this->data = $config;
            $this->render();
            return;
        }

        if (strlen($config['password']) < 8) {
            $this->errors[] = 'La contraseña debe tener al menos 8 caracteres.';
            $this->data = $config;
            $this->render();
            return;
        }

        if ($config['password'] !== $config['password_confirmation']) {
            $this->errors[] = 'Las contraseñas no coinciden.';
            $this->data = $config;
            $this->render();
            return;
        }

        $this->session['data']['admin'] = $config;
        $this->markStepComplete('admin');
        $this->redirect('?step=install');
    }

    /**
     * Manejar proceso de instalación
     */
    private function handleInstall(): void
    {
        // Capturar cualquier output previo (errores, warnings, etc.)
        ob_start();

        try {
            $installer = new InstallProcess($this->basePath, $this->session['data']);
            $result = $installer->run();

            if ($result['success']) {
                // Crear archivo de lock
                file_put_contents(
                    $this->basePath . '/storage/installed.lock',
                    json_encode([
                        'installed_at' => date('Y-m-d H:i:s'),
                        'version' => $this->getVersion(),
                        'php_version' => PHP_VERSION,
                    ])
                );

                // Limpiar sesión del instalador
                unset($_SESSION['installer']);
            }
        } catch (Throwable $e) {
            $result = [
                'success' => false,
                'message' => 'Error crítico: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
        }

        // Capturar cualquier output previo
        $previousOutput = ob_get_clean();

        // Si hubo output previo, incluirlo en el error
        if (!empty($previousOutput) && !$result['success']) {
            $result['debug_output'] = $previousOutput;
        } elseif (!empty($previousOutput)) {
            // Si hubo output pero la instalación fue exitosa, guardarlo en logs
            error_log('Installer output: ' . $previousOutput);
        }

        // Limpiar cualquier output buffer restante
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Enviar respuesta JSON
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    /**
     * Marcar paso como completado
     */
    private function markStepComplete(string $step): void
    {
        if (!in_array($step, $this->session['completed_steps'])) {
            $this->session['completed_steps'][] = $step;
        }
    }

    /**
     * Verificar si un paso está completado
     */
    public function isStepComplete(string $step): bool
    {
        return in_array($step, $this->session['completed_steps']);
    }

    /**
     * Obtener datos guardados de un paso
     */
    public function getStepData(string $step): array
    {
        return $this->session['data'][$step] ?? [];
    }

    /**
     * Renderizar vista actual
     */
    private function render(): void
    {
        $step = $this->step;
        $viewFile = __DIR__ . "/views/{$step}.php";

        if (!file_exists($viewFile)) {
            $viewFile = __DIR__ . '/views/welcome.php';
            $step = 'welcome';
        }

        // Variables disponibles en la vista
        $installer = $this;
        $basePath = $this->basePath;
        $errors = $this->errors;
        $data = $this->data;
        $version = $this->getVersion();

        // Cargar helpers según el paso
        if ($step === 'requirements') {
            require_once __DIR__ . '/helpers/RequirementsChecker.php';
            $checker = new RequirementsChecker($basePath);
            $requirements = $checker->getResults();
        }

        // Incluir layout con la vista
        include __DIR__ . '/views/layout.php';
    }

    /**
     * Obtener versión de la aplicación
     */
    public function getVersion(): string
    {
        $composerJson = $this->basePath . '/composer.json';

        if (file_exists($composerJson)) {
            $composer = json_decode(file_get_contents($composerJson), true);
            return $composer['version'] ?? '1.0.0';
        }

        return '1.0.0';
    }

    /**
     * Redireccionar
     */
    private function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    /**
     * Obtener paso actual
     */
    public function getStep(): string
    {
        return $this->step;
    }

    /**
     * Obtener lista de pasos
     */
    public function getSteps(): array
    {
        return [
            'welcome' => 'Bienvenida',
            'requirements' => 'Requisitos',
            'database' => 'Base de Datos',
            'application' => 'Aplicación',
            'admin' => 'Administrador',
            'install' => 'Instalar',
        ];
    }

    /**
     * Obtener número del paso actual
     */
    public function getStepNumber(): int
    {
        $steps = array_keys($this->getSteps());
        $index = array_search($this->step, $steps);
        return $index !== false ? $index + 1 : 1;
    }

    /**
     * Obtener total de pasos
     */
    public function getTotalSteps(): int
    {
        return count($this->getSteps());
    }
}
