<?php
// app/controllers/DoctorController.php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/AvailabilityModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/ConsultationModel.php';
require_once __DIR__ . '/../models/LabTestModel.php';
require_once __DIR__ . '/../models/MedicineModel.php';
require_once __DIR__ . '/../models/FollowUpModel.php';
require_once __DIR__ . '/../models/StudentModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class DoctorController {
    private DoctorModel       $doctors;
    private AvailabilityModel $avail;
    private AppointmentModel  $appointments;
    private ConsultationModel $consultations;
    private LabTestModel      $labTests;
    private MedicineModel     $medicines;
    private FollowUpModel     $followUps;
    private StudentModel      $students;
    private NotificationModel $notifications;

    public function __construct() {
        $this->doctors       = new DoctorModel();
        $this->avail         = new AvailabilityModel();
        $this->appointments  = new AppointmentModel();
        $this->consultations = new ConsultationModel();
        $this->labTests      = new LabTestModel();
        $this->medicines     = new MedicineModel();
        $this->followUps     = new FollowUpModel();
        $this->students      = new StudentModel();
        $this->notifications = new NotificationModel();
        requireRole('doctor');
        requireProfileComplete();
    }

    private function layout(string $pageTitle, string $viewFile, array $vars = []): void {
        extract($vars);
        include __DIR__ . '/../views/shared/header.php';
        include __DIR__ . '/../views/doctor/' . $viewFile;
        include __DIR__ . '/../views/shared/footer.php';
    }

    private function getDoctor(): array {
        return $this->doctors->findByUserId((int)$_SESSION['user_id']);
    }

    /* ── DASHBOARD ─────────────────────────────────────── */
    public function dashboard(): void {
        $userId   = (int)$_SESSION['user_id'];
        $doctor   = $this->getDoctor();
        $stats    = $this->doctors->countStats($userId);
        $queue    = $this->appointments->getTodayQueue($doctor['doctor_id']);
        $upcoming = $this->doctors->getAppointments($userId, 'Confirmed');
        $pending  = $this->doctors->getAppointments($userId, 'Pending');
        $unread   = $this->notifications->countUnread($userId);
        $this->layout('My Dashboard', 'dashboard.php',
            compact('doctor','stats','queue','upcoming','pending','unread'));
    }

    /* ── APPOINTMENTS ───────────────────────────────────── */
    public function appointments(): void {
        $userId  = (int)$_SESSION['user_id'];
        $filter  = $_GET['status'] ?? '';
        $appointments = $this->doctors->getAppointments($userId, $filter ?: null);
        $stats   = $this->doctors->countStats($userId);
        $success = getFlash('success');
        $this->layout('Appointments', 'appointments.php',
            compact('appointments','stats','success'));
    }

    public function updateAppointment(): void {
        verifyCsrf();
        $userId = (int)$_SESSION['user_id'];
        $apptId = (int)($_POST['appointment_id'] ?? 0);
        $status = postStr('status');
        $notes  = postStr('notes');
        $allowed = ['Confirmed','Completed','Cancelled'];
        if (!in_array($status, $allowed, true)) {
            flash('error', 'Invalid status.');
            redirect(BASE_URL . '/public/doctor/appointments.php');
        }
        $appt = $this->appointments->findById($apptId);
        $me   = $this->getDoctor();
        if (!$appt || (int)$appt['doctor_id'] !== (int)$me['doctor_id']) {
            flash('error', 'Appointment not found.');
            redirect(BASE_URL . '/public/doctor/appointments.php');
        }
        $this->appointments->updateStatus($apptId, $status, $notes ?: null);

        // Notify patient
        $student = $this->students->findById((int)$appt['student_id']);
        if ($student) {
            $this->notifications->create(
                (int)$student['user_id'], 'appointment',
                'Appointment ' . $status,
                "Your appointment on {$appt['appointment_date']} at {$appt['appointment_time']} with {$me['full_name']} has been {$status}.",
                $apptId
            );
        }
        auditLog('UPDATE_APPOINTMENT', 'appointments', $apptId, null, ['status'=>$status]);
        flash('success', 'Appointment updated.');
        redirect(BASE_URL . '/public/doctor/appointments.php');
    }

    /* ── CONSULTATION / MEDICAL RECORD ─────────────────── */
    public function showConsult(): void {
        $apptId  = (int)($_GET['appointment_id'] ?? 0);
        if (!$apptId) redirect(BASE_URL . '/public/doctor/appointments.php');
        $appt    = $this->appointments->findById($apptId);
        $doctor  = $this->getDoctor();
        if (!$appt || (int)$appt['doctor_id'] !== (int)$doctor['doctor_id'])
            redirect(BASE_URL . '/public/doctor/appointments.php');
        $student   = $this->students->findById((int)$appt['student_id']);
        $allergies = $this->students->getAllergies((int)$student['student_id']);
        $history   = $this->consultations->getByStudent((int)$student['student_id']);
        $existing  = $this->consultations->findByAppointment($apptId);
        $medicines = $this->medicines->getAll();
        $error     = getFlash('error');
        $success   = getFlash('success');
        $this->layout('Consultation', 'consult.php',
            compact('appt','doctor','student','allergies','history','existing','medicines','error','success'));
    }

    public function handleConsult(): void {
        verifyCsrf();
        $userId = (int)$_SESSION['user_id'];
        $doctor = $this->getDoctor();
        $apptId = (int)($_POST['appointment_id'] ?? 0);
        $appt   = $this->appointments->findById($apptId);
        if (!$appt || (int)$appt['doctor_id'] !== (int)$doctor['doctor_id']) {
            flash('error', 'Invalid appointment.');
            redirect(BASE_URL . '/public/doctor/appointments.php');
        }

        // Create or update consultation
        $existing = $this->consultations->findByAppointment($apptId);
        if ($existing) {
            $consultId = (int)$existing['consultation_id'];
        } else {
            $consultId = $this->consultations->create([
                'appointment_id'   => $apptId,
                'doctor_id'        => $doctor['doctor_id'],
                'student_id'       => $appt['student_id'],
                'consultation_date'=> date('Y-m-d'),
                'diagnosis'        => postStr('diagnosis'),
                'notes'            => postStr('consultation_notes'),
                'follow_up_date'   => postStr('follow_up_date') ?: null,
                'follow_up_notes'  => postStr('follow_up_notes'),
            ]);
        }

        // Prescriptions
        $medIds   = $_POST['medicine_id']     ?? [];
        $amounts  = $_POST['dosage_amount']   ?? [];
        $units    = $_POST['dosage_unit']     ?? [];
        $freqs    = $_POST['frequency']       ?? [];
        $durs     = $_POST['duration']        ?? [];
        $instrs   = $_POST['instruction']     ?? [];
        foreach ($medIds as $i => $mid) {
            if (!$mid) continue;
            $this->consultations->addPrescription($consultId, [
                'medicine_id'  => (int)$mid,
                'dosage_amount'=> $amounts[$i] ?? null,
                'dosage_unit'  => $units[$i]   ?? null,
                'frequency'    => $freqs[$i]   ?? null,
                'duration'     => $durs[$i]    ?? null,
                'instruction'  => $instrs[$i]  ?? null,
            ]);
        }

        // Lab tests
        $testNames = array_filter($_POST['test_name'] ?? []);
        foreach ($testNames as $testName) {
            $ltId = $this->labTests->create([
                'consultation_id' => $consultId,
                'student_id'      => (int)$appt['student_id'],
                'doctor_id'       => $doctor['doctor_id'],
                'test_name'       => clean($testName),
                'request_date'    => date('Y-m-d'),
            ]);
            // Notify patient
            $student = $this->students->findById((int)$appt['student_id']);
            if ($student) {
                $this->notifications->create(
                    (int)$student['user_id'], 'lab_test',
                    '🔬 Lab Test Requested',
                    "Dr. {$doctor['full_name']} has requested: {$testName}. Please get the test done.",
                    $ltId
                );
            }
        }

        // Follow-up
        $fuDate = postStr('follow_up_date');
        if ($fuDate) {
            $fuId = $this->followUps->create([
                'consultation_id' => $consultId,
                'student_id'      => (int)$appt['student_id'],
                'doctor_id'       => $doctor['doctor_id'],
                'followup_date'   => $fuDate,
                'notes'           => postStr('follow_up_notes'),
            ]);
            $student = $this->students->findById((int)$appt['student_id']);
            if ($student) {
                $this->notifications->create(
                    (int)$student['user_id'], 'followup',
                    '📅 Follow-up Scheduled',
                    "Dr. {$doctor['full_name']} has scheduled a follow-up for {$fuDate}. Please book an appointment.",
                    $fuId
                );
            }
        }

        $this->appointments->updateStatus($apptId, 'Completed', postStr('consultation_notes'));
        auditLog('CONSULTATION', 'consultations', $consultId);
        flash('success', 'Consultation saved successfully.');
        redirect(BASE_URL . '/public/doctor/appointments.php');
    }

    /* ── PATIENT RECORD ─────────────────────────────────── */
    public function viewPatient(): void {
        $studentId = (int)($_GET['student_id'] ?? 0);
        if (!$studentId) redirect(BASE_URL . '/public/doctor/appointments.php');
        $student   = $this->students->findById($studentId);
        $allergies = $this->students->getAllergies($studentId);
        $records   = $this->consultations->getFullRecord($studentId);
        $labTests  = $this->labTests->getByStudent($studentId);
        $followUps = $this->followUps->getByStudent($studentId);
        $this->layout('Patient Record', 'patient_record.php',
            compact('student','allergies','records','labTests','followUps'));
    }

    /* ── LAB TESTS ──────────────────────────────────────── */
    public function labTests(): void {
        $doctor = $this->getDoctor();
        $tests  = $this->labTests->getByDoctor($doctor['doctor_id']);
        $success = getFlash('success');
        $this->layout('Lab Tests', 'lab_tests.php', compact('tests','success'));
    }

    public function updateLabResult(): void {
        verifyCsrf();
        $id     = (int)($_POST['lab_test_id'] ?? 0);
        $result = postStr('result');
        $this->labTests->updateResult($id, $result);

        // Notify patient
        $test = $this->labTests->findById($id);
        if ($test) {
            $student = $this->students->findById((int)$test['student_id']);
            if ($student) {
                $this->notifications->create(
                    (int)$student['user_id'], 'lab_test',
                    '✅ Lab Test Result Ready',
                    "Your {$test['test_name']} result is now available. Please check your records.",
                    $id
                );
            }
        }
        auditLog('LAB_RESULT_UPDATED', 'lab_tests', $id);
        flash('success', 'Lab result updated and patient notified.');
        redirect(BASE_URL . '/public/doctor/lab-tests.php');
    }

    /* ── SCHEDULE ───────────────────────────────────────── */
    public function showSchedule(): void {
        $doctor   = $this->getDoctor();
        $schedule = $this->avail->getByDoctor($doctor['doctor_id']);
        $success  = getFlash('success');
        $error    = getFlash('error');
        $this->layout('My Schedule', 'schedule.php',
            compact('doctor','schedule','success','error'));
    }

    public function saveSchedule(): void {
        verifyCsrf();
        $doctor = $this->getDoctor();
        $days   = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        $slots  = [];
        foreach ($days as $day) {
            $key = strtolower($day);
            if (!empty($_POST['day_' . $key])) {
                $start = $_POST['start_' . $key] ?? '08:00';
                $end   = $_POST['end_'   . $key] ?? '17:00';
                if ($start < $end)
                    $slots[] = [
                        'day_of_week'   => $day,
                        'start_time'    => $start,
                        'end_time'      => $end,
                        'slot_duration' => 10,  // 10-minute slots
                    ];
            }
        }
        $this->avail->saveSchedule($doctor['doctor_id'], $slots);
        auditLog('UPDATE_SCHEDULE', 'availability', $doctor['doctor_id']);
        flash('success', 'Schedule saved. Slots are 10 minutes each.');
        redirect(BASE_URL . '/public/doctor/schedule.php');
    }

    /* ── PROFILE ────────────────────────────────────────── */
    public function showProfile(): void {
        $doctor  = $this->getDoctor();
        $success = getFlash('success');
        $error   = getFlash('error');
        $this->layout('My Profile', 'profile.php', compact('doctor','success','error'));
    }

    public function handleProfile(): void {
        verifyCsrf();
        $userId = (int)$_SESSION['user_id'];
        $d = [
            'full_name'        => postStr('full_name'),
            'specialization'   => postStr('specialization'),
            'department'       => postStr('department'),
            'consultation_fee' => (float)($_POST['consultation_fee'] ?? 0),
            'contact_number'   => postStr('contact_number'),
            'gender'           => postStr('gender'),
            'bio'              => postStr('bio'),
            'address'          => postStr('address'),
        ];
        $this->doctors->completeProfile($userId, $d);
        (new UserModel())->updateProfile($userId, [
            'full_name'     => $d['full_name'],
            'gender'        => $d['gender'],
            'phone'         => $d['contact_number'],
            'department'    => $d['department'],
            'address'       => $d['address'],
            'date_of_birth' => postStr('date_of_birth') ?: null,
        ]);
        $_SESSION['full_name'] = $d['full_name'];
        flash('success', 'Profile updated.');
        redirect(BASE_URL . '/public/doctor/profile.php');
    }

    public function toggleStatus(): void {
        verifyCsrf();
        $userId  = (int)$_SESSION['user_id'];
        $status  = postStr('availability_status');
        if (in_array($status, ['Available','Unavailable','On Leave'], true))
            $this->doctors->updateAvailabilityStatus($userId, $status);
        redirect(BASE_URL . '/public/doctor/dashboard.php');
    }

    /* ── NOTIFICATIONS ──────────────────────────────────── */
    public function notifications(): void {
        $userId = (int)$_SESSION['user_id'];
        $notifs = $this->notifications->getByUser($userId);
        $this->notifications->markRead($userId);
        $this->layout('Notifications', 'notifications.php', compact('notifs'));
    }
}
