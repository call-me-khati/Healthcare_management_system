<?php
// app/models/StudentModel.php
require_once __DIR__ . '/../../config/db.php';

class StudentModel {
    private PDO $db;
    public function __construct() { $this->db = getDB(); }

    public function create(array $d): int {
        $uid = $this->generateUid();
        $st = $this->db->prepare(
            'INSERT INTO `students`
               (user_id, student_uid, full_name, email, course, year_level, contact_number)
             VALUES (?,?,?,?,?,?,?)');
        $st->execute([
            $d['user_id'], $uid, $d['full_name'], $d['email'],
            $d['course'], $d['year_level'], $d['contact_number'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    private function generateUid(): string {
        $year = date('Y');
        $st = $this->db->prepare(
            "SELECT COUNT(*) FROM `students` WHERE student_uid LIKE ?");
        $st->execute(["STU-$year-%"]);
        $n = (int)$st->fetchColumn() + 1;
        return sprintf('STU-%s-%03d', $year, $n);
    }

    public function findByUserId(int $userId): ?array {
        $st = $this->db->prepare(
            'SELECT s.*, u.email AS user_email, u.is_first_login,
                    u.address AS user_address, u.gender, u.date_of_birth
             FROM `students` s
             JOIN `user` u ON u.user_id = s.user_id
             WHERE s.user_id = ? LIMIT 1');
        $st->execute([$userId]);
        return $st->fetch() ?: null;
    }

    public function findById(int $studentId): ?array {
        $st = $this->db->prepare(
            'SELECT s.*, u.email AS user_email
             FROM `students` s
             JOIN `user` u ON u.user_id = s.user_id
             WHERE s.student_id = ? LIMIT 1');
        $st->execute([$studentId]);
        return $st->fetch() ?: null;
    }

    public function updateProfile(int $userId, array $d): void {
        $this->db->prepare(
            'UPDATE `students`
             SET full_name=?, course=?, year_level=?, contact_number=?,
                 blood_group=?, emergency_contact_name=?, emergency_contact_phone=?,
                 medical_history=?
             WHERE user_id=?')
        ->execute([
            $d['full_name'],  $d['course'],       $d['year_level'],
            $d['contact_number'], $d['blood_group'] ?? null,
            $d['emergency_contact_name'] ?? null,
            $d['emergency_contact_phone'] ?? null,
            $d['medical_history'] ?? null,
            $userId,
        ]);
    }

    /* ── Allergies ─────────────────────────────────────── */
    public function getAllergies(int $studentId): array {
        $st = $this->db->prepare(
            'SELECT a.allergy_id, a.name, pai.level
             FROM `patient_allergy_info` pai
             JOIN `allergy` a ON a.allergy_id = pai.allergy_id
             WHERE pai.student_id = ?');
        $st->execute([$studentId]);
        return $st->fetchAll();
    }

    public function getAllAllergyOptions(): array {
        return $this->db->query(
            'SELECT * FROM `allergy` ORDER BY name')->fetchAll();
    }

    public function saveAllergies(int $studentId, array $allergyIds, array $levels): void {
        $this->db->prepare('DELETE FROM `patient_allergy_info` WHERE student_id = ?')
                 ->execute([$studentId]);
        if (empty($allergyIds)) return;
        $st = $this->db->prepare(
            'INSERT INTO `patient_allergy_info` (student_id, allergy_id, level) VALUES (?,?,?)');
        foreach ($allergyIds as $i => $aid) {
            $level = $levels[$i] ?? 'Mild';
            $st->execute([$studentId, (int)$aid, $level]);
        }
    }

    public function getAll(): array {
        return $this->db->query(
            'SELECT s.student_id, s.student_uid, s.full_name, s.email, s.course,
                    s.year_level, s.contact_number, s.blood_group, s.created_at,
                    u.is_first_login
             FROM `students` s
             JOIN `user` u ON u.user_id = s.user_id
             ORDER BY s.full_name')->fetchAll();
    }

    public function searchByUid(string $uid): ?array {
        $st = $this->db->prepare(
            'SELECT s.*, u.email AS user_email
             FROM `students` s
             JOIN `user` u ON u.user_id = s.user_id
             WHERE s.student_uid = ? LIMIT 1');
        $st->execute([$uid]);
        return $st->fetch() ?: null;
    }

    public function isEmailTaken(string $email): bool {
        $st = $this->db->prepare('SELECT COUNT(*) FROM `students` WHERE email = ?');
        $st->execute([$email]);
        return (int)$st->fetchColumn() > 0;
    }
}
