<?php
/**
 * Help Desk LAN - Central Dashboard Controller
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Database;
use PDO;

class DashboardController extends Controller {

    /**
     * Entry point for central router. Selects layout based on user roles.
     */
    public function index(Request $request): void {
        $this->authorize(['admin', 'technician', 'user']);
        $currentUser = $this->session->get('user');

        if ($currentUser['role'] === 'admin') {
            $this->adminDashboard();
        } elseif ($currentUser['role'] === 'technician') {
            $this->technicianDashboard($currentUser['id']);
        } else {
            // User role: redirect to their tickets layout
            $this->response->redirect('/my-tickets');
        }
    }

    /**
     * Admin Dashboard logic.
     */
    private function adminDashboard(): void {
        $db = Database::getConnection();

        // 1. General Metrics Counts
        $total = $db->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
        $open = $db->query("SELECT COUNT(*) FROM tickets WHERE status_id = 1")->fetchColumn(); // Nuevo
        $assigned = $db->query("SELECT COUNT(*) FROM tickets WHERE status_id = 3")->fetchColumn(); // Asignado
        $inProcess = $db->query("SELECT COUNT(*) FROM tickets WHERE status_id = 4")->fetchColumn(); // En proceso
        $waiting = $db->query("SELECT COUNT(*) FROM tickets WHERE status_id = 5")->fetchColumn(); // Esperando Usuario
        $escalated = $db->query("SELECT COUNT(*) FROM tickets WHERE status_id = 6")->fetchColumn(); // Escalado
        $resolved = $db->query("SELECT COUNT(*) FROM tickets WHERE status_id = 7")->fetchColumn(); // Resuelto
        $closed = $db->query("SELECT COUNT(*) FROM tickets WHERE status_id = 8")->fetchColumn(); // Cerrado
        $cancelled = $db->query("SELECT COUNT(*) FROM tickets WHERE status_id = 9")->fetchColumn(); // Cancelado

        $pending = $total - $closed - $cancelled;
        $urgent = $db->query("SELECT COUNT(*) FROM tickets WHERE priority_id IN (4, 5)")->fetchColumn(); // Crítica, Urgente
        $unassigned = $db->query("SELECT COUNT(*) FROM tickets WHERE assigned_technician_id IS NULL")->fetchColumn();

        // 2. Average Resolution Time (in hours)
        $avgResolutionMin = $db->query("SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, closed_at)) FROM tickets WHERE closed_at IS NOT NULL")->fetchColumn();
        $avgResolutionHours = $avgResolutionMin ? round($avgResolutionMin / 60, 1) : 0;

        // 3. Average Response Time (First response from tech/admin)
        $avgResponseMin = $db->query("
            SELECT AVG(TIMESTAMPDIFF(MINUTE, t.created_at, first_comments.min_created))
            FROM tickets t
            JOIN (
                SELECT ticket_id, MIN(created_at) as min_created 
                FROM comments 
                WHERE user_id IN (SELECT id FROM users WHERE role IN ('admin', 'technician'))
                GROUP BY ticket_id
            ) first_comments ON t.id = first_comments.ticket_id
        ")->fetchColumn();
        $avgResponseHours = $avgResponseMin ? round($avgResponseMin / 60, 1) : 0;

        // 4. Chart Data
        // Tickets by Technician
        $techChart = $db->query("
            SELECT CONCAT(u.first_name, ' ', u.last_name) as name, COUNT(t.id) as count 
            FROM users u 
            JOIN tickets t ON t.assigned_technician_id = u.id 
            WHERE u.role = 'technician' 
            GROUP BY u.id
        ")->fetchAll();

        // Tickets by Department
        $deptChart = $db->query("
            SELECT d.name, COUNT(t.id) as count 
            FROM departments d 
            JOIN tickets t ON t.department_id = d.id 
            GROUP BY d.id
        ")->fetchAll();

        // Tickets by Category
        $catChart = $db->query("
            SELECT c.name, COUNT(t.id) as count 
            FROM categories c 
            JOIN tickets t ON t.category_id = c.id 
            GROUP BY c.id
        ")->fetchAll();

        // Tickets by Priority
        $prioChart = $db->query("
            SELECT p.name, COUNT(t.id) as count 
            FROM priorities p 
            JOIN tickets t ON t.priority_id = p.id 
            GROUP BY p.id
        ")->fetchAll();

        // Tickets by Month (Last 6 Months)
        $monthChart = $db->query("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
            FROM tickets 
            GROUP BY month 
            ORDER BY month ASC 
            LIMIT 6
        ")->fetchAll();

        // 5. Recent Tickets
        $recentTickets = $db->query("
            SELECT t.*, s.name as status_name, s.color_hex as status_color 
            FROM tickets t 
            JOIN statuses s ON t.status_id = s.id 
            ORDER BY t.created_at DESC 
            LIMIT 5
        ")->fetchAll();

        // 6. Recent Activity logs
        $recentActivity = $db->query("
            SELECT a.*, u.username 
            FROM audit_logs a 
            LEFT JOIN users u ON a.user_id = u.id 
            ORDER BY a.created_at DESC 
            LIMIT 6
        ")->fetchAll();

        $this->render('dashboard/admin', [
            'metrics' => [
                'total' => $total,
                'open' => $open,
                'assigned' => $assigned,
                'inProcess' => $inProcess,
                'waiting' => $waiting,
                'escalated' => $escalated,
                'resolved' => $resolved,
                'closed' => $closed,
                'cancelled' => $cancelled,
                'pending' => $pending,
                'urgent' => $urgent,
                'unassigned' => $unassigned,
                'avgResolution' => $avgResolutionHours,
                'avgResponse' => $avgResponseHours
            ],
            'charts' => [
                'technician' => $techChart,
                'department' => $deptChart,
                'category' => $catChart,
                'priority' => $prioChart,
                'month' => $monthChart
            ],
            'recentTickets' => $recentTickets,
            'recentActivity' => $recentActivity
        ], 'Dashboard de Control - Administrador');
    }

    /**
     * Technician Dashboard logic.
     */
    private function technicianDashboard(int $techId): void {
        $db = Database::getConnection();

        // 1. My Personal workload
        $totalMyTickets = $db->query("SELECT COUNT(*) FROM tickets WHERE assigned_technician_id = $techId")->fetchColumn();
        $myPending = $db->query("SELECT COUNT(*) FROM tickets WHERE assigned_technician_id = $techId AND status_id NOT IN (7, 8, 9)")->fetchColumn();
        $myUrgent = $db->query("SELECT COUNT(*) FROM tickets WHERE assigned_technician_id = $techId AND status_id NOT IN (7, 8, 9) AND priority_id IN (4, 5)")->fetchColumn();
        $myInProcess = $db->query("SELECT COUNT(*) FROM tickets WHERE assigned_technician_id = $techId AND status_id = 4")->fetchColumn();

        // 2. Personal Assignments Tickets list
        $stmt = $db->prepare("SELECT t.*, s.name as status_name, s.color_hex as status_color, 
                                     p.name as priority_name, p.color_hex as priority_color,
                                     req.first_name as req_first, req.last_name as req_last
                              FROM tickets t 
                              JOIN statuses s ON t.status_id = s.id 
                              JOIN priorities p ON t.priority_id = p.id
                              JOIN users req ON t.requester_id = req.id
                              WHERE t.assigned_technician_id = ? AND t.status_id NOT IN (7, 8, 9)
                              ORDER BY t.priority_id DESC, t.created_at ASC 
                              LIMIT 5");
        $stmt->execute([$techId]);
        $myTicketsList = $stmt->fetchAll();

        // 3. Unread notifications
        $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 5");
        $stmt->execute([$techId]);
        $notifications = $stmt->fetchAll();

        $this->render('dashboard/technician', [
            'metrics' => [
                'total' => $totalMyTickets,
                'pending' => $myPending,
                'urgent' => $myUrgent,
                'inProcess' => $myInProcess
            ],
            'myTicketsList' => $myTicketsList,
            'notifications' => $notifications
        ], 'Dashboard Técnico');
    }

    /**
     * Mark notification as read.
     */
    public function markNotificationRead(Request $request, string $id): void {
        $this->authorize(['admin', 'technician', 'user']);
        $notifId = (int)$id;

        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        $stmt->execute([$notifId]);

        // Grab ticket id to redirect there
        $ticketId = $db->query("SELECT ticket_id FROM notifications WHERE id = $notifId")->fetchColumn();
        if ($ticketId) {
            $this->response->redirect("/tickets/view/{$ticketId}");
        } else {
            $this->response->redirect("/dashboard");
        }
    }
}
