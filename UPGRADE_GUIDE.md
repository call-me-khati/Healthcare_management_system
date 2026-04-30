### Rename: `students` → `patients`
```sql
-- OLD TABLE
CREATE TABLE `students` (
  `student_id`  ...
  `student_uid` ...
  `course`      ...

-- NEW TABLE (use this file: medibase_v2.sql)
CREATE TABLE `patients` (
  `patient_id`  ...
  `patient_uid` ...
  `role` ENUM('student','professor','staff','employee') -- NEW FIELD
  `illness`     VARCHAR(250) -- NEW FIELD (primary/known illness)
  `course`      ...
```

### Rename: `user_type = 'student'` → `user_type = 'patient'`
```sql
-- In `user` table, the ENUM now includes 'patient' instead of 'student'
`user_type` ENUM('admin','doctor','nurse','patient')
```

### All foreign keys updated:
- `appointments.student_id` → `appointments.patient_id`
- `consultations.student_id` → `consultations.patient_id`
- `lab_tests.student_id` → `lab_tests.patient_id`
- `follow_ups.student_id` → `follow_ups.patient_id`
- `feedback.student_id` → `feedback.patient_id`
- `patient_allergy_info.student_id` → `patient_allergy_info.patient_id`

### NEW TABLE: `medical_reports`
```sql
CREATE TABLE `medical_reports` (
  `report_id`    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `patient_id`   INT UNSIGNED NOT NULL,
  `report_type`  ENUM(
    'Diagnosis','Prescription','Symptoms','Doctor Notes','Nurse Notes',
    'Lab Test Results','X-Ray Report','MRI/CT Scan','Blood Test',
    'Vaccination Record','Allergy Record','Follow-up Notes',
    'Emergency Report','Surgery History','Other'
  ),
  `title`        VARCHAR(250),
  `description`  TEXT,
  `symptoms`     TEXT,
  `doctor_notes` TEXT,
  `nurse_notes`  TEXT,
  `file_upload`  VARCHAR(500), -- stored file path
  `file_type`    VARCHAR(50),
  `added_by`     INT UNSIGNED, -- user_id of doctor/nurse
  `added_role`   ENUM('doctor','nurse','admin'),
  `visit_date`   DATE,
  `is_private`   TINYINT(1),
  `created_at`   DATETIME,
  `updated_at`   DATETIME
);
```

---

## 2. PHP FILES TO RENAME/UPDATE

### Controllers
| Old File | New File |
|----------|----------|
| `app/controllers/StudentController.php` | `app/controllers/PatientController.php` |
| All references `$students` | `$patients` |
| All `StudentModel` | `PatientModel` |

### Models
| Old File | New File |
|----------|----------|
| `app/models/StudentModel.php` | `app/models/PatientModel.php` |
| All SQL `SELECT * FROM students` | `SELECT * FROM patients` |
| All `student_id` | `patient_id` |

### Views (Admin)
| Old File | New File |
|----------|----------|
| `app/views/admin/add_student.php` | `app/views/admin/add_patient.php` |
| `app/views/admin/list_students.php` | `app/views/admin/list_patients.php` |

### Views (Patient self-service — formerly /student/)
| Old Path | New Path |
|----------|----------|
| `public/student/dashboard.php` | `public/patient/dashboard.php` |
| `public/student/appointments.php` | `public/patient/appointments.php` |
| `public/student/book.php` | `public/patient/book.php` |
| `public/student/records.php` | `public/patient/records.php` |
| *(all files in public/student/)* | *(move to public/patient/)* |

### Public Admin Pages
| Old File | New File |
|----------|----------|
| `public/admin/add-student.php` | `public/admin/add-patient.php` |
| `public/admin/students.php` | `public/admin/patients.php` |

---

## 3. NEW FILES TO CREATE

