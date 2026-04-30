<?php
// app/models/MedicineModel.php
require_once __DIR__ . '/../../config/db.php';

class MedicineModel {
    private PDO $db;
    public function __construct() { $this->db = getDB(); }

    public function getAll(): array {
        return $this->db->query(
            'SELECT *, 
                    CASE WHEN quantity <= low_stock_threshold THEN 1 ELSE 0 END AS is_low_stock,
                    CASE WHEN expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                         THEN 1 ELSE 0 END AS expiring_soon
             FROM `medicines`
             ORDER BY medicine_name')->fetchAll();
    }

    public function findById(int $id): ?array {
        $st = $this->db->prepare('SELECT * FROM `medicines` WHERE medicine_id = ? LIMIT 1');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public function create(array $d): int {
        $st = $this->db->prepare(
            'INSERT INTO `medicines`
               (medicine_name, generic_name, category, quantity, unit,
                expiry_date, low_stock_threshold)
             VALUES (?,?,?,?,?,?,?)');
        $st->execute([
            $d['medicine_name'], $d['generic_name'] ?? null,
            $d['category'] ?? null,
            (int)$d['quantity'], $d['unit'] ?? 'tablet',
            $d['expiry_date'] ?? null,
            (int)($d['low_stock_threshold'] ?? 10),
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): void {
        $this->db->prepare(
            'UPDATE `medicines`
             SET medicine_name=?, generic_name=?, category=?, quantity=?,
                 unit=?, expiry_date=?, low_stock_threshold=?
             WHERE medicine_id=?')
        ->execute([
            $d['medicine_name'], $d['generic_name'] ?? null,
            $d['category'] ?? null,
            (int)$d['quantity'], $d['unit'] ?? 'tablet',
            $d['expiry_date'] ?? null,
            (int)($d['low_stock_threshold'] ?? 10),
            $id,
        ]);
    }

    /** Decrease stock when medicine dispensed */
    public function dispense(int $medicineId, int $qty = 1): bool {
        $st = $this->db->prepare(
            'UPDATE `medicines`
             SET quantity = quantity - ?
             WHERE medicine_id = ? AND quantity >= ?');
        $st->execute([$qty, $medicineId, $qty]);
        return $st->rowCount() > 0;
    }

    /** Mark a prescription as dispensed and reduce inventory */
    public function dispensePrescription(int $prescriptionId): bool {
        $st = $this->db->prepare(
            'SELECT p.*, m.quantity AS stock
             FROM `prescriptions` p
             JOIN `medicines` m ON m.medicine_id = p.medicine_id
             WHERE p.prescription_id = ? AND p.dispensed = 0 LIMIT 1');
        $st->execute([$prescriptionId]);
        $rx = $st->fetch();
        if (!$rx || $rx['stock'] < 1) return false;

        $this->dispense((int)$rx['medicine_id']);
        $this->db->prepare(
            'UPDATE `prescriptions`
             SET dispensed=1, dispensed_at=NOW()
             WHERE prescription_id=?')
        ->execute([$prescriptionId]);
        return true;
    }

    public function getLowStock(): array {
        return $this->db->query(
            'SELECT * FROM `medicines`
             WHERE quantity <= low_stock_threshold
             ORDER BY quantity ASC')->fetchAll();
    }

    public function getExpiringSoon(int $days = 30): array {
        $st = $this->db->prepare(
            'SELECT * FROM `medicines`
             WHERE expiry_date IS NOT NULL
               AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
               AND expiry_date >= CURDATE()
             ORDER BY expiry_date ASC');
        $st->execute([$days]);
        return $st->fetchAll();
    }

    public function search(string $q): array {
        $like = "%$q%";
        $st = $this->db->prepare(
            'SELECT * FROM `medicines`
             WHERE medicine_name LIKE ? OR generic_name LIKE ?
             ORDER BY medicine_name');
        $st->execute([$like, $like]);
        return $st->fetchAll();
    }

    public function totalUsage(): array {
        return $this->db->query(
            'SELECT m.medicine_name, COUNT(p.prescription_id) AS times_prescribed
             FROM `prescriptions` p
             JOIN `medicines` m ON m.medicine_id = p.medicine_id
             GROUP BY m.medicine_id, m.medicine_name
             ORDER BY times_prescribed DESC
             LIMIT 10')->fetchAll();
    }
}
