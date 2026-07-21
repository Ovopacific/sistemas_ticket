<?php
/**
 * Help Desk LAN - Ticket Model
 */

namespace App\Models;

use App\Core\Database;
use PDO;

class TicketModel {
    
    public static function getAll(array $filters = []): array {
        $db = Database::getConnection();
        
        $sql = "SELECT t.*, 
                       d.name as department_name, 
                       c.name as category_name, 
                       p.name as priority_name, p.color_hex as priority_color,
                       s.name as status_name, s.color_hex as status_color,
                       req.first_name as req_first, req.last_name as req_last,
                       tech.first_name as tech_first, tech.last_name as tech_last
                FROM tickets t
                LEFT JOIN departments d ON t.department_id = d.id
                LEFT JOIN categories c ON t.category_id = c.id
                LEFT JOIN priorities p ON t.priority_id = p.id
                LEFT JOIN statuses s ON t.status_id = s.id
                LEFT JOIN users req ON t.requester_id = req.id
                LEFT JOIN users tech ON t.assigned_technician_id = tech.id";

        $where = [];
        $params = [];

        // Apply filters
        if (!empty($filters['requester_id'])) {
            $where[] = "t.requester_id = :requester_id";
            $params['requester_id'] = (int)$filters['requester_id'];
        }
        if (!empty($filters['assigned_technician_id'])) {
            $where[] = "t.assigned_technician_id = :assigned_technician_id";
            $params['assigned_technician_id'] = (int)$filters['assigned_technician_id'];
        }
        if (!empty($filters['status_id'])) {
            $where[] = "t.status_id = :status_id";
            $params['status_id'] = (int)$filters['status_id'];
        }
        if (!empty($filters['priority_id'])) {
            $where[] = "t.priority_id = :priority_id";
            $params['priority_id'] = (int)$filters['priority_id'];
        }
        if (!empty($filters['category_id'])) {
            $where[] = "t.category_id = :category_id";
            $params['category_id'] = (int)$filters['category_id'];
        }
        if (!empty($filters['department_id'])) {
            $where[] = "t.department_id = :department_id";
            $params['department_id'] = (int)$filters['department_id'];
        }
        if (!empty($filters['today'])) {
            $where[] = "DATE(t.created_at) = CURRENT_DATE()";
        }
        if (!empty($filters['week'])) {
            $where[] = "t.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        }
        if (!empty($filters['month'])) {
            $where[] = "t.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        }
        if (isset($filters['unassigned']) && $filters['unassigned'] === true) {
            $where[] = "t.assigned_technician_id IS NULL";
        }
        if (!empty($filters['urgent'])) {
            $where[] = "t.priority_id IN (4, 5)"; // Critica, Urgente
        }
        if (!empty($filters['in_process'])) {
            $where[] = "t.status_id = 4"; // En proceso
        }
        if (!empty($filters['resolved'])) {
            $where[] = "t.status_id = 7"; // Resuelto
        }

        if (count($where) > 0) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY t.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getById(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT t.*, 
                                     d.name as department_name, 
                                     c.name as category_name, 
                                     p.name as priority_name, p.color_hex as priority_color,
                                     s.name as status_name, s.color_hex as status_color,
                                     req.first_name as req_first, req.last_name as req_last, req.email as req_email, req.phone as req_phone, req.avatar_path as req_avatar,
                                     tech.first_name as tech_first, tech.last_name as tech_last, tech.email as tech_email, tech.avatar_path as tech_avatar
                              FROM tickets t
                              LEFT JOIN departments d ON t.department_id = d.id
                              LEFT JOIN categories c ON t.category_id = c.id
                              LEFT JOIN priorities p ON t.priority_id = p.id
                              LEFT JOIN statuses s ON t.status_id = s.id
                              LEFT JOIN users req ON t.requester_id = req.id
                              LEFT JOIN users tech ON t.assigned_technician_id = tech.id
                              WHERE t.id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Create ticket. Automatically generates the ticket number (e.g. TK-000001)
     */
    public static function create(array $data): int {
        $db = Database::getConnection();
        
        // Transaction to ensure sequential ticket numbers safely
        $db->beginTransaction();
        try {
            // Find next ID
            $stmt = $db->query("SELECT MAX(id) as max_id FROM tickets");
            $row = $stmt->fetch();
            $nextId = ($row['max_id'] ?? 0) + 1;
            $ticketNumber = 'TK-' . str_pad((string)$nextId, 6, '0', STR_PAD_LEFT);

            $stmt = $db->prepare("INSERT INTO tickets (ticket_number, title, description, department_id, category_id, priority_id, status_id, requester_id, assigned_technician_id) 
                                  VALUES (:number, :title, :description, :dept, :cat, :prio, 1, :requester, :tech)");
            
            $stmt->execute([
                'number' => $ticketNumber,
                'title' => $data['title'],
                'description' => $data['description'],
                'dept' => !empty($data['department_id']) ? (int)$data['department_id'] : null,
                'cat' => !empty($data['category_id']) ? (int)$data['category_id'] : null,
                'prio' => !empty($data['priority_id']) ? (int)$data['priority_id'] : 1, // Baja default
                'requester' => (int)$data['requester_id'],
                'tech' => !empty($data['assigned_technician_id']) ? (int)$data['assigned_technician_id'] : null
            ]);

            $ticketId = (int)$db->lastInsertId();

            // Create notification for admin/assigned tech
            $message = "Se ha creado el ticket {$ticketNumber}: {$data['title']}";
            self::createNotification($db, (int)$data['requester_id'], $ticketId, $message);

            if (!empty($data['assigned_technician_id'])) {
                self::createNotification($db, (int)$data['assigned_technician_id'], $ticketId, "Se te ha asignado el ticket {$ticketNumber}");
            }

            $db->commit();
            return $ticketId;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function assign(int $id, ?int $techId): bool {
        $db = Database::getConnection();
        
        $ticket = self::getById($id);
        if (!$ticket) return false;

        $statusId = $techId ? 3 : 1; // Asignado or Nuevo

        $stmt = $db->prepare("UPDATE tickets SET assigned_technician_id = ?, status_id = ? WHERE id = ?");
        $result = $stmt->execute([$techId, $statusId, $id]);

        if ($result && $techId) {
            self::createNotification($db, $techId, $id, "Se te ha asignado el ticket {$ticket['ticket_number']}");
            self::createNotification($db, $ticket['requester_id'], $id, "Tu ticket {$ticket['ticket_number']} ha sido asignado a un técnico");
        }

        return $result;
    }

    public static function updateStatus(int $id, int $statusId): bool {
        $db = Database::getConnection();
        
        $ticket = self::getById($id);
        if (!$ticket) return false;

        $closedAt = null;
        if (in_array($statusId, [7, 8, 9])) { // Resuelto, Cerrado, Cancelado
            $closedAt = date('Y-m-d H:i:s');
        }

        $stmt = $db->prepare("UPDATE tickets SET status_id = ?, closed_at = ? WHERE id = ?");
        $result = $stmt->execute([$statusId, $closedAt, $id]);

        if ($result) {
            // Use prepared statement to avoid SQL injection
            $stmtStatus = $db->prepare("SELECT name FROM statuses WHERE id = ?");
            $stmtStatus->execute([$statusId]);
            $statusName = $stmtStatus->fetchColumn();
            self::createNotification($db, $ticket['requester_id'], $id, "El estado del ticket {$ticket['ticket_number']} cambió a: {$statusName}");
            if ($ticket['assigned_technician_id']) {
                self::createNotification($db, $ticket['assigned_technician_id'], $id, "El estado del ticket {$ticket['ticket_number']} cambió a: {$statusName}");
            }
        }

        return $result;
    }

    public static function updatePriority(int $id, int $priorityId): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE tickets SET priority_id = ? WHERE id = ?");
        return $stmt->execute([$priorityId, $id]);
    }

    public static function updateTimeSpent(int $id, int $minutes): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE tickets SET time_spent = time_spent + ? WHERE id = ?");
        return $stmt->execute([$minutes, $id]);
    }

    public static function delete(int $id): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM tickets WHERE id = ?");
        return $stmt->execute([$id]);
    }

    private static function createNotification($db, int $userId, int $ticketId, string $message): void {
        $stmt = $db->prepare("INSERT INTO notifications (user_id, ticket_id, message, is_read) VALUES (?, ?, ?, 0)");
        $stmt->execute([$userId, $ticketId, $message]);
    }
}
