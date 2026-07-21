<?php
/**
 * Mesa de Ayuda LAN - Cargador Seguro de Archivos
 */

namespace App\Helpers;

use App\Core\Database;
use App\Helpers\Logger;
use Exception;

class FileUploader {
    /**
     * Sube un archivo de forma segura comprobando extensiones, tamaño y sanitizando nombres.
     * Retorna un arreglo con ['status' => true, 'path' => 'ruta/relativa', 'filename' => 'nombre_original'] o arreglo de error.
     */
    public static function upload(array $file, string $customSubFolder = 'attachments'): array {
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['status' => false, 'error' => 'Parámetros de archivo inválidos.'];
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return ['status' => false, 'error' => 'No se subió ningún archivo.'];
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['status' => false, 'error' => 'El tamaño del archivo excede el límite del servidor.'];
            default:
                return ['status' => false, 'error' => 'Error desconocido en la subida del archivo.'];
        }

        // Obtener límites configurados en la base de datos
        $settings = self::getUploadSettings();
        $maxSize = (int)($settings['max_upload_size'] ?? 10485760); // 10MB por defecto
        $allowedExts = explode(',', strtolower($settings['allowed_extensions'] ?? 'pdf,doc,docx,xls,xlsx,png,jpg,jpeg,zip,rar,txt,log'));

        if ($file['size'] > $maxSize) {
            $readableSize = round($maxSize / 1024 / 1024, 2) . 'MB';
            return ['status' => false, 'error' => "El tamaño del archivo excede el límite permitido de {$readableSize}."];
        }

        $filename = basename($file['name']);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExts, true)) {
            return ['status' => false, 'error' => 'Extensión de archivo no permitida.'];
        }

        // Validar tipo MIME real del archivo para prevenir archivos maliciosos camuflados (SEC-05)
        // Solo activo si la extensión finfo está disponible
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $realMime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            // Bloquear tipos MIME potencialmente peligrosos
            $blockedMimes = ['text/x-php', 'application/x-php', 'application/php', 'text/php',
                             'application/x-httpd-php', 'application/x-httpd-php-source',
                             'text/x-sh', 'application/x-sh', 'text/x-perl'];
            if (in_array(strtolower($realMime), $blockedMimes, true)) {
                Logger::error("Intento de subida de archivo con MIME peligroso: {$realMime} — Archivo: {$filename}");
                return ['status' => false, 'error' => 'Tipo de archivo no permitido por seguridad.'];
            }
        }

        // Sanitizar el nombre del archivo
        $cleanName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', pathinfo($filename, PATHINFO_FILENAME));
        $newFilename = $cleanName . '_' . bin2hex(random_bytes(8)) . '.' . $ext;

        $targetDir = defined('UPLOAD_DIR') ? UPLOAD_DIR : __DIR__ . '/../../public/uploads';
        $subFolderDir = $targetDir . '/' . $customSubFolder;
        
        if (!is_dir($subFolderDir)) {
            mkdir($subFolderDir, 0755, true);
        }

        $destinationPath = $subFolderDir . '/' . $newFilename;
        $relativeWebPath = 'uploads/' . $customSubFolder . '/' . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $destinationPath)) {
            // Escribir un archivo index.html vacío dentro de uploads para evitar listado de directorios
            if (!file_exists($targetDir . '/index.html')) {
                file_put_contents($targetDir . '/index.html', '');
            }
            if (!file_exists($subFolderDir . '/index.html')) {
                file_put_contents($subFolderDir . '/index.html', '');
            }

            return [
                'status' => true,
                'path' => $relativeWebPath,
                'filename' => $filename
            ];
        }

        return ['status' => false, 'error' => 'No se pudo mover el archivo al directorio de subidas.'];
    }

    private static function getUploadSettings(): array {
        try {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('max_upload_size', 'allowed_extensions')");
            $rows = $stmt->fetchAll();
            $settings = [];
            foreach ($rows as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            return $settings;
        } catch (\Exception $e) {
            return [];
        }
    }
}
