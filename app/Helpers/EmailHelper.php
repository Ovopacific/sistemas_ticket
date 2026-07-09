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
     * Helper to send HTML emails using native PHP mail().
     */
    public static function sendHtml(string $to, string $subject, string $body): bool {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Mesa de Ayuda Ovopacific <no-reply@ovopacific.com>\r\n";
        
        // Log notification attempt
        Logger::info("Intentando enviar correo de notificación a: {$to} para el asunto: {$subject}");

        try {
            return mail($to, $subject, $body, $headers);
        } catch (\Exception $e) {
            Logger::error("Fallo al enviar notificación de correo: " . $e->getMessage());
            return false;
        }
    }
}