### Medical Reports Module
```
app/controllers/MedicalReportController.php
app/models/MedicalReportModel.php
app/views/admin/medical_reports.php
app/views/admin/add_medical_report.php
app/views/doctor/medical_reports.php
app/views/nurse/medical_reports.php
public/admin/medical-reports.php
public/doctor/medical-reports.php
public/nurse/medical-reports.php
public/shared/print-report.php         ← printable report page
uploads/medical/                        ← upload directory
```

---

## 4. CODE CHANGES (Key Snippets)

### AdminController.php — Add Patient CRUD

```php
// ADD PATIENT
public function showAddPatient(): void {
    $error   = getFlash('error');
    $success = getFlash('success');
    $this->layout('Add Patient', 'add_patient.php', compact('error','success'));
}

public function handleAddPatient(): void {
    verifyCsrf();
    $fullName = postStr('full_name');
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $role     = postStr('role'); // student/professor/staff/employee
    $illness  = postStr('illness');
    // ... validation ...
    $uid = $this->patients->createPatient([
        'full_name' => $fullName, 'email' => $email,
        'role' => $role, 'illness' => $illness, ...
    ]);
    flash('success', "Patient {$fullName} added.");
    redirect(BASE_URL . '/public/admin/patients.php');
}

// DELETE PATIENT (NEW — was missing in v1)
public function deletePatient(): void {
    verifyCsrf();
    $patientId = (int)postStr('patient_id');
    $patient   = $this->patients->getById($patientId);
    if (!$patient) { flash('error','Patient not found.'); redirect(...); }
    $this->patients->delete($patientId);
    $this->users->delete($patient['user_id']);
    flash('success', 'Patient deleted successfully.');
    redirect(BASE_URL . '/public/admin/patients.php');
}

// EDIT PATIENT (NEW)
public function updatePatient(): void {
    verifyCsrf();
    $patientId = (int)postStr('patient_id');
    $data = [
        'full_name'      => postStr('full_name'),
        'phone'          => postStr('contact_number'),
        'illness'        => postStr('illness'),
        'blood_group'    => postStr('blood_group'),
        'medical_history'=> postStr('medical_history'),
    ];
    $this->patients->update($patientId, $data);
    flash('success', 'Patient updated.');
    redirect(BASE_URL . '/public/admin/patients.php');
}
```

### PatientModel.php

```php
class PatientModel {
    private PDO $pdo;
    public function __construct() { $this->pdo = getPDO(); }

    public function getAll(): array {
        $stmt = $this->pdo->query(
            "SELECT p.*, u.email, u.created_at
             FROM patients p
             JOIN user u ON p.user_id = u.user_id
             ORDER BY p.created_at DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT p.*, u.email, u.gender, u.date_of_birth
             FROM patients p JOIN user u ON p.user_id = u.user_id
             WHERE p.patient_id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function update(int $id, array $data): void {
        $sql = "UPDATE patients SET full_name=:full_name,
                contact_number=:phone, illness=:illness,
                blood_group=:blood_group, medical_history=:medical_history
                WHERE patient_id=:id";
        $this->pdo->prepare($sql)->execute([...$data, 'id' => $id]);
    }

    public function delete(int $id): void {
        $this->pdo->prepare("DELETE FROM patients WHERE patient_id=?")->execute([$id]);
    }

    public function countAll(): int {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    }
}
```

### MedicalReportModel.php

