<?php
/**
 * Help Desk LAN - Reports & Audit logs Controller
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Database;
use App\Models\TicketModel;
use App\Models\DepartmentModel;
use App\Models\CategoryModel;
use App\Helpers\Auditor;
use PDO;

class ReportController extends Controller {

    /**
     * Show report query form.
     */
    public function index(Request $request): void {
        $this->authorize(['admin', 'technician']);

        $departments = DepartmentModel::getAll();
        $categories = CategoryModel::getAll();
        
        $db = Database::getConnection();
        $statuses = $db->query("SELECT * FROM statuses")->fetchAll();
        $priorities = $db->query("SELECT * FROM priorities")->fetchAll();
        $technicians = $db->query("SELECT id, first_name, last_name FROM users WHERE role = 'technician' AND status = 'active'")->fetchAll();

        $this->render('reports/index', [
            'departments' => $departments,
            'categories' => $categories,
            'statuses' => $statuses,
            'priorities' => $priorities,
            'technicians' => $technicians
        ], 'Generador de Reportes');
    }

    /**
     * Export reports based on queries in CSV, Excel or HTML Print.
     */
    public function export(Request $request): void {
        $this->authorize(['admin', 'technician']);

        $format = $request->post('format', 'csv');
        
        // Setup filter parameters
        $filters = [
            'status_id' => $request->post('status_id'),
            'priority_id' => $request->post('priority_id'),
            'category_id' => $request->post('category_id'),
            'department_id' => $request->post('department_id'),
            'assigned_technician_id' => $request->post('assigned_technician_id')
        ];

        // Date range filters
        $db = Database::getConnection();
        $sql = "SELECT t.*, 
                       d.name as department_name, 
                       c.name as category_name, 
                       p.name as priority_name,
                       s.name as status_name,
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

        // Dynamic WHERE conditions build
        foreach ($filters as $key => $val) {
            if (!empty($val)) {
                $where[] = "t.{$key} = :{$key}";
                $params[$key] = (int)$val;
            }
        }

        $startDate = $request->post('start_date');
        $endDate = $request->post('end_date');

        if (!empty($startDate)) {
            $where[] = "t.created_at >= :start_date";
            $params['start_date'] = $startDate . ' 00:00:00';
        }
        if (!empty($endDate)) {
            $where[] = "t.created_at <= :end_date";
            $params['end_date'] = $endDate . ' 23:59:59';
        }

        if (count($where) > 0) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY t.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $tickets = $stmt->fetchAll();

        $currentUser = $this->session->get('user');
        Auditor::log($currentUser['id'], 'REPORT_EXPORT', "Exportación de reporte en formato {$format}");

        // Handle Export formats
        if ($format === 'csv') {
            $this->exportCSV($tickets);
        } elseif ($format === 'excel') {
            $this->exportExcel($tickets);
        } else {
            // HTML / Print PDF layout view
            $this->render('reports/print', [
                'tickets' => $tickets
            ], 'Imprimir Reporte de Tickets');
        }
    }

    /**
     * Stream CSV file.
     */
    private function exportCSV(array $tickets): void {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=reporte_tickets_' . date('Ymd_His') . '.csv');
        
        // Output UTF-8 BOM for Excel compatibility
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');
        
        // headers
        fputcsv($output, [
            'Ticket #', 'Título', 'Solicitante', 'Departamento', 
            'Categoría', 'Prioridad', 'Estado', 'Técnico Asignado', 
            'Tiempo de Trabajo (Min)', 'Fecha Creación', 'Fecha Cierre'
        ]);

        foreach ($tickets as $t) {
            fputcsv($output, [
                $t['ticket_number'],
                $t['title'],
                $t['req_first'] . ' ' . $t['req_last'],
                $t['department_name'] ?? 'General',
                $t['category_name'] ?? 'General',
                $t['priority_name'],
                $t['status_name'],
                $t['tech_first'] ? ($t['tech_first'] . ' ' . $t['tech_last']) : 'Sin Asignar',
                $t['time_spent'],
                $t['created_at'],
                $t['closed_at'] ?? 'Abierto'
            ]);
        }
        fclose($output);
        exit;
    }

    /**
     * Stream Excel file (via pure HTML Table).
     */
    private function exportExcel(array $tickets): void {
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=reporte_tickets_' . date('Ymd_His') . '.xls');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
        echo '<table border="1">';
        echo '<tr>
                <th style="background-color: #0d6efd; color: white;">Ticket #</th>
                <th style="background-color: #0d6efd; color: white;">Título</th>
                <th style="background-color: #0d6efd; color: white;">Solicitante</th>
                <th style="background-color: #0d6efd; color: white;">Departamento</th>
                <th style="background-color: #0d6efd; color: white;">Categoría</th>
                <th style="background-color: #0d6efd; color: white;">Prioridad</th>
                <th style="background-color: #0d6efd; color: white;">Estado</th>
                <th style="background-color: #0d6efd; color: white;">Técnico Asignado</th>
                <th style="background-color: #0d6efd; color: white;">Tiempo Trabajado (Min)</th>
                <th style="background-color: #0d6efd; color: white;">Fecha Creación</th>
                <th style="background-color: #0d6efd; color: white;">Fecha Cierre</th>
              </tr>';

        foreach ($tickets as $t) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($t['ticket_number']) . '</td>';
            echo '<td>' . htmlspecialchars($t['title']) . '</td>';
            echo '<td>' . htmlspecialchars($t['req_first'] . ' ' . $t['req_last']) . '</td>';
            echo '<td>' . htmlspecialchars($t['department_name'] ?? 'General') . '</td>';
            echo '<td>' . htmlspecialchars($t['category_name'] ?? 'General') . '</td>';
            echo '<td>' . htmlspecialchars($t['priority_name']) . '</td>';
            echo '<td>' . htmlspecialchars($t['status_name']) . '</td>';
            echo '<td>' . htmlspecialchars($t['tech_first'] ? ($t['tech_first'] . ' ' . $t['tech_last']) : 'Sin Asignar') . '</td>';
            echo '<td>' . (int)$t['time_spent'] . '</td>';
            echo '<td>' . htmlspecialchars($t['created_at']) . '</td>';
            echo '<td>' . htmlspecialchars($t['closed_at'] ?? 'Abierto') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        exit;
    }

    /**
     * Show Audit Logs view (Admin only).
     */
    public function auditLogs(Request $request): void {
        $this->authorize(['admin']);

        $db = Database::getConnection();
        $logs = $db->query("
            SELECT a.*, u.username, u.first_name, u.last_name 
            FROM audit_logs a 
            LEFT JOIN users u ON a.user_id = u.id 
            ORDER BY a.created_at DESC 
            LIMIT 200
        ")->fetchAll();

        $this->render('reports/audit', ['logs' => $logs], 'Historial de Auditoría');
    }
}
