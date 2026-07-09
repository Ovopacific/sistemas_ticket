<?php
/**
 * Help Desk LAN - Application Framework Router & Dispatcher
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
     * Resolves the request route and dispatches it.
     */
    public function run(): void {
        $method = $this->request->getMethod();
        $path = $this->request->getPath();

        // Check exact match first
        $callback = $this->routes[$method][$path] ?? null;

        $params = [];

        // Check pattern matches (e.g. /tickets/view/{id} where {id} matches a number or string)
        if (!$callback) {
            foreach ($this->routes[$method] ?? [] as $routePath => $routeCallback) {
                // Convert {param} to capture groups
                $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_\-]+)', $routePath);
                $pattern = '#^' . $pattern . '$#';

                if (preg_match($pattern, $path, $matches)) {
                    array_shift($matches); // remove full match
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

        // Instantiate and run controller action
        [$controllerClass, $action] = $callback;
        
        if (class_exists($controllerClass)) {
            $controllerInstance = new $controllerClass();
            if (method_exists($controllerInstance, $action)) {
                call_user_func_array([$controllerInstance, $action], array_merge([$this->request], $params));
                return;
            }
        }

        // Fallback internal error
        $this->response->setStatusCode(500);
        $controller = new Controller();
        $controller->render('errors/500', ['message' => "El controlador o acción '$controllerClass@$action' no pudo ser resuelto."], 'Error del Servidor');
    }
}
