<?php
// app/models/AvailabilityModel.php
require_once __DIR__ . '/../../config/db.php';

class AvailabilityModel {
    private PDO $db;
    public function __construct() { $this->db = getDB(); }

    public function getByDoctor(int $doctorId): array {
        $st = $this->db->prepare(
            "SELECT * FROM `availability`
             WHERE doctor_id = ? AND is_active = 1
             ORDER BY FIELD(day_of_week,
               'Monday','Tuesday','Wednesday','Thursday',
               'Friday','Saturday','Sunday'), start_time");
        $st->execute([$doctorId]);
        return $st->fetchAll();
    }

    public function getBookedSlots(int $doctorId, string $date): array {
        $st = $this->db->prepare(
            "SELECT appointment_time FROM `appointments`
             WHERE doctor_id = ? AND appointment_date = ?
               AND status NOT IN ('Cancelled')");
        $st->execute([$doctorId, $date]);
        return array_column($st->fetchAll(), 'appointment_time');
    }

    public function saveSchedule(int $doctorId, array $slots): void {
        $this->db->prepare('DELETE FROM `availability` WHERE doctor_id = ?')
                 ->execute([$doctorId]);
        $st = $this->db->prepare(
            'INSERT INTO `availability`
               (doctor_id, day_of_week, start_time, end_time, slot_duration)
             VALUES (?,?,?,?,?)');
        foreach ($slots as $s) {
            $st->execute([
                $doctorId, $s['day_of_week'],
                $s['start_time'], $s['end_time'],
                $s['slot_duration'] ?? 10,
            ]);
        }
    }

    /** Generate time slots of given duration between start and end */
    public static function generateSlots(
        string $start, string $end, int $duration = 10
    ): array {
        $slots = [];
        $t = strtotime($start);
        $e = strtotime($end);
        while ($t < $e) {
            $slots[] = date('H:i', $t);
            $t += $duration * 60;
        }
        return $slots;
    }

    /** All slots with available/booked status */
    public function getAllSlotsWithStatus(int $doctorId, string $date): array {
        $dayName = date('l', strtotime($date));
        $st = $this->db->prepare(
            'SELECT start_time, end_time, slot_duration
             FROM `availability`
             WHERE doctor_id = ? AND day_of_week = ? AND is_active = 1');
        $st->execute([$doctorId, $dayName]);
        $windows = $st->fetchAll();
        if (empty($windows)) return [];

        $booked = array_map(
            fn($t) => substr($t, 0, 5),
            $this->getBookedSlots($doctorId, $date)
        );

        $slots = [];
        foreach ($windows as $w) {
            foreach (self::generateSlots(
                $w['start_time'], $w['end_time'], (int)$w['slot_duration']
            ) as $slot) {
                $slots[] = [
                    'time'      => $slot,
                    'available' => !in_array($slot, $booked, true),
                ];
            }
        }
        return $slots;
    }

    /** Only available (unbooked) slot times */
    public function getAvailableSlots(int $doctorId, string $date): array {
        return array_values(array_map(
            fn($s) => $s['time'],
            array_filter(
                $this->getAllSlotsWithStatus($doctorId, $date),
                fn($s) => $s['available']
            )
        ));
    }
}
