<?php
/**
 * Help Desk LAN - DB Activity Auditor Helper
 */

namespace App\Helpers;

use App\Core\Database;
use Exception;

class Auditor {
    /**
     * Log a user activity in the database.
     */
    public static function log(?int $userId, string $action, string $details = null): void {
        try {
            $db = Database::getConnection();
            $ip = self::getIpAddress();
            
            $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, ip_address, details) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $action, $ip, $details]);
        } catch (Exception $e) {
            // Fail silently or log to file to not interrupt the user's flow
            Logger::error("No se pudo escribir en el log de auditoría: " . $e->getMessage());
        }
    }

    /**
     * Safely grabs the remote user's client IP.
     */
    private static function getIpAddress(): string {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Can be a comma-separated list of IPs
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
