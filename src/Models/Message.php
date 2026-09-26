<?php
declare(strict_types=1);

namespace HelpDesk\Models;

use HelpDesk\Core\Database;

class Message
{
    public static function getByTicket(int $ticketId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT m.*, u.name as user_name, u.role as user_role
            FROM ticket_messages m
            JOIN users u ON m.user_id = u.id
            WHERE m.ticket_id = :ticket_id
            ORDER BY m.created_at ASC
        ");
        $stmt->execute(['ticket_id' => $ticketId]);
        return $stmt->fetchAll();
    }

    public static function create(int $ticketId, int $userId, string $message): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO ticket_messages (ticket_id, user_id, message)
            VALUES (:ticket_id, :user_id, :message)
        ");
        $stmt->execute([
            'ticket_id' => $ticketId,
            'user_id'   => $userId,
            'message'   => trim($message)
        ]);

        // Atualiza timestamp do ticket
        $upd = $db->prepare("UPDATE tickets SET updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $upd->execute(['id' => $ticketId]);

        return (int) $db->lastInsertId();
    }
}
