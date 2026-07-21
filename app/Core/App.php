<?php
/**
 * Mesa de Ayuda LAN - Enrutador y Despachador de la Aplicación
 */

namespace App\Core;

use Exception;

class App {
    protected array $routes = [];
    protected Request $request;
    protected Response $response;

    public function __construct() {
        $this->request = new Request();
        $this->response = new Response();
    }

    public function get(string $path, array $callback): void {
        $this->routes['GET'][$path] = $callback;
    }

    public function post(string $path, array $callback): void {
        $this->routes['POST'][$path] = $callback;
    }

    /**
     * Resuelve la ruta de la petición HTTP y ejecuta el controlador correspondiente.
     */
    public function run(): void {
        $method = $this->request->getMethod();
        $path = $this->request->getPath();

        // Verificar coincidencia exacta de ruta primero
        $callback = $this->routes[$method][$path] ?? null;

        $params = [];

        // Verificar coincidencias por patrón dinámico (ej. /tickets/view/{id})
        if (!$callback) {
            foreach ($this->routes[$method] ?? [] as $routePath => $routeCallback) {
                // Convertir {param} a grupos de captura regex
                $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_\-]+)', $routePath);
                $pattern = '#^' . $pattern . '$#';

                if (preg_match($pattern, $path, $matches)) {
                    array_shift($matches); // Remover coincidencia completa
                    $params = $matches;
                    $callback = $routeCallback;
                    break;
                }
            }
        }

        if (!$callback) {
            $this->response->setStatusCode(404);
            $controller = new Controller();
            $controller->render('errors/404', [], 'Página No Encontrada');
            return;
        }

        // Instanciar y ejecutar la acción del controlador
        [$controllerClass, $action] = $callback;
        
        if (class_exists($controllerClass)) {
            $controllerInstance = new $controllerClass();
            if (method_exists($controllerInstance, $action)) {
                call_user_func_array([$controllerInstance, $action], array_merge([$this->request], $params));
                return;
            }
        }

        // Error interno de respaldo
        $this->response->setStatusCode(500);
        $controller = new Controller();
        $controller->render('errors/500', ['message' => "El controlador o acción '$controllerClass@$action' no pudo ser resuelto."], 'Error del Servidor');
    }
}
