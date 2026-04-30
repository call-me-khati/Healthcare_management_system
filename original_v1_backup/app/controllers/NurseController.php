<?php
// app/controllers/NurseController.php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../models/NurseModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/StudentModel.php';
require_once __DIR__ . '/../models/ConsultationModel.php';
require_once __DIR__ . '/../models/LabTestModel.php';
require_once __DIR__ . '/../models/MedicineModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';

class NurseController {
    private NurseModel        $nurses;
    private AppointmentModel  $appointments;
    private StudentModel      $students;
    private ConsultationModel $consultations;
    private LabTestModel      $labTests;
    private MedicineModel     $medicines;
    private NotificationModel $notifications;

    public function __construct() {
        $this->nurses        = new NurseModel();
        $this->appointments  = new AppointmentModel();
        $this->students      = new StudentModel();
        $this->consultations = new ConsultationModel();
        $this->labTests      = new LabTestModel();
        $this->medicines     = new MedicineModel();
        $this->notifications = new NotificationModel();
        requireRole('nurse');
        requireProfileComplete();
    }

    private function layout(string $pageTitle, string $viewFile, array $vars = []): void {
        extract($vars);
        include __DIR__ . '/../views/shared/header.php';
        include __DIR__ . '/../views/nurse/' . $viewFile;
        include __DIR__ . '/../views/shared/footer.php';
    }

    /* ── DASHBOARD ─────────────────────────────────────── */
    public function dashboard(): void {
        $userId = (int)$_SESSION['user_id'];
        $nurse  = $this->nurses->findByUserId($userId);
        $stats  = $this->appointments->countAllByStatus();
        $today  = $this->appointments->getAll(null, date('Y-m-d'));
        $lowStock = $this->medicines->getLowStock();
        $expiring = $this->medicines->getExpiringSoon();
        $unread   = $this->notifications->countUnread($userId);
        $this->layout('Ward Dashboard', 'dashboard.php',
            compact('nurse','stats','today','lowStock','expiring','unread'));
    }

    /* ── APPOINTMENTS ───────────────────────────────────── */
    public function appointments(): void {
        $status = $_GET['status'] ?? '';
        $date   = $_GET['date']   ?? '';
        $appointments = $this->appointments->getAll($status ?: null, $date ?: null);
        $stats  = $this->appointments->countAllByStatus();
        $success = getFlash('success');
        $this->layout('All Appointments', 'appointments.php',
            compact('appointments','stats','success'));
    }

    public function updateAppointment(): void {
        verifyCsrf();
        $apptId = (int)($_POST['appointment_id'] ?? 0);
        $status = postStr('status');
        $notes  = postStr('notes');
        $this->appointments->updateStatus($apptId, $status, $notes ?: null);
        auditLog('NURSE_UPDATE_APPT', 'appointments', $apptId);
        flash('success', 'Record updated.');
        redirect(BASE_URL . '/public/nurse/appointments.php');
    }

    /* ── PATIENT RECORD (read-only access) ──────────────── */
    public function viewPatient(): void {
        $studentId = (int)($_GET['student_id'] ?? 0);
        if (!$studentId) redirect(BASE_URL . '/public/nurse/appointments.php');
        $student   = $this->students->findById($studentId);
        $allergies = $this->students->getAllergies($studentId);
        $records   = $this->consultations->getFullRecord($studentId);
        $labTests  = $this->labTests->getByStudent($studentId);
        $this->layout('Patient Record', 'patient_record.php',
            compact('student','allergies','records','labTests'));
    }

    /* ── LAB TESTS ──────────────────────────────────────── */
    public function labTests(): void {
        $status  = $_GET['status'] ?? '';
        $tests   = $this->labTests->getAll($status ?: null);
        $success = getFlash('success');
        $this->layout('Lab Tests', 'lab_tests.php', compact('tests','success'));
    }

    public function updateLabStatus(): void {
        verifyCsrf();
        $id     = (int)($_POST['lab_test_id'] ?? 0);
        $status = postStr('status');
        $this->labTests->updateStatus($id, $status);
        auditLog('NURSE_UPDATE_LAB', 'lab_tests', $id);
        flash('success', 'Lab test status updated.');
        redirect(BASE_URL . '/public/nurse/lab-tests.php');
    }

    /* ── MEDICINES (dispense) ───────────────────────────── */
    public function medicines(): void {
        $meds    = $this->medicines->getAll();
        $success = getFlash('success');
        $error   = getFlash('error');
        $this->layout('Medicine Inventory', 'medicines.php',
            compact('meds','success','error'));
    }

    public function dispenseMedicine(): void {
        verifyCsrf();
        $rxId = (int)($_POST['prescription_id'] ?? 0);
        $ok   = $this->medicines->dispensePrescription($rxId);
        if ($ok) {
            auditLog('DISPENSE_MEDICINE', 'prescriptions', $rxId);
            flash('success', 'Medicine dispensed and stock updated.');
        } else {
            flash('error', 'Unable to dispense – insufficient stock or already dispensed.');
        }
        redirect(BASE_URL . '/public/nurse/medicines.php');
    }

    /* ── PROFILE ────────────────────────────────────────── */
    public function showProfile(): void {
        $userId = (int)$_SESSION['user_id'];
        $nurse  = $this->nurses->findByUserId($userId);
        $success = getFlash('success');
        $this->layout('My Profile', 'profile.php', compact('nurse','success'));
    }

    public function handleProfile(): void {
        verifyCsrf();
        $userId = (int)$_SESSION['user_id'];
        $d = [
            'full_name'      => postStr('full_name'),
            'department'     => postStr('department'),
            'contact_number' => postStr('contact_number'),
            'gender'         => postStr('gender'),
            'address'        => postStr('address'),
        ];
        $this->nurses->completeProfile($userId, $d);
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
        redirect(BASE_URL . '/public/nurse/profile.php');
    }

    /* ── NOTIFICATIONS ──────────────────────────────────── */
    public function notifications(): void {
        $userId = (int)$_SESSION['user_id'];
        $notifs = $this->notifications->getByUser($userId);
        $this->notifications->markRead($userId);
        $this->layout('Notifications', 'notifications.php', compact('notifs'));
    }
}
