<?php
// app/controllers/AdminController.php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/NurseModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';
require_once __DIR__ . '/../models/StudentModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/MedicineModel.php';
require_once __DIR__ . '/../models/FeedbackModel.php';
require_once __DIR__ . '/../models/ConsultationModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../models/LabTestModel.php';

class AdminController {
    private UserModel        $users;
    private NurseModel       $nurses;
    private DoctorModel      $doctors;
    private StudentModel     $students;
    private AppointmentModel $appointments;
    private MedicineModel    $medicines;
    private FeedbackModel    $feedback;
    private ConsultationModel $consultations;
    private NotificationModel $notifications;
    private LabTestModel     $labTests;

    public function __construct() {
        $this->users         = new UserModel();
        $this->nurses        = new NurseModel();
        $this->doctors       = new DoctorModel();
        $this->students      = new StudentModel();
        $this->appointments  = new AppointmentModel();
        $this->medicines     = new MedicineModel();
        $this->feedback      = new FeedbackModel();
        $this->consultations = new ConsultationModel();
        $this->notifications = new NotificationModel();
        $this->labTests      = new LabTestModel();
        requireRole('admin');
        requireProfileComplete();
    }

    private function layout(string $pageTitle, string $viewFile, array $vars = []): void {
        extract($vars);
        include __DIR__ . '/../views/shared/header.php';
        include __DIR__ . '/../views/admin/' . $viewFile;
        include __DIR__ . '/../views/shared/footer.php';
    }

    /* ── DASHBOARD ─────────────────────────────────────── */
    public function dashboard(): void {
        $counts       = $this->users->countByRole();
        $apptStats    = $this->appointments->countAllByStatus();
        $doctors      = $this->doctors->getAll();
        $nurses       = $this->nurses->getAll();
        $students     = $this->students->getAll();
        $lowStock     = $this->medicines->getLowStock();
        $expiring     = $this->medicines->getExpiringSoon();
        $openComplaints = $this->feedback->countOpen();
        $monthlyAppts = $this->appointments->getMonthlyStats();
        $commonDx     = $this->consultations->commonDiagnoses();
        $topMeds      = $this->medicines->totalUsage();
        $pendingLabs  = $this->labTests->getAll('Requested');
        $unread       = $this->notifications->countUnread((int)$_SESSION['user_id']);

        $this->layout('Admin Dashboard', 'dashboard.php', compact(
            'counts','apptStats','doctors','nurses','students',
            'lowStock','expiring','openComplaints','monthlyAppts',
            'commonDx','topMeds','pendingLabs','unread'
        ));
    }

    /* ── CREATE STAFF ───────────────────────────────────── */
    public function showCreateStaff(): void {
        $error   = getFlash('error');
        $success = getFlash('success');
        $tempPwd = getFlash('temp_pwd');
        $this->layout('Create Staff Account', 'create_staff.php',
            compact('error','success','tempPwd'));
    }

    public function handleCreateStaff(): void {
        verifyCsrf();
        $role  = postStr('role');
        $empId = postStr('employee_id');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $errors = [];
        if (!in_array($role, ['doctor','nurse'], true)) $errors[] = 'Select a valid role.';
        if (!$empId)  $errors[] = 'Employee ID is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
        if ($this->users->isEmailTaken($email)) $errors[] = 'Email already registered.';
        if ($role === 'doctor' && $this->doctors->isEmployeeIdTaken($empId))
            $errors[] = 'Doctor Employee ID already in use.';
        if ($role === 'nurse' && $this->nurses->isEmployeeIdTaken($empId))
            $errors[] = 'Nurse Employee ID already in use.';
        if ($errors) {
            flash('error', implode('<br>', $errors));
            redirect(BASE_URL . '/public/admin/create-staff.php');
        }
        $tempPwd = generateTempPassword();
        $userId  = $this->users->create([
            'full_name'       => $empId,
            'email'           => $email,
            'password_hash'   => hashPwd($tempPwd),
            'user_type'       => $role,
            'must_change_pwd' => 1,
            'is_first_login'  => 1,
            'profile_complete'=> 0,
        ]);
        if ($role === 'doctor')
            $this->doctors->create(['user_id' => $userId, 'employee_id' => $empId]);
        else
            $this->nurses->create(['user_id' => $userId, 'employee_id' => $empId]);

        auditLog('CREATE_STAFF', 'user', $userId, null, ['role'=>$role,'email'=>$email]);
        flash('success', ucfirst($role).' account created successfully.');
        flash('temp_pwd', $tempPwd);
        redirect(BASE_URL . '/public/admin/create-staff.php');
    }

