<?php
/**
 * Help Desk LAN - User Administration Controller
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\UserModel;
use App\Models\DepartmentModel;
use App\Helpers\FileUploader;
use App\Helpers\Auditor;

class UserController extends Controller {
    
    /**
     * Users List Index (Admin only).
     */
    public function index(Request $request): void {
        $this->authorize(['admin']);
        
        $users = UserModel::getAll();
        $departments = DepartmentModel::getAll();

        $this->render('users/index', [
            'users' => $users,
            'departments' => $departments
        ], 'Gestión de Usuarios - Mesa de Ayuda');
    }

    /**
     * Create User view form.
     */
    public function create(Request $request): void {
        $this->authorize(['admin']);
        
        $departments = DepartmentModel::getAll();
        $this->render('users/create', [
            'departments' => $departments
        ], 'Crear Usuario - Mesa de Ayuda');
    }

    /**
     * Store new User profile.
     */
    public function store(Request $request): void {
        $this->authorize(['admin']);

        // CSRF Verification
        if (!$this->session->validateCsrfToken($request->post('csrf_token'))) {
            $this->session->setFlash('error', 'Token de seguridad expirado.');
            $this->response->redirect('/users');
        }

        $username = trim($request->post('username', ''));
        $email = trim($request->post('email', ''));
        $password = $request->post('password', '');
        $firstName = trim($request->post('first_name', ''));
        $lastName = trim($request->post('last_name', ''));
        $role = $request->post('role', 'user');
        $departmentId = $request->post('department_id', '');
        $phone = trim($request->post('phone', ''));
        $position = trim($request->post('position', ''));
        $specialty = trim($request->post('specialty', 'Soporte General'));

        // Backend Validations
        if (empty($username) || empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
            $this->session->setFlash('error', 'Todos los campos obligatorios deben completarse.');
            $this->response->redirect('/users/create');
        }

        // Check if username/email exists
        if (UserModel::getByUsername($username)) {
            $this->session->setFlash('error', 'El nombre de usuario ya está registrado.');
            $this->response->redirect('/users/create');
        }

        // Upload avatar if present
        $avatarPath = null;
        $avatarFile = $request->file('avatar');
        if ($avatarFile && $avatarFile['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload = FileUploader::upload($avatarFile, 'avatars');
            if ($upload['status']) {
                $avatarPath = $upload['path'];
            } else {
                $this->session->setFlash('error', 'Error en avatar: ' . $upload['error']);
                $this->response->redirect('/users/create');
            }
        }

        $userId = UserModel::create([
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => $role,
            'department_id' => $departmentId,
            'phone' => $phone,
            'position' => $position,
            'avatar_path' => $avatarPath,
            'specialty' => $specialty,
            'status' => 'active'
        ]);

        $currentUser = $this->session->get('user');
        Auditor::log($currentUser['id'], 'USER_CREATE', "Creación de usuario id: {$userId}, username: {$username}");

        $this->session->setFlash('success', 'Usuario creado con éxito.');
        $this->response->redirect('/users');
    }

    /**
     * Edit User view. Supports both admin editing others, and users updating their own info.
     */
    public function edit(Request $request, string $id): void {
        $userId = (int)$id;
        $currentUser = $this->session->get('user');

        // Only Admin or the user themselves can edit
        if ($currentUser['role'] !== 'admin' && $currentUser['id'] !== $userId) {
            $this->session->setFlash('error', 'No tiene permisos para editar este perfil.');
            $this->response->redirect($currentUser['role'] === 'user' ? '/my-tickets' : '/dashboard');
        }

        $user = UserModel::getById($userId);
        if (!$user) {
            $this->session->setFlash('error', 'El usuario no existe.');
            $this->response->redirect('/users');
        }

        // Get technician specialty if role is tech
        $specialty = 'Soporte General';
        if ($user['role'] === 'technician') {
            $db = \App\Core\Database::getConnection();
            $techStmt = $db->prepare("SELECT specialty FROM technicians WHERE user_id = ?");
            $techStmt->execute([$userId]);
            $tech = $techStmt->fetch();
            if ($tech) {
                $specialty = $tech['specialty'];
            }
        }

        $departments = DepartmentModel::getAll();

        $this->render('users/edit', [
            'user' => $user,
            'departments' => $departments,
            'specialty' => $specialty,
            'isSelfEdit' => ($currentUser['id'] === $userId)
        ], 'Editar Perfil - Mesa de Ayuda');
    }

    /**
     * Update User profile.
     */
    public function update(Request $request, string $id): void {
        $userId = (int)$id;
        $currentUser = $this->session->get('user');

        if ($currentUser['role'] !== 'admin' && $currentUser['id'] !== $userId) {
            $this->session->setFlash('error', 'No está autorizado.');
            $this->response->redirect('/dashboard');
        }

        // CSRF Verify
        if (!$this->session->validateCsrfToken($request->post('csrf_token'))) {
            $this->session->setFlash('error', 'Sesión de formulario expirada.');
            $this->response->redirect("/users/edit/{$id}");
        }

        $user = UserModel::getById($userId);
        if (!$user) {
            $this->session->setFlash('error', 'Usuario no encontrado.');
            $this->response->redirect('/users');
        }

        $email = trim($request->post('email', ''));
        $firstName = trim($request->post('first_name', ''));
        $lastName = trim($request->post('last_name', ''));
        $role = $currentUser['role'] === 'admin' ? $request->post('role', $user['role']) : $user['role'];
        $departmentId = $currentUser['role'] === 'admin' ? $request->post('department_id', $user['department_id']) : $user['department_id'];
        $status = $currentUser['role'] === 'admin' ? $request->post('status', $user['status']) : $user['status'];
        $phone = trim($request->post('phone', ''));
        $position = trim($request->post('position', ''));
        $password = $request->post('password', '');
        $specialty = trim($request->post('specialty', 'Soporte General'));

        if (empty($email) || empty($firstName) || empty($lastName)) {
            $this->session->setFlash('error', 'Campos requeridos vacíos.');
            $this->response->redirect("/users/edit/{$id}");
        }

        $avatarPath = $user['avatar_path'];
        $avatarFile = $request->file('avatar');
        if ($avatarFile && $avatarFile['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload = FileUploader::upload($avatarFile, 'avatars');
            if ($upload['status']) {
                $avatarPath = $upload['path'];
            } else {
                $this->session->setFlash('error', 'Error en avatar: ' . $upload['error']);
                $this->response->redirect("/users/edit/{$id}");
            }
        }

        UserModel::update($userId, [
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => $role,
            'department_id' => $departmentId,
            'phone' => $phone,
            'position' => $position,
            'status' => $status,
            'avatar_path' => $avatarPath,
            'password' => $password,
            'specialty' => $specialty
        ]);

        // If user edited themselves, refresh session
        if ($currentUser['id'] === $userId) {
            $this->session->set('user', [
                'id' => $userId,
                'username' => $user['username'],
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'role' => $role,
                'department_id' => $departmentId,
                'avatar_path' => $avatarPath,
                'status' => $status
            ]);
        }

        Auditor::log($currentUser['id'], 'USER_UPDATE', "Actualización de perfil del usuario ID: {$userId}");

        $this->session->setFlash('success', 'Perfil actualizado correctamente.');
        $this->response->redirect($currentUser['role'] === 'admin' ? '/users' : ($currentUser['role'] === 'user' ? '/my-tickets' : '/dashboard'));
    }

    /**
     * Toggle User status active/inactive.
     */
    public function toggleStatus(Request $request, string $id): void {
        $this->authorize(['admin']);
        $userId = (int)$id;

        $newStatus = UserModel::toggleStatus($userId);
        
        $currentUser = $this->session->get('user');
        Auditor::log($currentUser['id'], 'USER_TOGGLE_STATUS', "Cambio de estado del usuario ID {$userId} a {$newStatus}");

        $this->session->setFlash('success', "Estado del usuario cambiado a: " . ($newStatus === 'active' ? 'Activo' : 'Inactivo'));
        $this->response->redirect('/users');
    }

    /**
     * Show Technicians Workload List (Admin & Techs).
     */
    public function techniciansList(Request $request): void {
        $this->authorize(['admin', 'technician']);
        $techs = UserModel::getTechnicians();
        $this->render('users/technicians', ['techs' => $techs], 'Técnicos de Sistemas - Mesa de Ayuda');
    }
}
