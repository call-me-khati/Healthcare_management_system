<?php
// app/models/NurseModel.php
require_once __DIR__ . '/../../config/db.php';

class NurseModel {
    private PDO $db;
    public function __construct() { $this->db = getDB(); }

    public function create(array $d): int {
        $st = $this->db->prepare('INSERT INTO `nurses` (user_id, employee_id) VALUES (?,?)');
        $st->execute([$d['user_id'], $d['employee_id']]);
        return (int)$this->db->lastInsertId();
    }

    public function findByUserId(int $userId): ?array {
        $st = $this->db->prepare(
            'SELECT n.*, u.email, u.is_first_login, u.date_of_birth, u.address
             FROM `nurses` n
             JOIN `user` u ON u.user_id = n.user_id
             WHERE n.user_id = ? LIMIT 1');
        $st->execute([$userId]);
        return $st->fetch() ?: null;
    }

    public function completeProfile(int $userId, array $d): void {
        $this->db->prepare(
            'UPDATE `nurses`
             SET full_name=?, department=?, contact_number=?, gender=?, address=?
             WHERE user_id=?')
        ->execute([
            $d['full_name'], $d['department'],
            $d['contact_number'], $d['gender'],
            $d['address'] ?? null,
            $userId,
        ]);
    }

    public function isEmployeeIdTaken(string $empId, ?int $excludeUserId = null): bool {
        $sql = 'SELECT COUNT(*) FROM `nurses` WHERE employee_id = ?';
        $params = [$empId];
        if ($excludeUserId) { $sql .= ' AND user_id != ?'; $params[] = $excludeUserId; }
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return (int)$st->fetchColumn() > 0;
    }

    public function getAll(): array {
        return $this->db->query(
            'SELECT n.*, u.email, u.is_first_login
             FROM `nurses` n
             JOIN `user` u ON u.user_id = n.user_id
             ORDER BY n.full_name, n.employee_id')->fetchAll();
    }
}
