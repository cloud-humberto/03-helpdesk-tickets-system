<?php
declare(strict_types=1);

namespace HelpDesk\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dbPath = __DIR__ . '/../../database/helpdesk.sqlite';
            $isNew = !file_exists($dbPath);

            try {
                self::$instance = new PDO('sqlite:' . $dbPath);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$instance->exec('PRAGMA foreign_keys = ON;');

                if ($isNew || filesize($dbPath) === 0) {
                    self::setup(self::$instance);
                }
            } catch (PDOException $e) {
                die("HelpDesk Database Connection Error: " . htmlspecialchars($e->getMessage()));
            }
        }

        return self::$instance;
    }

    private static function setup(PDO $pdo): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                role TEXT NOT NULL CHECK(role IN ('admin', 'client')),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS tickets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                description TEXT NOT NULL,
                priority TEXT NOT NULL CHECK(priority IN ('low', 'medium', 'high', 'urgent')),
                status TEXT NOT NULL CHECK(status IN ('open', 'in_progress', 'resolved', 'closed')) DEFAULT 'open',
                attachment_name TEXT,
                attachment_path TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS ticket_messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ticket_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                message TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            );

            -- Test Users (Password for both: password123)
            INSERT INTO users (name, email, password_hash, role) VALUES
            ('Technical Support (Admin)', 'admin@company.com', '" . password_hash('password123', PASSWORD_BCRYPT) . "', 'admin'),
            ('Sarah Jenkins (Client)', 'sarah@client.com', '" . password_hash('password123', PASSWORD_BCRYPT) . "', 'client');

            -- Sample Demonstration Ticket
            INSERT INTO tickets (user_id, title, description, priority, status) VALUES
            (2, 'Payment Gateway Webhook Timeout', 'During checkout with Stripe, the webhook callback fails with HTTP 504 gateway timeout.', 'high', 'in_progress');

            INSERT INTO ticket_messages (ticket_id, user_id, message) VALUES
            (1, 2, 'Ticket submitted. System logs attached for diagnosis.'),
            (1, 1, 'Hello Sarah, our engineering team has isolated the network latency issue and is deploying a patch.');
        ";

        $pdo->exec($sql);
    }
}
