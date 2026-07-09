<?php
/**
 * Help Desk LAN - Response Wrapper
 */

namespace App\Core;

class Response {
    public function setStatusCode(int $code): void {
        http_response_code($code);
    }

    public function redirect(string $url): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        // Auto-prefix the base path if it's a root-relative path (starts with / but not //)
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
            $url = $basePath . $url;
        }

        header("Location: $url");
        exit;
    }

    public function json(array $data, int $statusCode = 200): void {
        $this->setStatusCode($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}
