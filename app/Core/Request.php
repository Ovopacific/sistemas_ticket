<?php
/**
 * Mesa de Ayuda LAN - Envoltorio de Peticiones HTTP (Request)
 */

namespace App\Core;

class Request {
    public function getMethod(): string {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function getPath(): string {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }
        
        // Remover la ruta de subcarpeta si está alojado en un subdirectorio
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $baseDir = dirname($scriptName);
        if ($baseDir !== '/' && $baseDir !== '\\') {
            $baseDir = str_replace('\\', '/', $baseDir);
            if (str_starts_with($path, $baseDir)) {
                $path = substr($path, strlen($baseDir));
            }
        }
        
        return '/' . trim($path, '/');
    }

    public function get(string $key, $default = null) {
        return $_GET[$key] ?? $default;
    }

    public function post(string $key, $default = null) {
        return $_POST[$key] ?? $default;
    }

    public function getBody(): array {
        $body = [];
        if ($this->getMethod() === 'GET') {
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        if ($this->getMethod() === 'POST') {
            // Verificar si el cuerpo viene en formato JSON
            $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
            if (str_contains($contentType, 'application/json')) {
                $rawInput = file_get_contents('php://input');
                $decoded = json_decode($rawInput, true);
                return is_array($decoded) ? $decoded : [];
            }

            foreach ($_POST as $key => $value) {
                if (is_array($value)) {
                    $body[$key] = filter_var_array($value, FILTER_SANITIZE_SPECIAL_CHARS);
                } else {
                    $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                }
            }
        }
        return $body;
    }

    public function isAjax(): bool {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
            || (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json'));
    }

    public function file(string $key) {
        return $_FILES[$key] ?? null;
    }
}
