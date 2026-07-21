<?php
/**
 * Mesa de Ayuda LAN - Gestor de Sesiones y Protección CSRF
 */

namespace App\Core;

class Session {
    protected const FLASH_KEY = 'flash_messages';

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            // Configurar directorio de sesiones local en el proyecto para garantizar permisos de escritura en Windows
            $sessionPath = __DIR__ . '/../../sessions';
            if (!is_dir($sessionPath)) {
                mkdir($sessionPath, 0755, true);
            }
            session_save_path($sessionPath);

            // Detectar HTTPS automáticamente: cookie segura en producción, normal en desarrollo local (SEC-08)
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
                    || (($_SERVER['SERVER_PORT'] ?? 80) == 443);

            // Configuración segura de la cookie de sesión
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $isHttps, // true en HTTPS (producción), false en HTTP (localhost)
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            
            session_start();

            // Tiempo límite de inactividad: expira la sesión tras 30 minutos de inactividad (AUTH-03)
            $inactivityLimit = 1800; // 30 minutos en segundos
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            $isAsset = (bool)preg_match('/\.(ico|map|css|js|png|jpg|jpeg|gif|svg|woff2?|json)$/i', $uri);
            if (!$isAsset && isset($_SESSION['_last_activity'])) {
                if ((time() - $_SESSION['_last_activity']) > $inactivityLimit) {
                    // La sesión ha expirado por inactividad
                    session_unset();
                    session_destroy();
                    session_start();
                }
            }
            if (!$isAsset) {
                $_SESSION['_last_activity'] = time();
            }
        }

        // Inicializar el ciclo de vida de los mensajes flash (solo para peticiones de páginas, no para recursos estáticos)
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

    // Mensajes Flash (Mensajes temporales entre redirecciones)
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

    // Gestión de Tokens CSRF
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

    // Limpieza de mensajes flash expirados al destruir la instancia
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
