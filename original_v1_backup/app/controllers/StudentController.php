<?php
// app/controllers/StudentController.php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../models/StudentModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/AvailabilityModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/ConsultationModel.php';
require_once __DIR__ . '/../models/LabTestModel.php';
require_once __DIR__ . '/../models/FollowUpModel.php';
require_once __DIR__ . '/../models/FeedbackModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class StudentController {
    private StudentModel      $students;
    private DoctorModel       $doctors;
    private AvailabilityModel $avail;
    private AppointmentModel  $appointments;
    private ConsultationModel $consultations;
    private LabTestModel      $labTests;
    private FollowUpModel     $followUps;
    private FeedbackModel     $feedback;
    private NotificationModel $notifications;

    public function __construct() {
        $this->students      = new StudentModel();
        $this->doctors       = new DoctorModel();
        $this->avail         = new AvailabilityModel();
        $this->appointments  = new AppointmentModel();
        $this->consultations = new ConsultationModel();
        $this->labTests      = new LabTestModel();
        $this->followUps     = new FollowUpModel();
        $this->feedback      = new FeedbackModel();
        $this->notifications = new NotificationModel();
        requireRole('student');
        requireProfileComplete();
    }

    private function layout(string $pageTitle, string $viewFile, array $vars = []): void {
        extract($vars);
        include __DIR__ . '/../views/shared/header.php';
        include __DIR__ . '/../views/student/' . $viewFile;
        include __DIR__ . '/../views/shared/footer.php';
    }

    private function getStudent(): array {
        return $this->students->findByUserId((int)$_SESSION['user_id']);
    }

    /* ── DASHBOARD ─────────────────────────────────────── */
    public function dashboard(): void {
        $userId    = (int)$_SESSION['user_id'];
        $student   = $this->getStudent();
        $stats     = $this->appointments->countByStudent($student['student_id']);
        $recent    = $this->appointments->getByStudent($student['student_id']);
        $labTests  = $this->labTests->getByStudent($student['student_id']);
        $followUps = $this->followUps->getUpcoming($student['student_id']);
        $unread    = $this->notifications->countUnread($userId);
        $this->layout('My Dashboard', 'dashboard.php',
            compact('student','stats','recent','labTests','followUps','unread'));
    }

    /* ── FIND DOCTORS & BOOK ────────────────────────────── */
    public function listDoctors(): void {
        $doctors = $this->doctors->getAvailableDoctors();
        $this->layout('Find a Doctor', 'doctors.php', compact('doctors'));
    }

    public function showBook(): void {
        $doctorId = (int)($_GET['doctor_id'] ?? 0);
        if (!$doctorId) redirect(BASE_URL . '/public/student/doctors.php');
        $doctor   = $this->doctors->findById($doctorId);
        if (!$doctor) redirect(BASE_URL . '/public/student/doctors.php');
        $date     = $_GET['date'] ?? date('Y-m-d');
        $slots    = $this->avail->getAllSlotsWithStatus($doctorId, $date);
        $schedule = $this->avail->getByDoctor($doctorId);
        $error    = getFlash('error');

        // Pre-fill follow-up info if coming from follow-up
        $followupId = (int)($_GET['followup_id'] ?? 0);
        $this->layout('Book Appointment', 'book.php',
            compact('doctor','date','slots','schedule','error','followupId'));
    }

    public function handleBook(): void {
        verifyCsrf();
        $userId    = (int)$_SESSION['user_id'];
        $student   = $this->getStudent();
        $doctorId  = (int)($_POST['doctor_id'] ?? 0);
        $date      = postStr('appointment_date');
        $time      = postStr('appointment_time');
        $reason    = postStr('reason');
        $priority  = postStr('priority') ?: 'Normal';
        $followupId= (int)($_POST['followup_id'] ?? 0);
        $errors    = [];
        if (!$doctorId) $errors[] = 'Please select a doctor.';
        if (!$date)     $errors[] = 'Please select a date.';
        if (!$time)     $errors[] = 'Please select a time slot.';
        if ($date && strtotime($date) < strtotime('today'))
            $errors[] = 'Date cannot be in the past.';
        if ($doctorId && $date && $time &&
            $this->appointments->slotTaken($doctorId, $date, $time))
            $errors[] = 'That slot was just taken. Please choose another.';
        if ($errors) {
            flash('error', implode('<br>', $errors));
            redirect(BASE_URL . "/public/student/book.php?doctor_id=$doctorId&date=$date");
        }
        $apptId = $this->appointments->book([
            'student_id'       => $student['student_id'],
            'doctor_id'        => $doctorId,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'reason'           => $reason,
            'priority'         => $priority,
            'is_followup'      => $followupId ? 1 : 0,
        ]);

        // Link follow-up if applicable
        if ($followupId)
            $this->followUps->linkAppointment($followupId, $apptId);

        // Notify doctor
        $doctor = $this->doctors->findById($doctorId);
        if ($doctor) {
            $this->notifications->create(
                (int)$doctor['user_id'], 'appointment',
                '📅 New Appointment Booked',
                "{$student['full_name']} has booked an appointment on {$date} at {$time}. Priority: {$priority}.",
                $apptId
            );
        }

        // Confirm to student
        $this->notifications->create(
            $userId, 'appointment',
            '✅ Appointment Confirmed',
            "Your appointment with {$doctor['full_name']} on {$date} at {$time} is booked. Queue number will be assigned.",
            $apptId
        );

        auditLog('BOOK_APPOINTMENT', 'appointments', $apptId);
        flash('success', "Appointment booked! Queue #{$apptId} assigned.");
        redirect(BASE_URL . '/public/student/appointments.php');
    }

    public function myAppointments(): void {
        $student  = $this->getStudent();
        $filter   = $_GET['status'] ?? '';
        $appointments = $this->appointments->getByStudent($student['student_id'], $filter ?: null);
        $success  = getFlash('success');
        $error    = getFlash('error');
        $this->layout('My Appointments', 'appointments.php',
            compact('appointments','success','error'));
    }

    public function cancelAppointment(): void {
        verifyCsrf();
        $student = $this->getStudent();
        $apptId  = (int)($_POST['appointment_id'] ?? 0);
        $ok = $this->appointments->cancelByStudent($apptId, $student['student_id']);
        flash($ok ? 'success' : 'error',
              $ok ? 'Appointment cancelled.' : 'Cannot cancel this appointment.');
        redirect(BASE_URL . '/public/student/appointments.php');
    }

    /* ── MEDICAL RECORDS ────────────────────────────────── */
    public function myRecords(): void {
        $student   = $this->getStudent();
        $records   = $this->consultations->getByStudent($student['student_id']);
        $allergies = $this->students->getAllergies($student['student_id']);
        $this->layout('My Medical Records', 'records.php',
            compact('student','records','allergies'));
    }

    /* ── LAB TESTS ──────────────────────────────────────── */
    public function myLabTests(): void {
        $student  = $this->getStudent();
        $tests    = $this->labTests->getByStudent($student['student_id']);
        $success  = getFlash('success');
        $error    = getFlash('error');
        $this->layout('My Lab Tests', 'lab_tests.php', compact('tests','success','error'));
    }

    public function markLabDone(): void {
        verifyCsrf();
        $student = $this->getStudent();
        $id      = (int)($_POST['lab_test_id'] ?? 0);
        $ok = $this->labTests->markPatientDone($id, $student['student_id']);
        if ($ok) {
            $test = $this->labTests->findById($id);
            // Notify doctor
            if ($test) {
                $this->notifications->create(
                    (int)$test['doctor_id'], 'lab_test',
                    '🔬 Lab Test Completed',
                    "{$student['full_name']} has completed: {$test['test_name']}. Please update the result when available.",
                    $id
                );
            }
            flash('success', 'Marked as done. Your doctor will update the result.');
        } else {
            flash('error', 'Could not mark test as done.');
        }
        redirect(BASE_URL . '/public/student/lab-tests.php');
    }

    /* ── FOLLOW-UPS ─────────────────────────────────────── */
    public function myFollowUps(): void {
        $student   = $this->getStudent();
        $followUps = $this->followUps->getByStudent($student['student_id']);
        $this->layout('My Follow-Ups', 'followups.php', compact('followUps'));
    }

    /* ── FEEDBACK & COMPLAINTS ──────────────────────────── */
    public function showFeedback(): void {
        $student  = $this->getStudent();
        $items    = $this->feedback->getByStudent($student['student_id']);
        $success  = getFlash('success');
        $error    = getFlash('error');
        $this->layout('Feedback & Complaints', 'feedback.php',
            compact('items','success','error'));
    }

    public function handleFeedback(): void {
        verifyCsrf();
        $student = $this->getStudent();
        $type    = postStr('type');
        $subject = postStr('subject');
        $message = postStr('message');
        if (!$subject || !$message) {
            flash('error', 'Subject and message are required.');
            redirect(BASE_URL . '/public/student/feedback.php');
        }
        $id = $this->feedback->create([
            'student_id' => $student['student_id'],
            'type'       => $type,
            'subject'    => $subject,
            'message'    => $message,
        ]);
        // Notify admin
        $this->notifications->notifyAdmins(
            'system',
            "New {$type} Submitted",
            "{$student['full_name']} submitted a {$type}: {$subject}"
        );
        flash('success', 'Your ' . strtolower($type) . ' has been submitted. Only admin can view it.');
        redirect(BASE_URL . '/public/student/feedback.php');
    }

    /* ── PROFILE ────────────────────────────────────────── */
    public function showProfile(): void {
        $student  = $this->getStudent();
        $allergies = $this->students->getAllergies($student['student_id']);
        $allergyOptions = $this->students->getAllAllergyOptions();
        $success = getFlash('success');
        $error   = getFlash('error');
        $this->layout('My Profile', 'profile.php',
            compact('student','allergies','allergyOptions','success','error'));
    }

    public function handleProfile(): void {
        verifyCsrf();
        $userId  = (int)$_SESSION['user_id'];
        $student = $this->getStudent();
        $this->students->updateProfile($userId, [
            'full_name'               => postStr('full_name'),
            'course'                  => postStr('course'),
            'year_level'              => postStr('year_level'),
            'contact_number'          => postStr('contact_number'),
            'blood_group'             => postStr('blood_group'),
            'emergency_contact_name'  => postStr('emergency_contact_name'),
            'emergency_contact_phone' => postStr('emergency_contact_phone'),
            'medical_history'         => postStr('medical_history'),
        ]);
        (new UserModel())->updateProfile($userId, [
            'full_name'     => postStr('full_name'),
            'gender'        => postStr('gender'),
            'phone'         => postStr('contact_number'),
            'address'       => postStr('address'),
            'date_of_birth' => postStr('date_of_birth') ?: null,
        ]);

        // Save allergies
        $allergyIds = $_POST['allergy_ids']    ?? [];
        $levels     = $_POST['allergy_levels'] ?? [];
        $this->students->saveAllergies($student['student_id'], $allergyIds, $levels);

        $_SESSION['full_name'] = postStr('full_name');
        auditLog('UPDATE_PROFILE', 'students', $student['student_id']);
        flash('success', 'Profile updated successfully.');
        redirect(BASE_URL . '/public/student/profile.php');
    }

    /* ── NOTIFICATIONS ──────────────────────────────────── */
    public function notifications(): void {
        $userId = (int)$_SESSION['user_id'];
        $notifs = $this->notifications->getByUser($userId);
        $this->notifications->markRead($userId);
        $this->layout('Notifications', 'notifications.php', compact('notifs'));
    }

    /* ── API: available slots (JSON) ────────────────────── */
    public function getSlots(): void {
        header('Content-Type: application/json');
        $doctorId = (int)($_GET['doctor_id'] ?? 0);
        $date     = $_GET['date'] ?? '';
        if (!$doctorId || !$date) { echo json_encode([]); exit; }
        echo json_encode($this->avail->getAllSlotsWithStatus($doctorId, $date));
        exit;
    }
}
