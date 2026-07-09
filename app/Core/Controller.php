<?php
/**
 * Help Desk LAN - Base Controller
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
     * Renders a view inside the standard template wrapper.
     */
    public function render(string $view, array $data = [], string $title = 'Mesa de Ayuda'): void {
        // Extract data values as variables
        extract($data);

        // Fetch settings from DB if available for logo/colors
        $appConfig = $this->getAppBranding();
        $theme_color = $appConfig['theme_color'] ?? '#0d6efd';
        $company_name = $appConfig['company_name'] ?? 'Mesa de Ayuda Corp';
        $company_logo = $appConfig['company_logo'] ?? '';

        // Start buffering main view
        ob_start();
        $viewPath = __DIR__ . "/../../views/{$view}.php";
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo "<div class='alert alert-danger'>La vista '{$view}' no existe en el sistema.</div>";
        }
        $content = ob_get_clean();

        // Get notifications if logged in
        $currentUser = $this->session->get('user');
        $notifications = [];
        if ($currentUser) {
            $notifications = $this->getUnreadNotifications($currentUser['id']);
        }

        // Render overall layout
        require __DIR__ . '/../../views/layouts/header.php';
        if ($currentUser) {
            require __DIR__ . '/../../views/layouts/sidebar.php';
        }
        echo $content;
        require __DIR__ . '/../../views/layouts/footer.php';
    }

    /**
     * Checks if current request is authorized under given roles.
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
     * Fetches application settings branding safely.
     */
    private function getAppBranding(): array {
        try {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
            $rows = $stmt->fetchAll();
            $config = [];
            foreach ($rows as $row) {
                $config[$row['setting_key']] = $row['setting_value'];
            }
            return $config;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Fetch recent unread notifications for navbar indicator.
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
