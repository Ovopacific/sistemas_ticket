<?php
/**
 * Help Desk LAN - Comment Model
 */

namespace App\Models;

use App\Core\Database;
use PDO;

class CommentModel {
    
    public static function getByTicketId(int $ticketId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT c.*, 
                                     u.first_name, u.last_name, u.role, u.avatar_path
                              FROM comments c
                              LEFT JOIN users u ON c.user_id = u.id
                              WHERE c.ticket_id = ?
                              ORDER BY c.created_at ASC");
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll();
    }

    public static function getById(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM comments WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO comments (ticket_id, user_id, comment_text, attachment_path, attachment_filename) 
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            (int)$data['ticket_id'],
            (int)$data['user_id'],
            $data['comment_text'],
            $data['attachment_path'] ?? null,
            $data['attachment_filename'] ?? null
        ]);

        return (int)$db->lastInsertId();
    }

    public static function update(int $id, string $text): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE comments SET comment_text = ? WHERE id = ?");
        return $stmt->execute([$text, $id]);
    }

    public static function delete(int $id): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM comments WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
