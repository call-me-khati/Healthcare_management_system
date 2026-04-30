<?php
// app/models/ConsultationModel.php
require_once __DIR__ . '/../../config/db.php';

class ConsultationModel {
    private PDO $db;
    public function __construct() { $this->db = getDB(); }

    public function create(array $d): int {
        $st = $this->db->prepare(
            'INSERT INTO `consultations`
               (appointment_id, doctor_id, student_id,
                consultation_date, diagnosis, consultation_notes,
                follow_up_date, follow_up_notes)
             VALUES (?,?,?,?,?,?,?,?)');
        $st->execute([
            $d['appointment_id'],     $d['doctor_id'],
            $d['student_id'],         $d['consultation_date'],
            $d['diagnosis'] ?? null,  $d['notes'] ?? null,
            $d['follow_up_date'] ?? null,
            $d['follow_up_notes'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id): ?array {
        $st = $this->db->prepare(
            'SELECT c.*, d.full_name AS doctor_name,
                    s.full_name AS student_name, s.student_uid
             FROM `consultations` c
             JOIN `doctors`  d ON d.doctor_id  = c.doctor_id
             JOIN `students` s ON s.student_id = c.student_id
             WHERE c.consultation_id = ? LIMIT 1');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public function findByAppointment(int $apptId): ?array {
        $st = $this->db->prepare(
            'SELECT * FROM `consultations` WHERE appointment_id = ? LIMIT 1');
        $st->execute([$apptId]);
        return $st->fetch() ?: null;
    }

    public function getByStudent(int $studentId): array {
        $st = $this->db->prepare(
            'SELECT c.*, d.full_name AS doctor_name, d.specialization
             FROM `consultations` c
             JOIN `doctors` d ON d.doctor_id = c.doctor_id
             WHERE c.student_id = ?
             ORDER BY c.consultation_date DESC');
        $st->execute([$studentId]);
        return $st->fetchAll();
    }

    public function getByDoctor(int $doctorId): array {
        $st = $this->db->prepare(
            'SELECT c.*, s.full_name AS student_name, s.student_uid
             FROM `consultations` c
             JOIN `students` s ON s.student_id = c.student_id
             WHERE c.doctor_id = ?
             ORDER BY c.consultation_date DESC');
        $st->execute([$doctorId]);
        return $st->fetchAll();
    }

    /* Full patient medical record for doctor/nurse view */
    public function getFullRecord(int $studentId): array {
        $st = $this->db->prepare(
            'SELECT c.*,
                    d.full_name AS doctor_name,
                    d.specialization,
                    a.appointment_date, a.appointment_time, a.priority
             FROM `consultations` c
             JOIN `doctors`      d ON d.doctor_id      = c.doctor_id
             JOIN `appointments` a ON a.appointment_id = c.appointment_id
             WHERE c.student_id = ?
             ORDER BY c.consultation_date DESC');
        $st->execute([$studentId]);
        return $st->fetchAll();
    }

    public function getPrescriptions(int $consultationId): array {
        $st = $this->db->prepare(
            'SELECT p.*, m.medicine_name, m.generic_name, m.unit
             FROM `prescriptions` p
             JOIN `medicines` m ON m.medicine_id = p.medicine_id
             WHERE p.consultation_id = ?');
        $st->execute([$consultationId]);
        return $st->fetchAll();
    }

    public function addPrescription(int $consultationId, array $d): void {
        $this->db->prepare(
            'INSERT INTO `prescriptions`
               (consultation_id, medicine_id, dosage_amount,
                dosage_unit, frequency, duration, instruction)
             VALUES (?,?,?,?,?,?,?)')
        ->execute([
            $consultationId,      $d['medicine_id'],
            $d['dosage_amount'],  $d['dosage_unit'] ?? null,
            $d['frequency'],      $d['duration'],
            $d['instruction'] ?? null,
        ]);
    }

    public function commonDiagnoses(): array {
        $st = $this->db->query(
            'SELECT diagnosis, COUNT(*) AS cnt
             FROM `consultations`
             WHERE diagnosis IS NOT NULL AND diagnosis != \'\'
             GROUP BY diagnosis
             ORDER BY cnt DESC
             LIMIT 10');
        return $st->fetchAll();
    }
}