```php
class MedicalReportModel {
    private PDO $pdo;
    public function __construct() { $this->pdo = getPDO(); }

    public function getAll(array $filters = []): array {
        $where = []; $params = [];
        if (!empty($filters['patient_id'])) {
            $where[] = 'mr.patient_id = ?'; $params[] = $filters['patient_id'];
        }
        if (!empty($filters['report_type'])) {
            $where[] = 'mr.report_type = ?'; $params[] = $filters['report_type'];
        }
        if (!empty($filters['added_role'])) {
            $where[] = 'mr.added_role = ?'; $params[] = $filters['added_role'];
        }
        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $stmt = $this->pdo->prepare("
            SELECT mr.*, p.full_name AS patient_name, p.patient_uid,
                   u.full_name AS added_by_name
            FROM medical_reports mr
            JOIN patients p ON mr.patient_id = p.patient_id
            JOIN user    u ON mr.added_by    = u.user_id
            {$whereSQL}
            ORDER BY mr.visit_date DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPatientHistory(int $patientId): array {
        // Returns FULL history: reports + consultations + lab tests + prescriptions
        return $this->getAll(['patient_id' => $patientId]);
    }

    public function create(array $data): int {
        $sql = "INSERT INTO medical_reports
                (patient_id,report_type,title,description,symptoms,
                 doctor_notes,nurse_notes,file_upload,file_type,
                 added_by,added_role,visit_date,is_private)
                VALUES
                (:patient_id,:report_type,:title,:description,:symptoms,
                 :doctor_notes,:nurse_notes,:file_upload,:file_type,
                 :added_by,:added_role,:visit_date,:is_private)";
        $this->pdo->prepare($sql)->execute($data);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void {
        $sql = "UPDATE medical_reports SET
                report_type=:report_type, title=:title, description=:description,
                symptoms=:symptoms, doctor_notes=:doctor_notes,
                nurse_notes=:nurse_notes, updated_at=NOW()
                WHERE report_id=:id";
        $this->pdo->prepare($sql)->execute([...$data, 'id' => $id]);
    }

    public function delete(int $id): void {
        $this->pdo->prepare("DELETE FROM medical_reports WHERE report_id=?")->execute([$id]);
    }
}
```

### File Upload Handler

```php
// In MedicalReportController.php
private function handleFileUpload(): ?array {
    if (empty($_FILES['file_upload']['name'])) return null;
    $file = $_FILES['file_upload'];
    $allowed = ['pdf','jpg','jpeg','png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        flash('error','Invalid file type. Only PDF/JPG/PNG allowed.');
        return null;
    }
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
        flash('error','File too large. Max 5MB.');
        return null;
    }
    $uploadDir = __DIR__ . '/../../../uploads/medical/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $filename = uniqid('report_', true) . '.' . $ext;
    move_uploaded_file($file['tmp_name'], $uploadDir . $filename);
    return ['path' => 'uploads/medical/' . $filename, 'type' => $ext];
}
```

---

## 5. NAVIGATION UPDATES

### Shared Header (`app/views/shared/header.php`)

```php
// Replace all 'student' nav entries with 'patient'
$allNav = [
    'patient' => [
        ['href'=> BASE_URL.'/public/patient/dashboard.php',     'label'=>'Dashboard',      'icon'=>'⊞'],
        ['href'=> BASE_URL.'/public/patient/doctors.php',       'label'=>'Find Doctors',   'icon'=>'🔍'],
        ['href'=> BASE_URL.'/public/patient/appointments.php',  'label'=>'My Appointments','icon'=>'📅'],
        ['href'=> BASE_URL.'/public/patient/records.php',       'label'=>'Medical Records','icon'=>'📋'],
        ['href'=> BASE_URL.'/public/patient/lab-tests.php',     'label'=>'Lab Tests',      'icon'=>'🔬'],
        ['href'=> BASE_URL.'/public/patient/medical-reports.php','label'=>'My Reports',    'icon'=>'📁'],
        ['href'=> BASE_URL.'/public/patient/followups.php',     'label'=>'Follow-Ups',     'icon'=>'🔄'],
        ['href'=> BASE_URL.'/public/patient/feedback.php',      'label'=>'Feedback',       'icon'=>'💬'],
        ['href'=> BASE_URL.'/public/patient/profile.php',       'label'=>'Profile',        'icon'=>'👤'],
    ],
    'doctor' => [
        // ... existing entries ...
        ['href'=> BASE_URL.'/public/doctor/medical-reports.php','label'=>'Medical Reports','icon'=>'📁'],  // NEW
    ],
    'nurse' => [
        // ... existing entries ...
        ['href'=> BASE_URL.'/public/nurse/medical-reports.php', 'label'=>'Medical Reports','icon'=>'📁'],  // NEW
    ],
    'admin' => [
        // ... existing entries ...
        ['href'=> BASE_URL.'/public/admin/patients.php',        'label'=>'Patients',       'icon'=>'🏥'],  // RENAMED from students
        ['href'=> BASE_URL.'/public/admin/medical-reports.php', 'label'=>'Medical Reports','icon'=>'📁'],  // NEW
    ],
];

// Update role labels
$roleLabels = [
    'admin'   => 'Administrator',
    'doctor'  => 'Physician',
    'nurse'   => 'Nursing Staff',
    'patient' => 'Patient',  // CHANGED from 'student'
];
```

