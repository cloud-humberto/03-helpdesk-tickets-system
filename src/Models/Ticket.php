<?php
declare(strict_types=1);

namespace HelpDesk\Models;

use HelpDesk\Core\Database;
use PDO;

class Ticket
{
    public static function all(?int $userId = null, ?string $status = null, ?string $priority = null): array
    {
        $db = Database::getConnection();
        $conditions = [];
        $params = [];

        if ($userId !== null) {
            $conditions[] = "t.user_id = :user_id";
            $params['user_id'] = $userId;
        }

        if ($status) {
            $conditions[] = "t.status = :status";
            $params['status'] = $status;
        }

        if ($priority) {
            $conditions[] = "t.priority = :priority";
            $params['priority'] = $priority;
        }

        $where = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

        $sql = "
            SELECT t.*, u.name as user_name, u.email as user_email
            FROM tickets t
            JOIN users u ON t.user_id = u.id
            {$where}
            ORDER BY 
                CASE t.status 
                    WHEN 'open' THEN 1 
                    WHEN 'in_progress' THEN 2 
                    WHEN 'resolved' THEN 3 
                    ELSE 4 
                END,
                t.updated_at DESC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT t.*, u.name as user_name, u.email as user_email
            FROM tickets t
            JOIN users u ON t.user_id = u.id
            WHERE t.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(
        int $userId,
        string $title,
        string $description,
        string $priority,
        ?string $attachName = null,
        ?string $attachPath = null
    ): int {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO tickets (user_id, title, description, priority, status, attachment_name, attachment_path)
            VALUES (:user_id, :title, :description, :priority, 'open', :attachment_name, :attachment_path)
        ");

        $stmt->execute([
            'user_id'         => $userId,
            'title'           => trim($title),
            'description'     => trim($description),
            'priority'        => $priority,
            'attachment_name' => $attachName,
            'attachment_path' => $attachPath
        ]);

        return (int) $db->lastInsertId();
    }

    public static function updateStatus(int $id, string $status): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE tickets 
            SET status = :status, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public static function getStats(?int $userId = null): array
    {
        $db = Database::getConnection();
        $where = $userId !== null ? "WHERE user_id = :user_id" : "";
        $params = $userId !== null ? ['user_id' => $userId] : [];

        $sql = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_tickets,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tickets,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_tickets
            FROM tickets
            {$where}
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch();

        return [
            'total'       => (int)($res['total'] ?? 0),
            'open'        => (int)($res['open_tickets'] ?? 0),
            'in_progress' => (int)($res['in_progress_tickets'] ?? 0),
            'resolved'    => (int)($res['resolved_tickets'] ?? 0),
        ];
    }
}
