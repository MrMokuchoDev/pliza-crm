<?php

/**
 * Gestor de conexión a base de datos
 */
class DatabaseManager
{
    /**
     * Probar conexión a la base de datos
     */
    public function testConnection(array $config): array
    {
        try {
            $dsn = $this->buildDsn($config);
            $pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => 5,
                ]
            );

            // Verificar que la base de datos existe y está accesible
            $pdo->query('SELECT 1');

            return [
                'success' => true,
                'message' => 'Conexión exitosa a la base de datos.',
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => $this->translateError($e),
            ];
        }
    }

    /**
     * Construir DSN según el driver
     */
    private function buildDsn(array $config): string
    {
        $driver = $config['driver'] ?? 'mysql';
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? '3306';
        $database = $config['database'] ?? '';

        switch ($driver) {
            case 'mysql':
                return "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

            case 'pgsql':
                return "pgsql:host={$host};port={$port};dbname={$database}";

            case 'sqlite':
                return "sqlite:{$database}";

            default:
                return "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
        }
    }

    /**
     * Traducir errores de PDO a mensajes amigables
     */
    private function translateError(PDOException $e): string
    {
        $code = $e->getCode();
        $message = $e->getMessage();

        // Errores comunes de MySQL
        $errors = [
            1044 => 'Acceso denegado a la base de datos. Verifica que el usuario tenga permisos.',
            1045 => 'Credenciales incorrectas. Verifica el usuario y contraseña.',
            1049 => 'La base de datos no existe. Créala primero desde cPanel o phpMyAdmin.',
            2002 => 'No se puede conectar al servidor. Verifica el host y puerto.',
            2003 => 'No se puede conectar al servidor MySQL. Verifica que esté corriendo.',
            2005 => 'Host desconocido. Verifica la dirección del servidor.',
            2006 => 'El servidor MySQL se ha desconectado.',
            2013 => 'Conexión perdida durante la consulta.',
        ];

        if (isset($errors[$code])) {
            return $errors[$code];
        }

        // Si es un error de conexión general
        if (strpos($message, 'Connection refused') !== false) {
            return 'Conexión rechazada. Verifica que MySQL esté corriendo y el puerto sea correcto.';
        }

        if (strpos($message, 'Access denied') !== false) {
            return 'Acceso denegado. Verifica las credenciales y permisos del usuario.';
        }

        if (strpos($message, 'Unknown database') !== false) {
            return 'La base de datos no existe. Debes crearla antes de continuar.';
        }

        // Mensaje genérico con detalle técnico
        return "Error de conexión: " . $message;
    }

    /**
     * Verificar si las tablas ya existen (para detectar reinstalación)
     */
    public function tablesExist(array $config): bool
    {
        try {
            $dsn = $this->buildDsn($config);
            $pdo = new PDO($dsn, $config['username'], $config['password']);

            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Obtener lista de tablas existentes
     */
    public function getTables(array $config): array
    {
        try {
            $dsn = $this->buildDsn($config);
            $pdo = new PDO($dsn, $config['username'], $config['password']);

            $stmt = $pdo->query("SHOW TABLES");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }
}
