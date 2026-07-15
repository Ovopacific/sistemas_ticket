<?php
/**
 * Help Desk LAN - Settings Controller
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Database;
use App\Helpers\FileUploader;
use App\Helpers\Auditor;

class SettingsController extends Controller {

    /**
     * Display configuration form.
     */
    public function index(Request $request): void {
        $this->authorize(['admin']);

        $db = Database::getConnection();
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $this->render('settings/index', ['settings' => $settings], 'Configuración del Sistema');
    }

    /**
     * Save settings parameters.
     */
    public function save(Request $request): void {
        $this->authorize(['admin']);

        $db = Database::getConnection();
        
        $params = [
            'company_name' => trim($request->post('company_name', 'Mesa de Ayuda')),
            'theme_color' => trim($request->post('theme_color', '#0d6efd')),
            'timezone' => trim($request->post('timezone', 'America/Bogota')),
            'language' => trim($request->post('language', 'es')),
            'max_upload_size' => trim($request->post('max_upload_size', '10485760')),
            'allowed_extensions' => trim($request->post('allowed_extensions', 'pdf,doc,docx,xls,xlsx,png,jpg,jpeg,zip,rar,txt,log')),
            'ldap_enabled' => $request->post('ldap_enabled') === '1' ? '1' : '0',
            'ldap_host' => trim($request->post('ldap_host', '')),
            'ldap_port' => trim($request->post('ldap_port', '389')),
            'ldap_dn' => trim($request->post('ldap_dn', '')),
            'ldap_search_base' => trim($request->post('ldap_search_base', ''))
        ];

        $db->beginTransaction();
        try {
            $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            foreach ($params as $key => $value) {
                $stmt->execute([$value, $key]);
            }
            $db->commit();
            
            $currentUser = $this->session->get('user');
            Auditor::log($currentUser['id'], 'SETTINGS_UPDATE', 'Ajustes del sistema actualizados');

            $this->session->setFlash('success', 'Configuraciones guardadas correctamente.');
        } catch (\Exception $e) {
            $db->rollBack();
            $this->session->setFlash('error', 'Error al guardar ajustes: ' . $e->getMessage());
        }

        $this->response->redirect('/settings');
    }

    /**
     * Upload brand logo.
     */
    public function uploadLogo(Request $request): void {
        $this->authorize(['admin']);

        $logoUrl = trim($request->post('logo_url', ''));
        if (!empty($logoUrl)) {
            $db = Database::getConnection();
            $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'company_logo'");
            $stmt->execute([$logoUrl]);

            $currentUser = $this->session->get('user');
            Auditor::log($currentUser['id'], 'LOGO_UPDATE', 'Logotipo corporativo modificado vía URL');

            $this->session->setFlash('success', 'Logotipo actualizado correctamente.');
            $this->response->redirect('/settings');
            return;
        }

        $file = $request->file('logo');
        if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload = FileUploader::upload($file, 'branding');
            if ($upload['status']) {
                $db = Database::getConnection();
                $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'company_logo'");
                $stmt->execute([$upload['path']]);

                $currentUser = $this->session->get('user');
                Auditor::log($currentUser['id'], 'LOGO_UPDATE', 'Logotipo corporativo modificado');

                $this->session->setFlash('success', 'Logotipo actualizado correctamente.');
            } else {
                $this->session->setFlash('error', 'Error al subir logotipo: ' . $upload['error']);
            }
        } else {
            $this->session->setFlash('error', 'No se ha seleccionado ningún archivo o URL de logotipo.');
        }

        $this->response->redirect('/settings');
    }
}