    /* ── ADD STUDENT ────────────────────────────────────── */
    public function showAddStudent(): void {
        $error   = getFlash('error');
        $success = getFlash('success');
        $tempPwd = getFlash('temp_pwd');
        $this->layout('Add Student Account', 'add_student.php',
            compact('error','success','tempPwd'));
    }

    public function handleAddStudent(): void {
        verifyCsrf();
        $email = strtolower(trim($_POST['email'] ?? ''));
        $errors = [];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
        if ($this->users->isEmailTaken($email)) $errors[] = 'Email already registered.';
        if ($errors) {
            flash('error', implode('<br>', $errors));
            redirect(BASE_URL . '/public/admin/add-student.php');
        }
        $tempPwd = generateTempPassword();
        $userId  = $this->users->create([
            'full_name'       => postStr('full_name') ?: $email,
            'email'           => $email,
            'password_hash'   => hashPwd($tempPwd),
            'user_type'       => 'student',
            'must_change_pwd' => 1,
            'is_first_login'  => 0,
            'profile_complete'=> 0,
        ]);
        $this->students->create([
            'user_id'        => $userId,
            'full_name'      => postStr('full_name') ?: $email,
            'email'          => $email,
            'course'         => postStr('course'),
            'year_level'     => postStr('year_level'),
            'contact_number' => postStr('contact_number'),
        ]);
        auditLog('ADD_STUDENT', 'user', $userId);
        flash('success', 'Student account created.');
        flash('temp_pwd', $tempPwd);
        redirect(BASE_URL . '/public/admin/add-student.php');
    }

    /* ── MEDICINES ──────────────────────────────────────── */
    public function listMedicines(): void {
        $meds     = $this->medicines->getAll();
        $lowStock = $this->medicines->getLowStock();
        $expiring = $this->medicines->getExpiringSoon();
        $success  = getFlash('success');
        $error    = getFlash('error');
        $this->layout('Medicine Inventory', 'medicines.php',
            compact('meds','lowStock','expiring','success','error'));
    }

    public function showAddMedicine(): void {
        $error = getFlash('error');
        $this->layout('Add Medicine', 'add_medicine.php', compact('error'));
    }

    public function handleAddMedicine(): void {
        verifyCsrf();
        $id = $this->medicines->create([
            'medicine_name'       => postStr('medicine_name'),
            'generic_name'        => postStr('generic_name'),
            'category'            => postStr('category'),
            'quantity'            => (int)($_POST['quantity'] ?? 0),
            'unit'                => postStr('unit') ?: 'tablet',
            'expiry_date'         => postStr('expiry_date') ?: null,
            'low_stock_threshold' => (int)($_POST['low_stock_threshold'] ?? 10),
        ]);
        auditLog('ADD_MEDICINE', 'medicines', $id);
        flash('success', 'Medicine added to inventory.');
        redirect(BASE_URL . '/public/admin/medicines.php');
    }

