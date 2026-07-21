<?php
/**
 * Help Desk LAN - Ticket Processing Controller
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\TicketModel;
use App\Models\CommentModel;
use App\Models\UserModel;
use App\Models\DepartmentModel;
use App\Models\CategoryModel;
use App\Helpers\FileUploader;
use App\Helpers\Auditor;
use App\Helpers\EmailHelper;

class TicketController extends Controller {

    /**
     * List all tickets (Admin and Technician only).
     */
    public function index(Request $request): void {
        $this->authorize(['admin', 'technician']);
        
        // Grab filter params
        $filters = [
            'status_id' => $request->get('status_id'),
            'priority_id' => $request->get('priority_id'),
            'category_id' => $request->get('category_id'),
            'department_id' => $request->get('department_id'),
            'today' => $request->get('today'),
            'week' => $request->get('week'),
            'month' => $request->get('month'),
            'unassigned' => $request->get('unassigned') === '1',
            'urgent' => $request->get('urgent') === '1',
            'in_process' => $request->get('in_process') === '1',
            'resolved' => $request->get('resolved') === '1'
        ];

        $tickets = TicketModel::getAll($filters);
        $departments = DepartmentModel::getAll();
        $categories = CategoryModel::getAll();
        
        $db = \App\Core\Database::getConnection();
        $statuses = $db->query("SELECT * FROM statuses")->fetchAll();
        $priorities = $db->query("SELECT * FROM priorities")->fetchAll();

        $this->render('tickets/index', [
            'tickets' => $tickets,
            'departments' => $departments,
            'categories' => $categories,
            'statuses' => $statuses,
            'priorities' => $priorities,
            'isUserPanel' => false
        ], 'Gestión de Tickets');
    }

    /**
     * List tickets for the logged in User or assigned to the logged in Technician.
     */
    public function myTickets(Request $request): void {
        $this->authorize(['user', 'technician', 'admin']);
        $currentUser = $this->session->get('user');

        $filters = [];
        if ($currentUser['role'] === 'user') {
            $filters['requester_id'] = $currentUser['id'];
        } elseif ($currentUser['role'] === 'technician') {
            $filters['assigned_technician_id'] = $currentUser['id'];
        }

        // Apply state quick filters
        if ($request->get('unassigned') === '1') $filters['unassigned'] = true;
        if ($request->get('urgent') === '1') $filters['urgent'] = true;
        if ($request->get('in_process') === '1') $filters['in_process'] = true;
        if ($request->get('resolved') === '1') $filters['resolved'] = true;

        $tickets = TicketModel::getAll($filters);
        $departments = DepartmentModel::getAll();
        $categories = CategoryModel::getAll();
        
        $db = \App\Core\Database::getConnection();
        $statuses = $db->query("SELECT * FROM statuses")->fetchAll();
        $priorities = $db->query("SELECT * FROM priorities")->fetchAll();

        $this->render('tickets/index', [
            'tickets' => $tickets,
            'departments' => $departments,
            'categories' => $categories,
            'statuses' => $statuses,
            'priorities' => $priorities,
            'isUserPanel' => true
        ], 'Mis Tickets');
    }

    /**
     * Show Ticket creation form.
     */
    public function create(Request $request): void {
        $this->authorize(['admin', 'technician', 'user']);
        $currentUser = $this->session->get('user');
        
        $departments = DepartmentModel::getAll();
        $categories = CategoryModel::getAll();
        
        $db = \App\Core\Database::getConnection();
        $priorities = $db->query("SELECT * FROM priorities")->fetchAll();

        $users = [];
        if ($currentUser['role'] !== 'user') {
            $users = $db->query("SELECT id, first_name, last_name, email FROM users WHERE status = 'active' ORDER BY first_name ASC")->fetchAll();
        }

        $this->render('tickets/create', [
            'departments' => $departments,
            'categories' => $categories,
            'priorities' => $priorities,
            'users' => $users
        ], 'Crear Solicitud de Soporte');
    }

    /**
     * Store new Ticket in database.
     */
    public function store(Request $request): void {
        $this->authorize(['admin', 'technician', 'user']);
        $currentUser = $this->session->get('user');

        if (!$this->session->validateCsrfToken($request->post('csrf_token'))) {
            $this->session->setFlash('error', 'Token de seguridad inválido.');
            $this->response->redirect('/tickets/create');
        }

        $title = trim($request->post('title', ''));
        $description = trim($request->post('description', ''));
        $departmentId = $request->post('department_id', '');
        $categoryId = $request->post('category_id', '');
        $priorityId = $request->post('priority_id', '1');

        if (empty($title) || empty($description)) {
            $this->session->setFlash('error', 'El título y la descripción son obligatorios.');
            $this->response->redirect('/tickets/create');
        }

        $requesterId = $currentUser['id'];
        if ($currentUser['role'] !== 'user') {
            $postRequester = $request->post('requester_id', '');
            if (!empty($postRequester)) {
                $requesterId = (int)$postRequester;
            }
        }

        $ticketId = TicketModel::create([
            'title' => $title,
            'description' => $description,
            'department_id' => $departmentId,
            'category_id' => $categoryId,
            'priority_id' => $priorityId,
            'requester_id' => $requesterId,
            'assigned_technician_id' => null
        ]);

        // Send email notification of new ticket creation to admin
        $newTicket = TicketModel::getById($ticketId);
        if ($newTicket) {
            EmailHelper::notifyNewTicket($newTicket);
        }

        // If ticket has attachment files directly on comment creation
        $attachmentPath = null;
        $attachmentName = null;
        $file = $request->file('attachment');
        if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload = FileUploader::upload($file, 'attachments');
            if ($upload['status']) {
                $attachmentPath = $upload['path'];
                $attachmentName = $upload['filename'];
                
                // Add an initial comment referencing the attachment
                CommentModel::create([
                    'ticket_id' => $ticketId,
                    'user_id' => $currentUser['id'],
                    'comment_text' => 'Archivo adjunto inicial del reporte.',
                    'attachment_path' => $attachmentPath,
                    'attachment_filename' => $attachmentName
                ]);
            } else {
                $this->session->setFlash('error', 'Archivo no cargado: ' . $upload['error']);
            }
        }

        Auditor::log($currentUser['id'], 'TICKET_CREATE', "Creación de ticket ID: {$ticketId}, Título: {$title}");

        $this->session->setFlash('success', 'Ticket creado correctamente.');
        $this->response->redirect($currentUser['role'] === 'user' ? '/my-tickets' : '/tickets');
    }

    /**
     * Detailed view of a ticket, loading discussion flow and admin options.
     */
    public function view(Request $request, string $id): void {
        $this->authorize(['admin', 'technician', 'user']);
        $ticketId = (int)$id;
        $currentUser = $this->session->get('user');

        $ticket = TicketModel::getById($ticketId);
        if (!$ticket) {
            $this->session->setFlash('error', 'Ticket no encontrado.');
            $this->response->redirect($currentUser['role'] === 'user' ? '/my-tickets' : '/tickets');
        }

        // Restrict final users to view only their own tickets
        if ($currentUser['role'] === 'user' && $ticket['requester_id'] !== $currentUser['id']) {
            $this->session->setFlash('error', 'No tiene permisos para ver este ticket.');
            $this->response->redirect('/my-tickets');
        }

        $comments = CommentModel::getByTicketId($ticketId);
        $technicians = UserModel::getTechnicians();
        
        $db = \App\Core\Database::getConnection();
        $statuses = $db->query("SELECT * FROM statuses")->fetchAll();
        $priorities = $db->query("SELECT * FROM priorities")->fetchAll();
        $categories = $db->query("SELECT * FROM categories")->fetchAll();

        // Get audit trail logs for this ticket
        $auditLogs = [];
        if ($currentUser['role'] !== 'user') {
            $auditStmt = $db->prepare("SELECT a.*, u.username 
                                      FROM audit_logs a 
                                      LEFT JOIN users u ON a.user_id = u.id 
                                      WHERE a.details LIKE ? 
                                      ORDER BY a.created_at DESC");
            $auditStmt->execute(["%ID: {$ticketId}%"]);
            $auditLogs = $auditStmt->fetchAll();
        }

        $this->render('tickets/view', [
            'ticket' => $ticket,
            'comments' => $comments,
            'technicians' => $technicians,
            'statuses' => $statuses,
            'priorities' => $priorities,
            'categories' => $categories,
            'auditLogs' => $auditLogs
        ], "Detalle Ticket {$ticket['ticket_number']}");
    }

    /**
     * Add comment to ticket.
     */
    public function addComment(Request $request, string $id): void {
        $this->authorize(['admin', 'technician', 'user']);
        $ticketId = (int)$id;
        $currentUser = $this->session->get('user');

        $commentText = trim($request->post('comment_text', ''));
        
        $attachmentPath = null;
        $attachmentName = null;
        $file = $request->file('attachment');
        if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload = FileUploader::upload($file, 'attachments');
            if ($upload['status']) {
                $attachmentPath = $upload['path'];
                $attachmentName = $upload['filename'];
            } else {
                $this->session->setFlash('error', 'Error de archivo: ' . $upload['error']);
                $this->response->redirect("/tickets/view/{$ticketId}");
            }
        }

        if (empty($commentText) && empty($attachmentPath)) {
            $this->session->setFlash('error', 'Debe escribir un mensaje o adjuntar un archivo.');
            $this->response->redirect("/tickets/view/{$ticketId}");
        }

        CommentModel::create([
            'ticket_id' => $ticketId,
            'user_id' => $currentUser['id'],
            'comment_text' => $commentText,
            'attachment_path' => $attachmentPath,
            'attachment_filename' => $attachmentName
        ]);

        Auditor::log($currentUser['id'], 'TICKET_COMMENT', "Comentario agregado al ticket ID: {$ticketId}");

        $this->session->setFlash('success', 'Mensaje enviado.');
        $this->response->redirect("/tickets/view/{$ticketId}");
    }

    /**
     * Assign ticket to a systems engineer.
     */
    public function assign(Request $request, string $id): void {
        $this->authorize(['admin', 'technician']);
        $ticketId = (int)$id;

        // CSRF Verification for critical assignment action
        if (!$this->session->validateCsrfToken($request->post('csrf_token'))) {
            $this->session->setFlash('error', 'Token de seguridad inválido.');
            $this->response->redirect("/tickets/view/{$ticketId}");
            return;
        }

        $techId = $request->post('assigned_technician_id', '');
        $techId = !empty($techId) ? (int)$techId : null;

        TicketModel::assign($ticketId, $techId);

        $currentUser = $this->session->get('user');
        Auditor::log($currentUser['id'], 'TICKET_ASSIGN', "Asignación de técnico a ticket ID: {$ticketId}");

        // Notify requester that a technician has been assigned
        if ($techId) {
            $updatedTicket = TicketModel::getById($ticketId);
            if ($updatedTicket) {
                EmailHelper::notifyTicketAssigned($updatedTicket);
            }
        }

        $this->session->setFlash('success', 'Técnico asignado correctamente.');
        $this->response->redirect("/tickets/view/{$ticketId}");
    }

    /**
     * Change ticket state.
     */
    public function changeStatus(Request $request, string $id): void {
        $this->authorize(['admin', 'technician']);
        $ticketId = (int)$id;
        $statusId = (int)$request->post('status_id');

        TicketModel::updateStatus($ticketId, $statusId);

        $currentUser = $this->session->get('user');
        Auditor::log($currentUser['id'], 'TICKET_STATUS_CHANGE', "Cambio de estado ticket ID: {$ticketId} a ID: {$statusId}");

        // Notify requester and admin when ticket is resolved (7) or closed (8)
        if (in_array($statusId, [7, 8])) {
            $updatedTicket = TicketModel::getById($ticketId);
            if ($updatedTicket) {
                EmailHelper::notifyTicketResolved($updatedTicket);
            }
        }

        $this->session->setFlash('success', 'Estado del ticket modificado.');
        $this->response->redirect("/tickets/view/{$ticketId}");
    }

    /**
     * Change priority.
     */
    public function changePriority(Request $request, string $id): void {
        $this->authorize(['admin', 'technician']);
        $ticketId = (int)$id;
        $priorityId = (int)$request->post('priority_id');

        TicketModel::updatePriority($ticketId, $priorityId);

        $currentUser = $this->session->get('user');
        Auditor::log($currentUser['id'], 'TICKET_PRIORITY_CHANGE', "Cambio de prioridad ticket ID: {$ticketId} a ID: {$priorityId}");

        $this->session->setFlash('success', 'Prioridad del ticket modificada.');
        $this->response->redirect("/tickets/view/{$ticketId}");
    }

    /**
     * Log work hours spent (accumulative minutes worked).
     */
    public function updateTimeSpent(Request $request, string $id): void {
        $this->authorize(['admin', 'technician']);
        $ticketId = (int)$id;
        $minutes = (int)$request->post('time_spent', 0);

        if ($minutes > 0) {
            TicketModel::updateTimeSpent($ticketId, $minutes);
            $currentUser = $this->session->get('user');
            Auditor::log($currentUser['id'], 'TICKET_TIME_LOGGED', "Registro de {$minutes} minutos de trabajo en ticket ID: {$ticketId}");
            $this->session->setFlash('success', 'Tiempo de trabajo registrado con éxito.');
        } else {
            $this->session->setFlash('error', 'El tiempo ingresado debe ser mayor a 0 minutos.');
        }

        $this->response->redirect("/tickets/view/{$ticketId}");
    }

    /**
     * Edit a specific comment.
     */
    public function editComment(Request $request, string $id): void {
        $this->authorize(['admin', 'technician', 'user']);
        $commentId = (int)$id;
        $currentUser = $this->session->get('user');
        
        $comment = CommentModel::getById($commentId);
        if (!$comment) {
            $this->response->json(['status' => false, 'error' => 'Comentario no encontrado.'], 404);
        }

        if ($currentUser['role'] !== 'admin' && $comment['user_id'] !== $currentUser['id']) {
            $this->response->json(['status' => false, 'error' => 'No autorizado.'], 403);
        }

        $newText = trim($request->post('comment_text', ''));
        if (empty($newText)) {
            $this->response->json(['status' => false, 'error' => 'El texto no puede estar vacío.'], 400);
        }

        CommentModel::update($commentId, $newText);
        Auditor::log($currentUser['id'], 'COMMENT_EDIT', "Edición de comentario ID: {$commentId}");

        $this->response->json(['status' => true, 'message' => 'Comentario actualizado.']);
    }

    /**
     * Delete a specific comment.
     */
    public function deleteComment(Request $request, string $id): void {
        $this->authorize(['admin', 'technician', 'user']);
        $commentId = (int)$id;
        $currentUser = $this->session->get('user');

        $comment = CommentModel::getById($commentId);
        if (!$comment) {
            $this->response->redirect('/tickets');
        }

        if ($currentUser['role'] !== 'admin' && $comment['user_id'] !== $currentUser['id']) {
            $this->session->setFlash('error', 'No autorizado.');
            $this->response->redirect("/tickets/view/{$comment['ticket_id']}");
        }

        CommentModel::delete($commentId);
        Auditor::log($currentUser['id'], 'COMMENT_DELETE', "Eliminación de comentario ID: {$commentId}");

        $this->session->setFlash('success', 'Mensaje eliminado.');
        $this->response->redirect("/tickets/view/{$comment['ticket_id']}");
    }

    /**
     * Delete a specific ticket (Admin and Technician only).
     */
    public function delete(Request $request, string $id): void {
        $this->authorize(['admin', 'technician']);
        $ticketId = (int)$id;
        $currentUser = $this->session->get('user');

        $ticket = TicketModel::getById($ticketId);
        if (!$ticket) {
            $this->session->setFlash('error', 'Ticket no encontrado.');
            $this->response->redirect($currentUser['role'] === 'user' ? '/my-tickets' : '/tickets');
            return;
        }

        TicketModel::delete($ticketId);
        Auditor::log($currentUser['id'], 'TICKET_DELETE', "Eliminación de ticket ID: {$ticketId}, Número: {$ticket['ticket_number']}, Título: {$ticket['title']}");

        $this->session->setFlash('success', 'Ticket eliminado correctamente.');
        $this->response->redirect($currentUser['role'] === 'user' ? '/my-tickets' : '/tickets');
    }

    /**
     * Change ticket category.
     */
    public function changeCategory(Request $request, string $id): void {
        $this->authorize(['admin', 'technician']);
        $ticketId = (int)$id;
        $categoryId = (int)$request->post('category_id');

        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("UPDATE tickets SET category_id = ? WHERE id = ?");
        $stmt->execute([$categoryId, $ticketId]);

        $currentUser = $this->session->get('user');
        Auditor::log($currentUser['id'], 'TICKET_CATEGORY_CHANGE', "Cambio de categoría ticket ID: {$ticketId} a ID: {$categoryId}");

        $this->session->setFlash('success', 'Categoría cambiada correctamente.');
        $this->response->redirect("/tickets/view/{$ticketId}");
    }

    /**
     * Update all ticket properties at once.
     */
    public function updateProperties(Request $request, string $id): void {
        $this->authorize(['admin', 'technician']);
        $ticketId = (int)$id;
        $currentUser = $this->session->get('user');

        // Grab previous ticket state to detect changes
        $prevTicket = TicketModel::getById($ticketId);
        $prevTechId = $prevTicket['assigned_technician_id'] ?? null;

        $techId = $request->post('assigned_technician_id', '');
        $techId = !empty($techId) ? (int)$techId : null;
        
        $statusId = (int)$request->post('status_id');
        $priorityId = (int)$request->post('priority_id');
        $categoryId = (int)$request->post('category_id');

        // Apply changes
        TicketModel::assign($ticketId, $techId);
        TicketModel::updateStatus($ticketId, $statusId);
        TicketModel::updatePriority($ticketId, $priorityId);
        
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("UPDATE tickets SET category_id = ? WHERE id = ?");
        $stmt->execute([$categoryId, $ticketId]);

        Auditor::log($currentUser['id'], 'TICKET_PROPERTIES_UPDATE', "Actualización de propiedades del ticket ID: {$ticketId}");

        // Reload updated ticket once for email logic
        $updatedTicket = TicketModel::getById($ticketId);
        if ($updatedTicket) {
            // Notify requester if a technician was newly assigned
            if ($techId && $techId !== (int)$prevTechId) {
                EmailHelper::notifyTicketAssigned($updatedTicket);
            }
            // Notify requester + admin if ticket is being resolved or closed
            if (in_array($statusId, [7, 8])) {
                EmailHelper::notifyTicketResolved($updatedTicket);
            }
        }

        $this->session->setFlash('success', 'Propiedades del ticket actualizadas correctamente.');
        $this->response->redirect("/tickets/view/{$ticketId}");
    }
}
