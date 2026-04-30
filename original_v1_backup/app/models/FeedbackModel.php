<?php
// app/models/FeedbackModel.php
require_once __DIR__ . '/../../config/db.php';

class FeedbackModel {
    private PDO $db;
    public function __construct() { $this->db = getDB(); }

    public function create(array $d): int {
        $st = $this->db->prepare(
            'INSERT INTO `feedback`
               (student_id, type, subject, message)
             VALUES (?,?,?,?)');
        $st->execute([
            $d['student_id'], $d['type'],
            $d['subject'],    $d['message'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    /* ADMIN-ONLY: get all feedback */
    public function getAll(?string $type = null, ?string $status = null): array {
        $sql = 'SELECT f.*, s.full_name AS student_name, s.student_uid
                FROM `feedback` f
                JOIN `students` s ON s.student_id = f.student_id
                WHERE 1=1';
        $params = [];
        if ($type)   { $sql .= ' AND f.type = ?';   $params[] = $type; }
        if ($status) { $sql .= ' AND f.status = ?'; $params[] = $status; }
        $sql .= ' ORDER BY f.created_at DESC';
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public function getByStudent(int $studentId): array {
        $st = $this->db->prepare(
            'SELECT * FROM `feedback`
             WHERE student_id = ?
             ORDER BY created_at DESC');
        $st->execute([$studentId]);
        return $st->fetchAll();
    }

    public function updateStatus(int $id, string $status, ?string $notes = null): void {
        $this->db->prepare(
            'UPDATE `feedback` SET status=?, admin_notes=? WHERE feedback_id=?')
        ->execute([$status, $notes, $id]);
    }

    public function countOpen(): int {
        return (int)$this->db->query(
            "SELECT COUNT(*) FROM `feedback` WHERE status = 'Open'")->fetchColumn();
    }
}
