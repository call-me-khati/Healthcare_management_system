<?php
// app/models/DoctorModel.php
require_once __DIR__ . '/../../config/db.php';

class DoctorModel {
    private PDO $db;
    public function __construct() { $this->db = getDB(); }

    public function create(array $d): int {
        $st = $this->db->prepare('INSERT INTO `doctors` (user_id, employee_id) VALUES (?,?)');
        $st->execute([$d['user_id'], $d['employee_id']]);
        return (int)$this->db->lastInsertId();
    }

    public function findByUserId(int $userId): ?array {
        $st = $this->db->prepare(
            'SELECT d.*, u.email, u.is_first_login, u.date_of_birth, u.address
             FROM `doctors` d
             JOIN `user` u ON u.user_id = d.user_id
             WHERE d.user_id = ? LIMIT 1');
        $st->execute([$userId]);
        return $st->fetch() ?: null;
    }

    public function findById(int $doctorId): ?array {
        $st = $this->db->prepare(
            'SELECT d.*, u.email
             FROM `doctors` d
             JOIN `user` u ON u.user_id = d.user_id
             WHERE d.doctor_id = ? LIMIT 1');
        $st->execute([$doctorId]);
        return $st->fetch() ?: null;
    }

    public function completeProfile(int $userId, array $d): void {
        $this->db->prepare(
            'UPDATE `doctors`
             SET full_name=?, specialization=?, department=?,
                 consultation_fee=?, contact_number=?, gender=?, bio=?, address=?
             WHERE user_id=?')
        ->execute([
            $d['full_name'],     $d['specialization'],
            $d['department'],    $d['consultation_fee'],
            $d['contact_number'],$d['gender'],
            $d['bio'],           $d['address'] ?? null,
            $userId,
        ]);
    }

    public function updateAvailabilityStatus(int $userId, string $status): void {
        $this->db->prepare(
            'UPDATE `doctors` SET availability_status = ? WHERE user_id = ?')
        ->execute([$status, $userId]);
    }

    public function isEmployeeIdTaken(string $empId, ?int $excludeUserId = null): bool {
        $sql = 'SELECT COUNT(*) FROM `doctors` WHERE employee_id = ?';
        $params = [$empId];
        if ($excludeUserId) { $sql .= ' AND user_id != ?'; $params[] = $excludeUserId; }
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return (int)$st->fetchColumn() > 0;
    }

    public function getAvailableDoctors(): array {
        return $this->db->query(
            "SELECT d.doctor_id, d.full_name, d.specialization,
                    d.department, d.consultation_fee,
                    d.availability_status, d.bio
             FROM `doctors` d
             WHERE d.full_name IS NOT NULL
               AND d.availability_status != 'On Leave'
             ORDER BY d.full_name")->fetchAll();
    }

    public function getAll(): array {
        return $this->db->query(
            'SELECT d.*, u.email, u.is_first_login
             FROM `doctors` d
             JOIN `user` u ON u.user_id = d.user_id
             ORDER BY d.full_name, d.employee_id')->fetchAll();
    }

    public function getAppointments(int $userId, ?string $status = null): array {
        $sql = 'SELECT a.*, s.full_name AS student_name,
                       s.contact_number AS student_phone, s.student_uid,
                       s.blood_group
                FROM `appointments` a
                JOIN `students` s ON s.student_id = a.student_id
                JOIN `doctors`  d ON d.doctor_id  = a.doctor_id
                WHERE d.user_id = ?';
        $params = [$userId];
        if ($status) { $sql .= ' AND a.status = ?'; $params[] = $status; }
        $sql .= ' ORDER BY a.priority DESC, a.appointment_date ASC, a.appointment_time ASC';
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public function countStats(int $userId): array {
        $st = $this->db->prepare(
            'SELECT status, COUNT(*) AS cnt
             FROM `appointments` a
             JOIN `doctors` d ON d.doctor_id = a.doctor_id
             WHERE d.user_id = ?
             GROUP BY status');
        $st->execute([$userId]);
        $out = ['Pending'=>0,'Confirmed'=>0,'Completed'=>0,'Cancelled'=>0];
        foreach ($st->fetchAll() as $r) $out[$r['status']] = (int)$r['cnt'];
        return $out;
    }
}
