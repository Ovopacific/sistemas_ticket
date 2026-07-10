<?php
/**
 * Help Desk LAN - SMTP Settings Controller
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Database;
use App\Helpers\EmailHelper;
use App\Helpers\Auditor;

class SmtpSettingsController extends Controller {

    /**
     * Display the SMTP configuration panel.
     */
    public function index(Request $request): void {
        $this->authorize(['admin']);

        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM smtp_settings WHERE id = 1 LIMIT 1");
        $smtp = $stmt->fetch();

        // Decrypt password to display in form
        if ($smtp && !empty($smtp['mail_password'])) {
            $smtp['mail_password_decrypted'] = EmailHelper::decrypt($smtp['mail_password']);
        } else {
            $smtp['mail_password_decrypted'] = '';
        }

        $this->render('settings/smtp', ['smtp' => $smtp], 'Configuración de Correo SMTP');
    }

    /**
     * Save SMTP settings.
     */
    public function save(Request $request): void {
        $this->authorize(['admin']);

        $db = Database::getConnection();

        $host = trim($request->post('mail_host', ''));
        $port = (int)$request->post('mail_port', 587);
        $username = trim($request->post('mail_username', ''));
        $password = $request->post('mail_password', '');
        $encryption = trim($request->post('mail_encryption', 'tls'));
        $from = trim($request->post('mail_from', ''));
        $from_name = trim($request->post('mail_from_name', ''));
        $reply_to = trim($request->post('reply_to', ''));
        $bcc = trim($request->post('bcc', ''));
        $timeout = (int)$request->post('timeout', 30);
        $status = trim($request->post('status', 'inactive'));

        // Basic Validations
        if (empty($host) || empty($username) || $port <= 0 || empty($from) || empty($from_name)) {
            $this->session->setFlash('error', 'Por favor, complete todos los campos obligatorios.');
            $this->response->redirect('/settings/smtp');
            return;
        }

        try {
            // Load existing to check password
            $stmt = $db->query("SELECT mail_password FROM smtp_settings WHERE id = 1 LIMIT 1");
            $existing = $stmt->fetch();

            if (empty($password)) {
                // Keep existing password
                $encryptedPassword = $existing ? $existing['mail_password'] : '';
            } else {
                $encryptedPassword = EmailHelper::encrypt($password);
            }

            $stmt = $db->prepare("UPDATE smtp_settings SET 
                mail_host = ?, 
                mail_port = ?, 
                mail_username = ?, 
                mail_password = ?, 
                mail_encryption = ?, 
                mail_from = ?, 
                mail_from_name = ?, 
                reply_to = ?, 
                bcc = ?, 
                timeout = ?, 
                status = ? 
                WHERE id = 1");
            
            $stmt->execute([
                $host,
                $port,
                $username,
                $encryptedPassword,
                $encryption,
                $from,
                $from_name,
                empty($reply_to) ? null : $reply_to,
                empty($bcc) ? null : $bcc,
                $timeout,
                $status
            ]);

            $currentUser = $this->session->get('user');
            Auditor::log($currentUser['id'], 'SMTP_SETTINGS_UPDATE', 'Configuración de correo SMTP actualizada');

            $this->session->setFlash('success', 'Configuración de correo guardada correctamente.');
        } catch (\Exception $e) {
            $this->session->setFlash('error', 'Error al guardar la configuración: ' . $e->getMessage());
        }

        $this->response->redirect('/settings/smtp');
    }

    /**
     * Run a real-time SMTP connection diagnostic test.
     */
    public function test(Request $request): void {
        $this->authorize(['admin']);

        $testEmail = trim($request->post('test_email', ''));
        if (empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            $this->session->setFlash('error', 'Por favor, proporcione un correo de prueba válido.');
            $this->response->redirect('/settings/smtp');
            return;
        }

        // We test with the values currently in the form so the user can test before saving.
        $config = [
            'mail_host' => trim($request->post('mail_host', '')),
            'mail_port' => (int)$request->post('mail_port', 587),
            'mail_username' => trim($request->post('mail_username', '')),
            'mail_password' => $request->post('mail_password', ''), // plain text password from form
            'mail_encryption' => trim($request->post('mail_encryption', 'tls')),
            'mail_from' => trim($request->post('mail_from', '')),
            'mail_from_name' => trim($request->post('mail_from_name', '')),
            'reply_to' => trim($request->post('reply_to', '')),
            'bcc' => trim($request->post('bcc', '')),
            'timeout' => (int)$request->post('timeout', 30),
        ];

        // If the password field in the form is empty, load the encrypted password from DB and decrypt it
        if (empty($config['mail_password'])) {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT mail_password FROM smtp_settings WHERE id = 1 LIMIT 1");
            $saved = $stmt->fetch();
            if ($saved && !empty($saved['mail_password'])) {
                $config['mail_password'] = EmailHelper::decrypt($saved['mail_password']);
            }
        }

        $result = EmailHelper::sendTestEmail($config, $testEmail);

        // Flash message with status and test result
        if ($result['status']) {
            $this->session->setFlash('success', $result['message']);
        } else {
            $this->session->setFlash('error', $result['message']);
        }

        // Store log in session to display in pre tag for diagnostics
        $_SESSION['smtp_test_log'] = $result['log'];

        $this->response->redirect('/settings/smtp');
    }
}
