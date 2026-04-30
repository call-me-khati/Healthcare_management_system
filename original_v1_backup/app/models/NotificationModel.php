<?php
// app/models/NotificationModel.php
require_once __DIR__ . '/../../config/db.php';

class NotificationModel {
    private PDO $db;
    public function __construct() { $this->db = getDB(); }

    public function create(int $userId, string $type, string $title, string $message, ?int $relatedId = null): void {
        $this->db->prepare(
            'INSERT INTO `notifications` (user_id, type, title, message, related_id)
             VALUES (?,?,?,?,?)')
        ->execute([$userId, $type, $title, $message, $relatedId]);
    }

    public function getByUser(int $userId, bool $unreadOnly = false): array {
        $sql = 'SELECT * FROM `notifications` WHERE user_id = ?';
        if ($unreadOnly) $sql .= ' AND is_read = 0';
        $sql .= ' ORDER BY created_at DESC LIMIT 50';
        $st = $this->db->prepare($sql);
        $st->execute([$userId]);
        return $st->fetchAll();
    }

    public function countUnread(int $userId): int {
        $st = $this->db->prepare(
            'SELECT COUNT(*) FROM `notifications` WHERE user_id = ? AND is_read = 0');
        $st->execute([$userId]);
        return (int)$st->fetchColumn();
    }

    public function markRead(int $userId): void {
        $this->db->prepare(
            'UPDATE `notifications` SET is_read = 1 WHERE user_id = ?')
        ->execute([$userId]);
    }

    public function markOneRead(int $notifId, int $userId): void {
        $this->db->prepare(
            'UPDATE `notifications` SET is_read = 1 WHERE notification_id = ? AND user_id = ?')
        ->execute([$notifId, $userId]);
    }

    /** Notify all admin users */
    public function notifyAdmins(string $type, string $title, string $message): void {
        $st = $this->db->prepare(
            "SELECT user_id FROM `user` WHERE user_type = 'admin'");
        $st->execute();
        foreach ($st->fetchAll() as $admin)
            $this->create((int)$admin['user_id'], $type, $title, $message);
    }
}
