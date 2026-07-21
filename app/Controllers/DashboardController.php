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

        // 1. General Metrics — consolidated into a single query (PERF-01)
        // Previously this was 11 separate DB round-trips; now it is just 1.
        $metricsStmt = $db->query("
            SELECT
                COUNT(*)                                                   AS total,
                SUM(status_id = 1)                                         AS open,
                SUM(status_id = 3)                                         AS assigned,
                SUM(status_id = 4)                                         AS inProcess,
                SUM(status_id = 5)                                         AS waiting,
                SUM(status_id = 6)                                         AS escalated,
                SUM(status_id = 7)                                         AS resolved,
                SUM(status_id = 8)                                         AS closed,
                SUM(status_id = 9)                                         AS cancelled,
                SUM(priority_id IN (4, 5))                                 AS urgent,
                SUM(assigned_technician_id IS NULL)                        AS unassigned,
                AVG(TIMESTAMPDIFF(MINUTE, created_at, closed_at))          AS avgResolutionMin
            FROM tickets
        ");
        $m = $metricsStmt->fetch();

        $total      = (int)($m['total']      ?? 0);
        $open       = (int)($m['open']       ?? 0);
        $assigned   = (int)($m['assigned']   ?? 0);
        $inProcess  = (int)($m['inProcess']  ?? 0);
        $waiting    = (int)($m['waiting']    ?? 0);
        $escalated  = (int)($m['escalated']  ?? 0);
        $resolved   = (int)($m['resolved']   ?? 0);
        $closed     = (int)($m['closed']     ?? 0);
        $cancelled  = (int)($m['cancelled']  ?? 0);
        $urgent     = (int)($m['urgent']     ?? 0);
        $unassigned = (int)($m['unassigned'] ?? 0);
        $pending    = $total - $closed - $cancelled;

        $avgResolutionHours = $m['avgResolutionMin'] ? round($m['avgResolutionMin'] / 60, 1) : 0;

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

        // 1. My Personal workload — using prepared statements to prevent SQL injection
        $stmtTotal = $db->prepare("SELECT COUNT(*) FROM tickets WHERE assigned_technician_id = ?");
        $stmtTotal->execute([$techId]);
        $totalMyTickets = $stmtTotal->fetchColumn();

        $stmtPending = $db->prepare("SELECT COUNT(*) FROM tickets WHERE assigned_technician_id = ? AND status_id NOT IN (7, 8, 9)");
        $stmtPending->execute([$techId]);
        $myPending = $stmtPending->fetchColumn();

        $stmtUrgent = $db->prepare("SELECT COUNT(*) FROM tickets WHERE assigned_technician_id = ? AND status_id NOT IN (7, 8, 9) AND priority_id IN (4, 5)");
        $stmtUrgent->execute([$techId]);
        $myUrgent = $stmtUrgent->fetchColumn();

        $stmtInProcess = $db->prepare("SELECT COUNT(*) FROM tickets WHERE assigned_technician_id = ? AND status_id = 4");
        $stmtInProcess->execute([$techId]);
        $myInProcess = $stmtInProcess->fetchColumn();

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
        $currentUser = $this->session->get('user');

        $db = Database::getConnection();

        // Fetch notification and verify ownership to prevent IDOR
        $stmtCheck = $db->prepare("SELECT user_id, ticket_id FROM notifications WHERE id = ?");
        $stmtCheck->execute([$notifId]);
        $notification = $stmtCheck->fetch();

        if (!$notification || (int)$notification['user_id'] !== (int)$currentUser['id']) {
            // Silently redirect — do not reveal whether the notification exists
            $this->response->redirect('/dashboard');
            return;
        }

        // Mark as read only if it belongs to the current user
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$notifId, $currentUser['id']]);

        if ($notification['ticket_id']) {
            $this->response->redirect("/tickets/view/{$notification['ticket_id']}");
        } else {
            $this->response->redirect('/dashboard');
        }
    }
}
