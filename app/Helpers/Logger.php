<?php
/**
 * Help Desk LAN - System File Logger
 */

namespace App\Helpers;

class Logger {
    public static function log(string $message, string $level = 'INFO'): void {
        $logDir = defined('LOG_DIR') ? LOG_DIR : __DIR__ . '/../../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $date = date('Y-m-d');
        $time = date('H:i:s');
        $logFile = $logDir . "/app-{$date}.log";

        // Simple size-based rotation: limit daily log file to 5MB (LOG-01)
        if (file_exists($logFile) && filesize($logFile) > 5242880) {
            rename($logFile, $logDir . "/app-{$date}." . time() . ".log");
        }

        $formattedMessage = "[{$date} {$time}] [{$level}]: {$message}" . PHP_EOL;

        file_put_contents($logFile, $formattedMessage, FILE_APPEND);
    }

    public static function info(string $message): void {
        self::log($message, 'INFO');
    }

    public static function error(string $message): void {
        self::log($message, 'ERROR');
    }

    public static function debug(string $message): void {
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            self::log($message, 'DEBUG');
        }
    }
}