---

## 6. AUTH HELPER UPDATES

In `app/helpers/auth.php`, update all 'student' checks to 'patient':

```php
// OLD
function requireStudent() { requireRole('student'); }

// NEW
function requirePatient() { requireRole('patient'); }

// Also update session role check
$validRoles = ['admin', 'doctor', 'nurse', 'patient']; // was 'student'
```

---

## 7. PRINT REPORT — Add to every major view

```php
<!-- Add this button in every page header -->
<button onclick="printReport()" class="btn btn-print">
    <i class="fa fa-print"></i> Print Report
</button>

<!-- Print header (visible only when printing via CSS) -->
<div class="print-header" id="print-header">
    <div class="print-logo">🏥</div>
    <div class="print-header-text">
        <h1>MediBase University Medical Clinic</h1>
        <p>University Campus | Tel: +880 2 XXXX-XXXX | clinic@medibase.edu</p>
        <p><strong><?= $pageTitle ?></strong> | Generated: <?= date('d M Y, H:i') ?></p>
    </div>
</div>

<!-- Print footer -->
<div class="print-footer">
    <div>
        <p>Generated: <?= date('d M Y, H:i:s') ?></p>
        <p>By: <?= $currentUser['full_name'] ?> (<?= ucfirst($role) ?>)</p>
    </div>
    <div style="text-align:center">
        <p style="border-top:1px solid #999;padding-top:8px;min-width:200px">
            Authorized Signature
        </p>
        <p>Clinic Director / Medical Officer</p>
    </div>
    <div style="text-align:right">
        <p>MediBase University Medical Clinic</p>
        <p>Page 1 of 1</p>
    </div>
</div>
```

### Print CSS (in style.css):

```css
@media print {
    body { background: #fff !important; font-size: 12pt; }
    .sidebar, .topbar, .no-print, .btn-print { display: none !important; }
    .main-area { margin-left: 0 !important; }
    .content { padding: 0 !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; page-break-inside: avoid; }
    .print-header { display: flex !important; }
    .print-footer { display: flex !important; }
    table { font-size: 10pt; }
    .chart-container { page-break-inside: avoid; }
    @page { margin: 1.5cm; }
}
.print-header { display: none; }
.print-footer { display: none; }
```

---

## 8. EXPORT CSV/EXCEL FUNCTION

```php
// Add to PatientController.php
public function exportCsv(): void {
    requireRole('admin');
    $patients = $this->patients->getAll();
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="patients_' . date('Y-m-d') . '.csv"');
    $fp = fopen('php://output', 'w');
    fputcsv($fp, ['Patient ID','Name','Gender','Age','Role','Phone','Illness','Department','Status','Registered']);
    foreach ($patients as $p) {
        $dob = $p['date_of_birth'] ? (new DateTime($p['date_of_birth']))->diff(new DateTime())->y : '—';
        fputcsv($fp, [
            $p['patient_uid'], $p['full_name'], $p['gender'] ?? '—', $dob,
            $p['role'] ?? 'student', $p['contact_number'] ?? '—',
            $p['illness'] ?? '—', $p['department'] ?? '—',
            'Active', date('d M Y', strtotime($p['created_at']))
        ]);
    }
    fclose($fp);
    exit;
}

// Add to MedicalReportController.php
public function exportReportsCsv(): void {
    $reports = $this->medReports->getAll();
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="medical_reports_' . date('Y-m-d') . '.csv"');
    $fp = fopen('php://output', 'w');
    fputcsv($fp, ['Report ID','Patient Name','Report Type','Title','Added By','Role','Visit Date','Has File']);
    foreach ($reports as $r) {
        fputcsv($fp, [
            'RPT-' . $r['report_id'], $r['patient_name'], $r['report_type'],
            $r['title'], $r['added_by_name'], $r['added_role'],
            $r['visit_date'], $r['file_upload'] ? 'Yes' : 'No'
        ]);
    }
    fclose($fp);
    exit;
}
```

