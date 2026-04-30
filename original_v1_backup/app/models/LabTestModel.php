<?php
// app/models/LabTestModel.php
require_once __DIR__ . '/../../config/db.php';

class LabTestModel {
    private PDO $db;
    public function __construct() { $this->db = getDB(); }

    public function create(array $d): int {
        $st = $this->db->prepare(
            'INSERT INTO `lab_tests`
               (consultation_id, student_id, doctor_id, test_name, request_date)
             VALUES (?,?,?,?,?)');
        $st->execute([
            $d['consultation_id'], $d['student_id'],
            $d['doctor_id'],       $d['test_name'],
            $d['request_date'] ?? date('Y-m-d'),
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id): ?array {
        $st = $this->db->prepare(
            'SELECT lt.*, s.full_name AS student_name, s.student_uid,
                    d.full_name AS doctor_name
             FROM `lab_tests` lt
             JOIN `students` s ON s.student_id = lt.student_id
             JOIN `doctors`  d ON d.doctor_id  = lt.doctor_id
             WHERE lt.lab_test_id = ? LIMIT 1');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public function getByStudent(int $studentId): array {
        $st = $this->db->prepare(
            'SELECT lt.*, d.full_name AS doctor_name
             FROM `lab_tests` lt
             JOIN `doctors` d ON d.doctor_id = lt.doctor_id
             WHERE lt.student_id = ?
             ORDER BY lt.request_date DESC');
        $st->execute([$studentId]);
        return $st->fetchAll();
    }

    public function getByDoctor(int $doctorId): array {
        $st = $this->db->prepare(
            'SELECT lt.*, s.full_name AS student_name, s.student_uid
             FROM `lab_tests` lt
             JOIN `students` s ON s.student_id = lt.student_id
             WHERE lt.doctor_id = ?
             ORDER BY lt.request_date DESC');
        $st->execute([$doctorId]);
        return $st->fetchAll();
    }

    public function getAll(?string $status = null): array {
        $sql = 'SELECT lt.*, s.full_name AS student_name, s.student_uid,
                       d.full_name AS doctor_name
                FROM `lab_tests` lt
                JOIN `students` s ON s.student_id = lt.student_id
                JOIN `doctors`  d ON d.doctor_id  = lt.doctor_id
                WHERE 1=1';
        $params = [];
        if ($status) { $sql .= ' AND lt.status = ?'; $params[] = $status; }
        $sql .= ' ORDER BY lt.request_date DESC';
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    /** Doctor/nurse marks result ready */
    public function updateResult(int $id, string $result, string $status = 'Completed'): void {
        $this->db->prepare(
            'UPDATE `lab_tests`
             SET result=?, result_date=CURDATE(), status=?
             WHERE lab_test_id=?')
        ->execute([$result, $status, $id]);
    }

    public function updateStatus(int $id, string $status): void {
        $this->db->prepare('UPDATE `lab_tests` SET status=? WHERE lab_test_id=?')
        ->execute([$status, $id]);
    }

    /** Patient clicks "Test Done" */
    public function markPatientDone(int $id, int $studentId): bool {
        $st = $this->db->prepare(
            "UPDATE `lab_tests`
             SET patient_done_at=NOW(), status='Done'
             WHERE lab_test_id=? AND student_id=? AND status='Requested'");
        $st->execute([$id, $studentId]);
        return $st->rowCount() > 0;
    }

    /** Get pending (Requested, not yet Done) for daily reminders */
    public function getPendingForReminder(): array {
        return $this->db->query(
            "SELECT lt.*, s.full_name AS student_name, u.user_id
             FROM `lab_tests` lt
             JOIN `students` s ON s.student_id = lt.student_id
             JOIN `user`     u ON u.user_id     = s.user_id
             WHERE lt.status = 'Requested'
               AND lt.patient_done_at IS NULL")->fetchAll();
    }
}
