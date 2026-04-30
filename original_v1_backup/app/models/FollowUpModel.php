<?php
// app/models/FollowUpModel.php
require_once __DIR__ . '/../../config/db.php';

class FollowUpModel {
    private PDO $db;
    public function __construct() { $this->db = getDB(); }

    public function create(array $d): int {
        $st = $this->db->prepare(
            'INSERT INTO `follow_ups`
               (consultation_id, student_id, doctor_id, followup_date, notes)
             VALUES (?,?,?,?,?)');
        $st->execute([
            $d['consultation_id'], $d['student_id'],
            $d['doctor_id'],       $d['followup_date'],
            $d['notes'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getByStudent(int $studentId): array {
        $st = $this->db->prepare(
            'SELECT fu.*, d.full_name AS doctor_name
             FROM `follow_ups` fu
             JOIN `doctors` d ON d.doctor_id = fu.doctor_id
             WHERE fu.student_id = ?
             ORDER BY fu.followup_date ASC');
        $st->execute([$studentId]);
        return $st->fetchAll();
    }

    public function getUpcoming(int $studentId): array {
        $st = $this->db->prepare(
            "SELECT fu.*, d.full_name AS doctor_name
             FROM `follow_ups` fu
             JOIN `doctors` d ON d.doctor_id = fu.doctor_id
             WHERE fu.student_id = ?
               AND fu.followup_date >= CURDATE()
               AND fu.status NOT IN ('Completed','Missed')
             ORDER BY fu.followup_date ASC");
        $st->execute([$studentId]);
        return $st->fetchAll();
    }

    public function linkAppointment(int $followupId, int $appointmentId): void {
        $this->db->prepare(
            "UPDATE `follow_ups`
             SET appointment_id=?, status='Booked'
             WHERE followup_id=?")
        ->execute([$appointmentId, $followupId]);
    }

    public function updateStatus(int $id, string $status): void {
        $this->db->prepare(
            'UPDATE `follow_ups` SET status=? WHERE followup_id=?')
        ->execute([$status, $id]);
    }

    public function getDueReminders(): array {
        return $this->db->query(
            "SELECT fu.*, u.user_id, s.full_name AS student_name
             FROM `follow_ups` fu
             JOIN `students` s ON s.student_id = fu.student_id
             JOIN `user`     u ON u.user_id     = s.user_id
             WHERE fu.followup_date = DATE_ADD(CURDATE(), INTERVAL 2 DAY)
               AND fu.status = 'Scheduled'")->fetchAll();
    }
}
