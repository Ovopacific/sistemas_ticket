<?php
/**
 * Help Desk LAN - Session & CSRF Manager
 */

namespace App\Core;

class Session {
    protected const FLASH_KEY = 'flash_messages';

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            // Configure a local sessions path in the project to guarantee write permissions on Windows
            $sessionPath = __DIR__ . '/../../sessions';
            if (!is_dir($sessionPath)) {
                mkdir($sessionPath, 0755, true);
            }
            session_save_path($sessionPath);

            // Secure cookie session configurations
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => false, // Set to false to allow HTTP on localhost
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            
            session_start();
        }

        // Initialize flash messages lifecycle (only for main page requests, not asset 404s/favicons)
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $isAsset = preg_match('/\.(ico|map|css|js|png|jpg|jpeg|gif|svg|woff2?|json)$/i', $uri);
        
        if (!$isAsset) {
            $flashMessages = $_SESSION[self::FLASH_KEY] ?? [];
            foreach ($flashMessages as $key => &$flashMessage) {
                $flashMessage['remove'] = true;
            }
            $_SESSION[self::FLASH_KEY] = $flashMessages;
        }
    }

    public function set(string $key, $value): void {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public function remove(string $key): void {
        unset($_SESSION[$key]);
    }

    public function destroy(): void {
        session_destroy();
        $_SESSION = [];
    }

    // Flash Messages
    public function setFlash(string $key, string $message): void {
        $_SESSION[self::FLASH_KEY][$key] = [
            'remove' => false,
            'value' => $message
        ];
    }

    public function getFlash(string $key): ?string {
        return $_SESSION[self::FLASH_KEY][$key]['value'] ?? null;
    }

    public function hasFlash(string $key): bool {
        return isset($_SESSION[self::FLASH_KEY][$key]);
    }

    // CSRF Management
    public function getCsrfToken(): string {
        $token = $this->get('csrf_token');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            $this->set('csrf_token', $token);
        }
        return $token;
    }

    public function validateCsrfToken(?string $token): bool {
        $stored = $this->get('csrf_token');
        if (!$stored || !$token) {
            return false;
        }
        return hash_equals($stored, $token);
    }

    // Clean up expired flash messages
    public function __destruct() {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $isAsset = preg_match('/\.(ico|map|css|js|png|jpg|jpeg|gif|svg|woff2?|json)$/i', $uri);

        if (!$isAsset) {
            $flashMessages = $_SESSION[self::FLASH_KEY] ?? [];
            foreach ($flashMessages as $key => $flashMessage) {
                if ($flashMessage['remove']) {
                    unset($flashMessages[$key]);
                }
            }
            $_SESSION[self::FLASH_KEY] = $flashMessages;
        }
    }
}
