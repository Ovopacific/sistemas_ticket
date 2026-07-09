<?php
/**
 * Help Desk LAN - Authentication Controller
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Auth;
use App\Helpers\Auditor;

class AuthController extends Controller {
    
    /**
     * Show Login Panel.
     */
    public function showLogin(Request $request): void {
        if ($this->session->get('user')) {
            $this->response->redirect('/dashboard');
        }
        $this->render('auth/login', [], 'Iniciar Sesión - Mesa de Ayuda');
    }

    /**
     * Authenticate post request.
     */
    public function login(Request $request): void {
        // Validate CSRF
        $token = $request->post('csrf_token');
        if (!$this->session->validateCsrfToken($token)) {
            \App\Helpers\Logger::error("Fallo de Login: CSRF inválido. Recibido: " . var_export($token, true) . ", Esperado: " . var_export($this->session->getCsrfToken(), true));
            $this->session->setFlash('error', 'Token de seguridad inválido.');
            $this->response->redirect('/login');
        }

        $username = trim($request->post('username', ''));
        $password = $request->post('password', '');
        $remember = $request->post('remember') !== null;

        if (empty($username) || empty($password)) {
            $this->session->setFlash('error', 'Debe rellenar todos los campos.');
            $this->response->redirect('/login');
        }

        $result = Auth::login($username, $password);
        \App\Helpers\Logger::info("Intento de Login Local para usuario: {$username}. Resultado: " . var_export($result, true));

        if ($result['status']) {
            $user = $result['user'];
            \App\Helpers\Logger::info("Autenticacion exitosa para usuario: {$username}. Session ID: " . session_id());
            
            // Set session variables
            $this->session->set('user', [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'role' => $user['role'],
                'department_id' => $user['department_id'],
                'avatar_path' => $user['avatar_path'],
                'status' => $user['status']
            ]);

            // Set cookie for persistence if chosen
            if ($remember) {
                setcookie('helpdesk_user', $username, time() + (86400 * 30), "/", "", false, true);
            } else {
                setcookie('helpdesk_user', '', time() - 3600, "/");
            }

            $this->session->setFlash('success', "Bienvenido de nuevo, {$user['first_name']}.");
            
            if ($user['role'] === 'user') {
                $this->response->redirect('/my-tickets');
            } else {
                $this->response->redirect('/dashboard');
            }
        } else {
            $this->session->setFlash('error', $result['error']);
            $this->response->redirect('/login');
        }
    }

    /**
     * Logout Session handler.
     */
    public function logout(Request $request): void {
        $user = $this->session->get('user');
        if ($user) {
            Auditor::log($user['id'], 'LOGOUT', 'Cierre de sesión del usuario');
        }
        $this->session->destroy();
        header("Location: /login");
        exit;
    }

    /**
     * Show Password Recovery instructions.
     */
    public function showRecover(Request $request): void {
        $this->render('auth/recover', [], 'Recuperar Contraseña - Mesa de Ayuda');
    }

    /**
     * Recovery trigger post handler.
     */
    public function recover(Request $request): void {
        $email = trim($request->post('email', ''));

        if (empty($email)) {
            $this->session->setFlash('error', 'Debe ingresar un correo electrónico.');
            $this->response->redirect('/recover');
        }

        // Mock recover: print instructions or trigger reset protocol if it's LAN
        // In a LAN, we advise contacting the Administrator or trigger an audit trace
        Auditor::log(null, 'PASSWORD_RESET_REQ', "Solicitud de restablecimiento para email: {$email}");
        
        $this->session->setFlash('success', 'Solicitud registrada. Por seguridad, contacte al administrador de TI para el restablecimiento.');
        $this->response->redirect('/recover');
    }
}
