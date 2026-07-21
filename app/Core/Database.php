<?php
/**
 * Mesa de Ayuda LAN - Conector Singleton a la Base de Datos PDO
 */

namespace App\Core;

use PDO;
use PDOException;
use Exception;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            // Verificar si las constantes de configuración ya están definidas, de lo contrario cargar archivo de configuración
            if (!defined('DB_HOST')) {
                $configFile = __DIR__ . '/../../config/config.php';
                if (!file_exists($configFile)) {
                    throw new Exception("El archivo de configuración no existe. Por favor ejecute install.php.");
                }
                require_once $configFile;
            }

            try {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                    PDO::ATTR_PERSISTENT => true
                ]);
            } catch (PDOException $e) {
                throw new Exception("Error de conexión a la base de datos: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
