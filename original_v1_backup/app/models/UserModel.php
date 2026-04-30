<?php
// app/models/UserModel.php
require_once __DIR__ . '/../../config/db.php';

class UserModel {
    private PDO $db;
    public function __construct() { $this->db = getDB(); }

    public function findByEmail(string $email): ?array {
        $st = $this->db->prepare('SELECT * FROM `user` WHERE email = ? LIMIT 1');
        $st->execute([$email]);
        return $st->fetch() ?: null;
    }

    public function findById(int $id): ?array {
        $st = $this->db->prepare(
            'SELECT user_id, full_name, email, user_type,
                    must_change_pwd, is_first_login, profile_complete,
                    gender, department, phone, address, date_of_birth, created_at
             FROM `user` WHERE user_id = ? LIMIT 1');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public function create(array $d): int {
        $st = $this->db->prepare(
            'INSERT INTO `user`
               (full_name, email, password_hash, user_type,
                must_change_pwd, is_first_login, profile_complete, department, gender, phone)
             VALUES (?,?,?,?,?,?,?,?,?,?)');
        $st->execute([
            $d['full_name'],
            $d['email'],
            $d['password_hash'],
            $d['user_type'],
            $d['must_change_pwd']    ?? 0,
            $d['is_first_login']     ?? 0,
            $d['profile_complete']   ?? 0,
            $d['department']         ?? null,
            $d['gender']             ?? null,
            $d['phone']              ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function changePassword(int $userId, string $hash): void {
        $this->db->prepare(
            'UPDATE `user`
             SET password_hash = ?, must_change_pwd = 0, is_first_login = 0
             WHERE user_id = ?')
        ->execute([$hash, $userId]);
    }

    public function updateProfile(int $userId, array $d): void {
        $this->db->prepare(
            'UPDATE `user`
             SET full_name = ?, gender = ?, phone = ?, department = ?,
                 address = ?, date_of_birth = ?, is_first_login = 0, profile_complete = 1
             WHERE user_id = ?')
        ->execute([
            $d['full_name']     ?? null,
            $d['gender']        ?? null,
            $d['phone']         ?? null,
            $d['department']    ?? null,
            $d['address']       ?? null,
            $d['date_of_birth'] ?? null,
            $userId,
        ]);
    }

    public function isEmailTaken(string $email, ?int $excludeId = null): bool {
        if ($excludeId !== null) {
            $st = $this->db->prepare('SELECT COUNT(*) FROM `user` WHERE email = ? AND user_id != ?');
            $st->execute([$email, $excludeId]);
        } else {
            $st = $this->db->prepare('SELECT COUNT(*) FROM `user` WHERE email = ?');
            $st->execute([$email]);
        }
        return (int)$st->fetchColumn() > 0;
    }

    public function getAll(?string $role = null): array {
        if ($role) {
            $st = $this->db->prepare(
                'SELECT user_id, full_name, email, user_type, is_first_login, created_at
                 FROM `user` WHERE user_type = ? ORDER BY created_at DESC');
            $st->execute([$role]);
        } else {
            $st = $this->db->query(
                'SELECT user_id, full_name, email, user_type, is_first_login, created_at
                 FROM `user` ORDER BY created_at DESC');
        }
        return $st->fetchAll();
    }

    public function countByRole(): array {
        $st  = $this->db->query(
            'SELECT user_type, COUNT(*) AS total FROM `user` GROUP BY user_type');
        $out = [];
        foreach ($st->fetchAll() as $row)
            $out[$row['user_type']] = (int)$row['total'];
        return $out;
    }

    public function delete(int $id): void {
        $this->db->prepare('DELETE FROM `user` WHERE user_id = ?')->execute([$id]);
    }
}
