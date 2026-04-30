<?php
// app/controllers/AuthController.php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/StudentModel.php';
require_once __DIR__ . '/../models/NurseModel.php';
require_once __DIR__ . '/../models/DoctorModel.php';

class AuthController {
    private UserModel    $users;
    private StudentModel $students;
    private NurseModel   $nurses;
    private DoctorModel  $doctors;

    public function __construct() {
        $this->users    = new UserModel();
        $this->students = new StudentModel();
        $this->nurses   = new NurseModel();
        $this->doctors  = new DoctorModel();
    }

    public function showLogin(): void {
        startAppSession();
        if (!empty($_SESSION['logged_in'])) redirect($this->dashUrl($_SESSION['role']));
        $error = getFlash('error');
        include __DIR__ . '/../views/auth/login.php';
    }

    public function handleLogin(): void {
        verifyCsrf();
        $email = strtolower(trim($_POST['email'] ?? ''));
        $pwd   = $_POST['password'] ?? '';
        $user  = $this->users->findByEmail($email);
        if (!$user || !verifyPwd($pwd, $user['password_hash'])) {
            auditLog('LOGIN_FAIL', 'user', null, ['email' => $email]);
            flash('error', 'Invalid email or password.');
            redirect(BASE_URL . '/public/login.php');
        }
        loginUser($user);
        auditLog('LOGIN', 'user', (int)$user['user_id']);
        if (!empty($user['is_first_login']))
            redirect(BASE_URL . '/public/complete-profile.php');
        redirect($this->dashUrl($user['user_type']));
    }

    public function showRegister(): void {
        $error   = getFlash('error');
        $success = getFlash('success');
        include __DIR__ . '/../views/auth/register.php';
    }

    public function handleRegister(): void {
        verifyCsrf();
        $fullName  = postStr('full_name');
        $email     = strtolower(trim($_POST['email'] ?? ''));
        $password  = $_POST['password']         ?? '';
        $confirm   = $_POST['confirm_password'] ?? '';
        $course    = postStr('course');
        $yearLevel = postStr('year_level');
        $contact   = postStr('contact_number');
        $errors = [];
        if (!$fullName) $errors[] = 'Full name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
        if ($password !== $confirm) $errors[] = 'Passwords do not match.';
        if (!isStrongPassword($password))
            $errors[] = 'Password needs ≥8 chars, uppercase, lowercase and digit.';
        if ($this->users->isEmailTaken($email))
            $errors[] = 'Email already registered.';
        if ($errors) {
            flash('error', implode('<br>', $errors));
            redirect(BASE_URL . '/public/register.php');
        }
        $userId = $this->users->create([
            'full_name'       => $fullName,
            'email'           => $email,
            'password_hash'   => hashPwd($password),
            'user_type'       => 'student',
            'must_change_pwd' => 0,
            'is_first_login'  => 0,
            'profile_complete'=> 0,
        ]);
        $this->students->create([
            'user_id'        => $userId,
            'full_name'      => $fullName,
            'email'          => $email,
            'course'         => $course,
            'year_level'     => $yearLevel,
            'contact_number' => $contact,
        ]);
        auditLog('REGISTER', 'user', $userId);
        flash('success', 'Account created! Please sign in.');
        redirect(BASE_URL . '/public/login.php');
    }

    public function showCompleteProfile(): void {
        requireLogin();
        if (empty($_SESSION['is_first_login']))
            redirect($this->dashUrl($_SESSION['role']));
        $error = getFlash('error');
        $role  = $_SESSION['role'];
        include __DIR__ . '/../views/auth/complete_profile.php';
    }

    public function handleCompleteProfile(): void {
        requireLogin();
        verifyCsrf();
        $userId   = (int)$_SESSION['user_id'];
        $role     = $_SESSION['role'];
        $fullName = postStr('full_name');
        $gender   = postStr('gender');
        $phone    = postStr('contact_number');
        $dept     = postStr('department');
        $dob      = postStr('date_of_birth');
        $address  = postStr('address');
        $newPwd   = $_POST['new_password']     ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        $errors   = [];
        if (!$fullName) $errors[] = 'Full name is required.';
        if (!isStrongPassword($newPwd))
            $errors[] = 'Password needs ≥8 chars, uppercase, lowercase and digit.';
        if ($newPwd !== $confirm) $errors[] = 'Passwords do not match.';
        if ($errors) {
            flash('error', implode('<br>', $errors));
            redirect(BASE_URL . '/public/complete-profile.php');
        }
        $this->users->changePassword($userId, hashPwd($newPwd));
        $this->users->updateProfile($userId, [
            'full_name'   => $fullName, 'gender' => $gender,
            'phone'       => $phone,    'department' => $dept,
            'address'     => $address,  'date_of_birth' => $dob ?: null,
        ]);
        if ($role === 'doctor') {
            $this->doctors->completeProfile($userId, [
                'full_name'        => $fullName,
                'specialization'   => postStr('specialization'),
                'department'       => $dept,
                'consultation_fee' => (float)($_POST['consultation_fee'] ?? 0),
                'contact_number'   => $phone,
                'gender'           => $gender,
                'bio'              => postStr('bio'),
                'address'          => $address,
            ]);
        } elseif ($role === 'nurse') {
            $this->nurses->completeProfile($userId, [
                'full_name'      => $fullName, 'department' => $dept,
                'contact_number' => $phone,    'gender'     => $gender,
                'address'        => $address,
            ]);
        }
        $_SESSION['full_name']      = $fullName;
        $_SESSION['is_first_login'] = false;
        auditLog('PROFILE_COMPLETE', 'user', $userId);
        flash('success', 'Profile completed. Welcome to MediBase!');
        redirect($this->dashUrl($role));
    }

    public function showChangePassword(): void {
        requireLogin();
        requireProfileComplete();
        $pageTitle = 'Change Password';
        include __DIR__ . '/../views/shared/header.php';
        include __DIR__ . '/../views/auth/change_password.php';
        include __DIR__ . '/../views/shared/footer.php';
    }

    public function handleChangePassword(): void {
        requireLogin();
        verifyCsrf();
        $userId  = (int)$_SESSION['user_id'];
        $current = $_POST['current_password'] ?? '';
        $newPwd  = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $dbUser  = $this->users->findByEmail($_SESSION['email']);
        if (!$dbUser || !verifyPwd($current, $dbUser['password_hash'])) {
            flash('error', 'Current password is incorrect.');
            redirect(BASE_URL . '/public/change-password.php');
        }
        if (!isStrongPassword($newPwd)) {
            flash('error', 'Password needs ≥8 chars, uppercase, lowercase and digit.');
            redirect(BASE_URL . '/public/change-password.php');
        }
        if ($newPwd !== $confirm) {
            flash('error', 'Passwords do not match.');
            redirect(BASE_URL . '/public/change-password.php');
        }
        $this->users->changePassword($userId, hashPwd($newPwd));
        auditLog('PASSWORD_CHANGE', 'user', $userId);
        flash('success', 'Password changed successfully.');
        redirect(BASE_URL . '/public/change-password.php');
    }

    public function handleLogout(): void {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) auditLog('LOGOUT', 'user', (int)$userId);
        logoutUser();
        redirect(BASE_URL . '/public/login.php');
    }

    private function dashUrl(string $role): string {
        $map = [
            'admin'   => '/public/admin/dashboard.php',
            'doctor'  => '/public/doctor/dashboard.php',
            'nurse'   => '/public/nurse/dashboard.php',
            'student' => '/public/student/dashboard.php',
        ];
        return BASE_URL . ($map[$role] ?? '/public/login.php');
    }
}
