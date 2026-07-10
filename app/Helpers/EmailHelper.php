<?php
/**
 * Help Desk LAN - Secure Email Helper
 */

namespace App\Helpers;

class EmailHelper {

    /**
     * Sends an email notification to the administrator when a new ticket is created.
     */
    public static function notifyNewTicket(array $ticket): bool {
        // Recipient email - configure this with your actual email address
        $to = getenv('NOTIFICATION_EMAIL') ?: 'admin@ovopacific.com';
        
        $subject = "🎫 Nuevo Ticket #" . $ticket['ticket_number'] . " - " . $ticket['title'];
        
        // HTML Body template with a professional design matching the dashboard theme
        $body = "
        <html>
        <head>
            <title>Nuevo Ticket Creado</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f5f7; padding: 20px;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 30px; border: 1px solid #e1e4e8; border-radius: 8px; background-color: #ffffff; box-shadow: 0 4px 6px rgba(0,0,0,0.05);'>
                <h2 style='color: #0d6efd; margin-top: 0; font-size: 1.5rem; border-bottom: 2px solid #f4f5f7; padding-bottom: 15px;'>Mesa de Ayuda - Ovopacific</h2>
                <p style='font-size: 1rem; color: #555;'>Se ha registrado una nueva solicitud de soporte:</p>
                
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <tr>
                        <td style='padding: 10px 0; font-weight: bold; width: 140px; color: #666; border-bottom: 1px dashed #f4f5f7;'>Ticket Nro:</td>
                        <td style='padding: 10px 0; color: #111; font-weight: bold; border-bottom: 1px dashed #f4f5f7;'>#" . htmlspecialchars($ticket['ticket_number']) . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px 0; font-weight: bold; color: #666; border-bottom: 1px dashed #f4f5f7;'>Título:</td>
                        <td style='padding: 10px 0; color: #111; border-bottom: 1px dashed #f4f5f7;'>" . htmlspecialchars($ticket['title']) . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px 0; font-weight: bold; color: #666; border-bottom: 1px dashed #f4f5f7;'>Solicitante:</td>
                        <td style='padding: 10px 0; color: #111; border-bottom: 1px dashed #f4f5f7;'>" . htmlspecialchars($ticket['req_first'] . ' ' . $ticket['req_last']) . " (" . htmlspecialchars($ticket['req_email']) . ")</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px 0; font-weight: bold; color: #666; border-bottom: 1px dashed #f4f5f7;'>Prioridad:</td>
                        <td style='padding: 10px 0; color: #111; border-bottom: 1px dashed #f4f5f7;'><span style='background-color: " . htmlspecialchars($ticket['priority_color'] ?? '#0d6efd') . "15; color: " . htmlspecialchars($ticket['priority_color'] ?? '#0d6efd') . "; padding: 3px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: bold;'>" . htmlspecialchars($ticket['priority_name'] ?? 'Normal') . "</span></td>
                    </tr>
                    <tr>
                        <td style='padding: 10px 0; font-weight: bold; color: #666; border-bottom: 1px dashed #f4f5f7;'>Departamento:</td>
                        <td style='padding: 10px 0; color: #111; border-bottom: 1px dashed #f4f5f7;'>" . htmlspecialchars($ticket['department_name'] ?? 'Sistemas') . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 15px 0; font-weight: bold; color: #666; vertical-align: top;'>Descripción:</td>
                        <td style='padding: 15px 0; color: #333; background-color: #f8f9fa; border-radius: 6px; padding: 12px; white-space: pre-wrap; font-size: 0.9rem; line-height: 1.5;'>" . htmlspecialchars($ticket['description']) . "</td>
                    </tr>
                </table>
                
                <div style='text-align: center; margin-top: 30px;'>
                    <a href='" . (getenv('APP_URL') ?: 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost:8080')) . "/tickets/view/" . $ticket['id'] . "' 
                       style='display: inline-block; padding: 12px 24px; background-color: #0d6efd; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 0.95rem;'>
                        Ver Ticket en el Panel
                    </a>
                </div>
                
                <hr style='border: none; border-top: 1px solid #e1e4e8; margin-top: 30px;' />
                <p style='font-size: 0.775rem; color: #888; text-align: center; margin-bottom: 0;'>
                    Este es un correo de notificación automático del sistema de Tickets de Ovopacific. Por favor no responder a este mensaje.
                </p>
            </div>
        </body>
        </html>
        ";
        
        return self::sendHtml($to, $subject, $body);
    }

    /**
     * Cifra un valor usando AES-256-CBC con la llave APP_KEY.
     */
    public static function encrypt(string $value): string {
        $key = hash('sha256', APP_KEY, true);
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encrypted = openssl_encrypt($value, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * Descifra un valor usando AES-256-CBC con la llave APP_KEY.
     */
    public static function decrypt(string $value): string {
        try {
            $key = hash('sha256', APP_KEY, true);
            $data = base64_decode($value);
            $ivLength = openssl_cipher_iv_length('aes-256-cbc');
            $iv = substr($data, 0, $ivLength);
            $encrypted = substr($data, $ivLength);
            return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv) ?: '';
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Envía un correo HTML utilizando la configuración SMTP de la base de datos o reporta inactividad.
     */
    public static function sendHtml(string $to, string $subject, string $body): bool {
        Logger::info("Intentando enviar correo a: {$to} con asunto: {$subject}");

        try {
            $db = \App\Core\Database::getConnection();
            $stmt = $db->query("SELECT * FROM smtp_settings WHERE id = 1 LIMIT 1");
            $config = $stmt->fetch();

            if (!$config || $config['status'] !== 'active') {
                Logger::warning("Envío de correo cancelado: la configuración SMTP está inactiva o no existe.");
                return false;
            }

            // Cargar PHPMailer
            require_once __DIR__ . '/../Libraries/PHPMailer/Exception.php';
            require_once __DIR__ . '/../Libraries/PHPMailer/PHPMailer.php';
            require_once __DIR__ . '/../Libraries/PHPMailer/SMTP.php';

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            // Servidor
            $mail->isSMTP();
            $mail->Host = $config['mail_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['mail_username'];
            $mail->Password = self::decrypt($config['mail_password']);
            $mail->Port = (int)$config['mail_port'];
            $mail->Timeout = (int)$config['timeout'];

            $encryption = strtolower($config['mail_encryption']);
            if ($encryption === 'ssl') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption === 'tls') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = '';
                $mail->SMTPAutoTLS = false;
            }

            // Destinatarios
            $mail->setFrom($config['mail_from'], $config['mail_from_name']);
            $mail->addAddress($to);

            if (!empty($config['reply_to'])) {
                $mail->addReplyTo($config['reply_to']);
            }

            if (!empty($config['bcc'])) {
                $mail->addBCC($config['bcc']);
            }

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->CharSet = 'UTF-8';

            $mail->send();
            Logger::info("Correo enviado exitosamente a: {$to}");
            return true;
        } catch (\Exception $e) {
            Logger::error("Error de PHPMailer al enviar correo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Método para enviar un correo de prueba y retornar el diagnóstico exacto.
     */
    public static function sendTestEmail(array $config, string $testEmail): array {
        try {
            // Cargar PHPMailer
            require_once __DIR__ . '/../Libraries/PHPMailer/Exception.php';
            require_once __DIR__ . '/../Libraries/PHPMailer/PHPMailer.php';
            require_once __DIR__ . '/../Libraries/PHPMailer/SMTP.php';

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            // Capturar logs de SMTP detallados
            $smtpLog = '';
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = function($str, $level) use (&$smtpLog) {
                $smtpLog .= $str . "\n";
            };

            $mail->isSMTP();
            $mail->Host = $config['mail_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['mail_username'];
            $mail->Password = $config['mail_password']; // Contraseña en texto plano para la prueba al vuelo
            $mail->Port = (int)$config['mail_port'];
            $mail->Timeout = (int)$config['timeout'];

            $encryption = strtolower($config['mail_encryption']);
            if ($encryption === 'ssl') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption === 'tls') {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = '';
                $mail->SMTPAutoTLS = false;
            }

            $mail->setFrom($config['mail_from'], $config['mail_from_name']);
            $mail->addAddress($testEmail);

            if (!empty($config['reply_to'])) {
                $mail->addReplyTo($config['reply_to']);
            }

            if (!empty($config['bcc'])) {
                $mail->addBCC($config['bcc']);
            }

            $mail->isHTML(true);
            $mail->Subject = "Correo de Prueba - Configuración SMTP";
            $mail->Body = "<h3>¡Conexión exitosa!</h3><p>Este es un correo de prueba enviado desde la configuración del sistema de soporte.</p>";
            $mail->CharSet = 'UTF-8';

            $mail->send();
            return [
                'status' => true,
                'message' => '✅ Conexión realizada correctamente. Correo enviado correctamente.',
                'log' => $smtpLog
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '❌ Fallo al enviar el correo de prueba: ' . $e->getMessage(),
                'log' => $smtpLog ?? 'No se pudo generar el log SMTP.'
            ];
        }
    }
}
