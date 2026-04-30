<?php
// app/models/AppointmentModel.php
require_once __DIR__ . '/../../config/db.php';

class AppointmentModel {
    private PDO $db;
    public function __construct() { $this->db = getDB(); }

    /* ── Book ───────────────────────────────────────────── */
    public function book(array $d): int {
        $queue = $this->nextQueueNumber(
            (int)$d['doctor_id'], $d['appointment_date']);
        $st = $this->db->prepare(
            "INSERT INTO `appointments`
               (student_id, doctor_id, appointment_date,
                appointment_time, reason, priority, status, queue_number, is_followup)
             VALUES (?,?,?,?,?,?,?,?,?)");
        $st->execute([
            $d['student_id'],       $d['doctor_id'],
            $d['appointment_date'], $d['appointment_time'],
            $d['reason'] ?? null,
            $d['priority'] ?? 'Normal',
            'Pending',
            $queue,
            $d['is_followup'] ?? 0,
        ]);
        return (int)$this->db->lastInsertId();
    }

    private function nextQueueNumber(int $doctorId, string $date): int {
        $st = $this->db->prepare(
            "SELECT COALESCE(MAX(queue_number),0)+1
             FROM `appointments`
             WHERE doctor_id=? AND appointment_date=?
               AND status NOT IN ('Cancelled')");
        $st->execute([$doctorId, $date]);
        return (int)$st->fetchColumn();
    }

    /* ── Student view ───────────────────────────────────── */
    public function getByStudent(int $studentId, ?string $status = null): array {
        $sql = 'SELECT a.*, d.full_name AS doctor_name,
                       d.specialization, d.consultation_fee
                FROM `appointments` a
                JOIN `doctors` d ON d.doctor_id = a.doctor_id
                WHERE a.student_id = ?';
        $params = [$studentId];
        if ($status) { $sql .= ' AND a.status = ?'; $params[] = $status; }
        $sql .= ' ORDER BY a.appointment_date DESC, a.appointment_time DESC';
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    /* ── Single appointment ─────────────────────────────── */
    public function findById(int $id): ?array {
        $st = $this->db->prepare(
            'SELECT a.*, s.full_name AS student_name, s.student_uid,
                    s.blood_group, s.contact_number AS student_phone,
                    d.full_name AS doctor_name, d.specialization
             FROM `appointments` a
             JOIN `students` s ON s.student_id = a.student_id
             JOIN `doctors`  d ON d.doctor_id  = a.doctor_id
             WHERE a.appointment_id = ? LIMIT 1');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    /* ── Cancel (student) ───────────────────────────────── */
    public function cancelByStudent(int $apptId, int $studentId): bool {
        $st = $this->db->prepare(
            "UPDATE `appointments`
             SET status = 'Cancelled'
             WHERE appointment_id = ? AND student_id = ?
               AND status = 'Pending'");
        $st->execute([$apptId, $studentId]);
        return $st->rowCount() > 0;
    }

    /* ── Doctor / nurse updates ─────────────────────────── */
    public function updateStatus(int $apptId, string $status, ?string $notes = null): void {
        $this->db->prepare(
            'UPDATE `appointments` SET status=?, notes=? WHERE appointment_id=?')
        ->execute([$status, $notes, $apptId]);
    }

    /* ── All appointments (nurse / admin) ───────────────── */
    public function getAll(?string $status = null, ?string $date = null): array {
        $sql = 'SELECT a.*, s.full_name AS student_name, s.student_uid,
                       d.full_name AS doctor_name, d.specialization
                FROM `appointments` a
                JOIN `students` s ON s.student_id = a.student_id
                JOIN `doctors`  d ON d.doctor_id  = a.doctor_id
                WHERE 1=1';
        $params = [];
        if ($status) { $sql .= ' AND a.status = ?';           $params[] = $status; }
        if ($date)   { $sql .= ' AND a.appointment_date = ?'; $params[] = $date; }
        $sql .= ' ORDER BY a.priority DESC, a.appointment_date DESC, a.appointment_time DESC';
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    /** Today's queue for a doctor, ordered by priority then queue number */
    public function getTodayQueue(int $doctorId): array {
        $st = $this->db->prepare(
            "SELECT a.*, s.full_name AS student_name, s.student_uid, s.blood_group
             FROM `appointments` a
             JOIN `students` s ON s.student_id = a.student_id
             WHERE a.doctor_id = ? AND a.appointment_date = CURDATE()
               AND a.status IN ('Pending','Confirmed')
             ORDER BY
               FIELD(a.priority,'Emergency','Urgent','Normal'),
               a.queue_number ASC");
        $st->execute([$doctorId]);
        return $st->fetchAll();
    }

    /* ── Conflict check ─────────────────────────────────── */
    public function slotTaken(int $doctorId, string $date, string $time): bool {
        $st = $this->db->prepare(
            "SELECT COUNT(*) FROM `appointments`
             WHERE doctor_id=? AND appointment_date=?
               AND appointment_time=? AND status NOT IN ('Cancelled')");
        $st->execute([$doctorId, $date, $time]);
        return (int)$st->fetchColumn() > 0;
    }

    /* ── Stats ──────────────────────────────────────────── */
    public function countAllByStatus(): array {
        $st  = $this->db->query(
            'SELECT status, COUNT(*) AS cnt FROM `appointments` GROUP BY status');
        $out = ['Pending'=>0,'Confirmed'=>0,'Completed'=>0,'Cancelled'=>0,'total'=>0];
        foreach ($st->fetchAll() as $r) {
            $out[$r['status']]  = (int)$r['cnt'];
            $out['total']      += (int)$r['cnt'];
        }
        return $out;
    }

    public function countByStudent(int $studentId): array {
        $st = $this->db->prepare(
            'SELECT status, COUNT(*) AS cnt FROM `appointments`
             WHERE student_id = ? GROUP BY status');
        $st->execute([$studentId]);
        $out = ['Pending'=>0,'Confirmed'=>0,'Completed'=>0,'Cancelled'=>0,'total'=>0];
        foreach ($st->fetchAll() as $r) {
            $out[$r['status']]  = (int)$r['cnt'];
            $out['total']      += (int)$r['cnt'];
        }
        return $out;
    }

    public function getMonthlyStats(): array {
        $st = $this->db->query(
            "SELECT DATE_FORMAT(appointment_date,'%b %Y') AS month,
                    COUNT(*) AS total
             FROM `appointments`
             WHERE appointment_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY DATE_FORMAT(appointment_date,'%Y-%m')
             ORDER BY appointment_date ASC");
        return $st->fetchAll();
    }
}
