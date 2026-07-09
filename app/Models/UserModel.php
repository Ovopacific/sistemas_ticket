<?php
/**
 * Help Desk LAN - User Model
 */

namespace App\Models;

use App\Core\Database;
use PDO;

class UserModel {
    
    public static function getAll(): array {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT u.*, d.name as department_name 
                            FROM users u 
                            LEFT JOIN departments d ON u.department_id = d.id 
                            ORDER BY u.created_at DESC");
        return $stmt->fetchAll();
    }

    public static function getById(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT u.*, d.name as department_name 
                              FROM users u 
                              LEFT JOIN departments d ON u.department_id = d.id 
                              WHERE u.id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function getByUsername(string $username): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO users (username, password, email, first_name, last_name, role, department_id, phone, position, avatar_path, status) 
                              VALUES (:username, :password, :email, :first_name, :last_name, :role, :department_id, :phone, :position, :avatar_path, :status)");
        
        $stmt->execute([
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'email' => $data['email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'role' => $data['role'] ?? 'user',
            'department_id' => !empty($data['department_id']) ? (int)$data['department_id'] : null,
            'phone' => $data['phone'] ?? null,
            'position' => $data['position'] ?? null,
            'avatar_path' => $data['avatar_path'] ?? null,
            'status' => $data['status'] ?? 'active'
        ]);

        $userId = (int)$db->lastInsertId();

        // If the role is technician, also sync in the technicians table
        if (($data['role'] ?? 'user') === 'technician') {
            $techStmt = $db->prepare("INSERT INTO technicians (user_id, specialty, status) VALUES (?, ?, 'available')");
            $techStmt->execute([$userId, $data['specialty'] ?? 'Soporte General']);
        }

        return $userId;
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getConnection();
        
        // Base sql fields
        $sql = "UPDATE users SET 
                email = :email, 
                first_name = :first_name, 
                last_name = :last_name, 
                role = :role, 
                department_id = :department_id, 
                phone = :phone, 
                position = :position, 
                status = :status";
        
        $params = [
            'email' => $data['email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'role' => $data['role'],
            'department_id' => !empty($data['department_id']) ? (int)$data['department_id'] : null,
            'phone' => $data['phone'] ?? null,
            'position' => $data['position'] ?? null,
            'status' => $data['status'],
            'id' => $id
        ];

        // Conditional values
        if (!empty($data['password'])) {
            $sql .= ", password = :password";
            $params['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if (isset($data['avatar_path'])) {
            $sql .= ", avatar_path = :avatar_path";
            $params['avatar_path'] = $data['avatar_path'];
        }

        $sql .= " WHERE id = :id";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute($params);

        // Sync technicians table roles
        if ($data['role'] === 'technician') {
            $techCheck = $db->prepare("SELECT 1 FROM technicians WHERE user_id = ?");
            $techCheck->execute([$id]);
            if (!$techCheck->fetch()) {
                $techStmt = $db->prepare("INSERT INTO technicians (user_id, specialty, status) VALUES (?, ?, 'available')");
                $techStmt->execute([$id, $data['specialty'] ?? 'Soporte General']);
            } else if (isset($data['specialty'])) {
                $techStmt = $db->prepare("UPDATE technicians SET specialty = ? WHERE user_id = ?");
                $techStmt->execute([$data['specialty'], $id]);
            }
        } else {
            // Delete if role changed from technician to other
            $techDel = $db->prepare("DELETE FROM technicians WHERE user_id = ?");
            $techDel->execute([$id]);
        }

        return $result;
    }

    public static function toggleStatus(int $id): string {
        $db = Database::getConnection();
        $user = self::getById($id);
        if (!$user) return 'inactive';

        $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
        $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);

        return $newStatus;
    }

    public static function getTechnicians(): array {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT u.*, t.specialty, t.status as tech_status, d.name as department_name,
                            (SELECT COUNT(*) FROM tickets WHERE assigned_technician_id = u.id AND status_id NOT IN (7, 8, 9)) as active_tickets_count
                            FROM users u 
                            INNER JOIN technicians t ON u.id = t.user_id 
                            LEFT JOIN departments d ON u.department_id = d.id 
                            WHERE u.status = 'active'
                            ORDER BY active_tickets_count ASC");
        return $stmt->fetchAll();
    }
}