---

## 9. QUICK REFERENCE — URL CHANGES

| Old URL | New URL |
|---------|---------|
| `/public/admin/add-student.php` | `/public/admin/add-patient.php` |
| `/public/admin/students.php` | `/public/admin/patients.php` |
| `/public/admin/delete-user.php` | `/public/admin/delete-patient.php` (expanded) |
| `/public/student/dashboard.php` | `/public/patient/dashboard.php` |
| `/public/student/records.php` | `/public/patient/records.php` |
| — | `/public/admin/medical-reports.php` (NEW) |
| — | `/public/doctor/medical-reports.php` (NEW) |
| — | `/public/nurse/medical-reports.php` (NEW) |
| — | `/public/shared/print-report.php` (NEW) |

---

## 10. MIGRATION SCRIPT

To migrate existing data from v1 to v2:

```sql
ALTER TABLE `students`
    ADD COLUMN `role`    ENUM('student','professor','staff','employee') NOT NULL DEFAULT 'student' AFTER `student_uid`,
    ADD COLUMN `illness` VARCHAR(250) DEFAULT NULL AFTER `year_level`;

RENAME TABLE `students` TO `patients`;
ALTER TABLE `patients` RENAME COLUMN `student_id` TO `patient_id`;
ALTER TABLE `patients` RENAME COLUMN `student_uid` TO `patient_uid`;

ALTER TABLE `user` MODIFY `user_type` ENUM('admin','doctor','nurse','patient') NOT NULL DEFAULT 'patient';
UPDATE `user` SET `user_type` = 'patient' WHERE `user_type` = 'student';

ALTER TABLE `appointments`
    CHANGE `student_id` `patient_id` INT UNSIGNED NOT NULL;
ALTER TABLE `consultations`
    CHANGE `student_id` `patient_id` INT UNSIGNED NOT NULL;
ALTER TABLE `lab_tests`
    CHANGE `student_id` `patient_id` INT UNSIGNED NOT NULL;
ALTER TABLE `follow_ups`
    CHANGE `student_id` `patient_id` INT UNSIGNED NOT NULL;
ALTER TABLE `feedback`
    CHANGE `student_id` `patient_id` INT UNSIGNED NOT NULL;
ALTER TABLE `patient_allergy_info`
    CHANGE `student_id` `patient_id` INT UNSIGNED NOT NULL;


```

---
changes
| Feature | Status |
|---------|--------|
| Student → Patient rename (all tables, files, labels)
| Admin: Add Patient 
| Admin: Edit Patient
| Admin: Delete Patient 
| Admin: View Patient Details 
| Dashboard: 8 summary cards 
| Dashboard: Bar Chart 
| Dashboard: Pie Chart 
| Dashboard: Line Chart 
| Dashboard: Doughnut Chart 
| Dashboard: Area Chart 
| All tables: Search + Filter + Pagination 
| All tables: Export CSV + Excel buttons
| Print Report button on every page
| Print report: university logo + header + footer 
| Medical Reports module 
| Doctor can add medical reports 
| Nurse can add medical reports 
| 15 report types (X-Ray, MRI, Blood Test, etc.)
| File upload (PDF, JPG, PNG) 
| View Full Medical History timeline
| Print patient medical history 
| `medical_reports` database table 