    public function handleEditMedicine(): void {
        verifyCsrf();
        $id = (int)($_POST['medicine_id'] ?? 0);
        $this->medicines->update($id, [
            'medicine_name'       => postStr('medicine_name'),
            'generic_name'        => postStr('generic_name'),
            'category'            => postStr('category'),
            'quantity'            => (int)($_POST['quantity'] ?? 0),
            'unit'                => postStr('unit') ?: 'tablet',
            'expiry_date'         => postStr('expiry_date') ?: null,
            'low_stock_threshold' => (int)($_POST['low_stock_threshold'] ?? 10),
        ]);
        auditLog('EDIT_MEDICINE', 'medicines', $id);
        flash('success', 'Medicine updated.');
        redirect(BASE_URL . '/public/admin/medicines.php');
    }

    /* ── FEEDBACK / COMPLAINTS (admin-only) ─────────────── */
    public function listFeedback(): void {
        $type     = $_GET['type']   ?? '';
        $status   = $_GET['status'] ?? '';
        $items    = $this->feedback->getAll($type ?: null, $status ?: null);
        $success  = getFlash('success');
        $this->layout('Feedback & Complaints', 'feedback.php',
            compact('items','success'));
    }

    public function handleFeedback(): void {
        verifyCsrf();
        $id     = (int)($_POST['feedback_id'] ?? 0);
        $status = postStr('status');
        $notes  = postStr('admin_notes');
        $this->feedback->updateStatus($id, $status, $notes ?: null);
        auditLog('FEEDBACK_UPDATE', 'feedback', $id);
        flash('success', 'Feedback updated.');
        redirect(BASE_URL . '/public/admin/feedback.php');
    }

    /* ── AUDIT LOG ──────────────────────────────────────── */
    public function auditLog(): void {
        $db    = getDB();
        $limit = 100;
        $logs  = $db->query(
            'SELECT al.*, u.full_name, u.email
             FROM `audit_log` al
             LEFT JOIN `user` u ON u.user_id = al.user_id
             ORDER BY al.created_at DESC
             LIMIT ' . $limit)->fetchAll();
        $this->layout('Audit Log', 'audit_log.php', compact('logs'));
    }

    /* ── LIST PAGES ─────────────────────────────────────── */
    public function listDoctors(): void {
        $doctors = $this->doctors->getAll();
        $this->layout('Doctors', 'list_doctors.php', compact('doctors'));
    }

    public function listNurses(): void {
        $nurses = $this->nurses->getAll();
        $this->layout('Nurses', 'list_nurses.php', compact('nurses'));
    }

    public function listStudents(): void {
        $q        = $_GET['q'] ?? '';
        $students = $q
            ? [$this->students->searchByUid($q)]
            : $this->students->getAll();
        $students = array_filter($students);
        $this->layout('Students', 'list_students.php', compact('students','q'));
    }

    public function listAppointments(): void {
        $status = $_GET['status'] ?? '';
        $date   = $_GET['date']   ?? '';
        $appointments = $this->appointments->getAll($status ?: null, $date ?: null);
        $stats  = $this->appointments->countAllByStatus();
        $this->layout('All Appointments', 'list_appointments.php',
            compact('appointments','stats'));
    }

    public function listLabTests(): void {
        $status = $_GET['status'] ?? '';
        $tests  = $this->labTests->getAll($status ?: null);
        $this->layout('Lab Tests', 'lab_tests.php', compact('tests'));
    }

    /* ── NOTIFICATIONS ──────────────────────────────────── */
    public function notifications(): void {
        $userId = (int)$_SESSION['user_id'];
        $notifs = $this->notifications->getByUser($userId);
        $this->notifications->markRead($userId);
        $this->layout('Notifications', 'notifications.php', compact('notifs'));
    }

    /* ── DELETE USER ────────────────────────────────────── */
    public function deleteUser(): void {
        verifyCsrf();
        $targetId = (int)($_POST['user_id'] ?? 0);
        if ($targetId && $targetId !== (int)$_SESSION['user_id']) {
            auditLog('DELETE_USER', 'user', $targetId);
            $this->users->delete($targetId);
        }
        flash('success', 'User deleted.');
        redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/public/admin/dashboard.php');
    }
}
