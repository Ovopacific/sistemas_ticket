<?php
/**
 * Mesa de Ayuda LAN - Controlador Base
 */

namespace App\Core;

class Controller {
    protected Session $session;
    protected Response $response;

    public function __construct() {
        $this->session = new Session();
        $this->response = new Response();
    }

    /**
     * Renderiza una vista dentro de las plantillas principales (encabezado, barra lateral y pie de página).
     */
    public function render(string $view, array $data = [], string $title = 'Mesa de Ayuda'): void {
        // Extraer los datos del arreglo como variables individuales
        extract($data);

        // Obtener la configuración visual del sistema (marca, colores y logo)
        $appConfig = $this->getAppBranding();
        $theme_color = $appConfig['theme_color'] ?? '#0d6efd';
        $company_name = $appConfig['company_name'] ?? 'Mesa de Ayuda Corp';
        $company_logo = $appConfig['company_logo'] ?? '';

        // Iniciar el búfer de salida para la vista principal
        ob_start();
        $viewPath = __DIR__ . "/../../views/{$view}.php";
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo "<div class='alert alert-danger'>La vista '{$view}' no existe en el sistema.</div>";
        }
        $content = ob_get_clean();

        // Obtener las notificaciones no leídas si el usuario ha iniciado sesión
        $currentUser = $this->session->get('user');
        $notifications = [];
        if ($currentUser) {
            $notifications = $this->getUnreadNotifications($currentUser['id']);
        }

        // Renderizar la estructura general del sitio
        require __DIR__ . '/../../views/layouts/header.php';
        if ($currentUser) {
            require __DIR__ . '/../../views/layouts/sidebar.php';
        }
        echo $content;
        require __DIR__ . '/../../views/layouts/footer.php';
    }

    /**
     * Verifica si la petición actual está autorizada para los roles especificados.
     */
    protected function authorize(array $allowedRoles = []): void {
        $user = $this->session->get('user');
        \App\Helpers\Logger::info("Intento de autorizacion en controlador. Session ID: " . session_id() . ", Usuario en sesion: " . var_export($user, true));

        if (!$user) {
            $this->session->setFlash('error', 'Por favor inicie sesión para acceder.');
            $this->response->redirect('/login');
        }

        if ($user['status'] !== 'active') {
            $this->session->destroy();
            $this->session->setFlash('error', 'Su cuenta de usuario está desactivada.');
            $this->response->redirect('/login');
        }

        if (!empty($allowedRoles) && !in_array($user['role'], $allowedRoles, true)) {
            $this->session->setFlash('error', 'Acceso denegado. No tiene permisos suficientes.');
            $this->response->redirect($user['role'] === 'user' ? '/my-tickets' : '/dashboard');
        }
    }

    /**
     * Obtiene de forma segura los ajustes de marca e identidad visual de la aplicación.
     * El resultado se almacena en caché de sesión durante 5 minutos para optimizar el rendimiento (PERF-02).
     */
    private function getAppBranding(): array {
        // Utilizar versión en caché si está disponible y tiene menos de 5 minutos
        $cached    = $_SESSION['_app_branding_cache']    ?? null;
        $cachedAt  = $_SESSION['_app_branding_cache_ts'] ?? 0;

        if ($cached !== null && (time() - $cachedAt) < 300) {
            return $cached;
        }

        try {
            $db   = Database::getConnection();
            $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
            $rows = $stmt->fetchAll();
            $config = [];
            foreach ($rows as $row) {
                $config[$row['setting_key']] = $row['setting_value'];
            }
            // Almacenar en sesión junto con la marca de tiempo
            $_SESSION['_app_branding_cache']    = $config;
            $_SESSION['_app_branding_cache_ts'] = time();
            return $config;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obtiene las notificaciones recientes no leídas para el indicador del menú superior.
     */
    private function getUnreadNotifications(int $userId): array {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT n.*, t.ticket_number 
                                  FROM notifications n 
                                  LEFT JOIN tickets t ON n.ticket_id = t.id 
                                  WHERE n.user_id = ? AND n.is_read = 0 
                                  ORDER BY n.created_at DESC 
                                  LIMIT 5");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }
}
