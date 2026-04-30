<?php
/**
 * app/views/shared/header.php
 */
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/render.php';
require_once __DIR__ . '/../../models/NotificationModel.php';

$user  = currentUser();
$role  = $user['role'];
$name  = $user['full_name'] ?: $user['email'];
$unreadCount = 0;
if ($user['user_id']) {
    $nm = new NotificationModel();
    $unreadCount = $nm->countUnread((int)$user['user_id']);
}

$notifUrl = [
    'admin'   => BASE_URL . '/public/admin/notifications.php',
    'doctor'  => BASE_URL . '/public/doctor/notifications.php',
    'nurse'   => BASE_URL . '/public/nurse/notifications.php',
    'student' => BASE_URL . '/public/student/notifications.php',
];

$allNav = [
    'student' => [
        ['href'=>BASE_URL.'/public/student/dashboard.php',     'label'=>'Dashboard',      'icon'=>'⊞'],
        ['href'=>BASE_URL.'/public/student/doctors.php',       'label'=>'Find Doctors',   'icon'=>'🔍'],
        ['href'=>BASE_URL.'/public/student/appointments.php',  'label'=>'My Appointments','icon'=>'📅'],
        ['href'=>BASE_URL.'/public/student/records.php',       'label'=>'Medical Records','icon'=>'📋'],
        ['href'=>BASE_URL.'/public/student/lab-tests.php',     'label'=>'Lab Tests',      'icon'=>'🔬'],
        ['href'=>BASE_URL.'/public/student/followups.php',     'label'=>'Follow-Ups',     'icon'=>'🔄'],
        ['href'=>BASE_URL.'/public/student/feedback.php',      'label'=>'Feedback',       'icon'=>'💬'],
        ['href'=>BASE_URL.'/public/student/profile.php',       'label'=>'Profile',        'icon'=>'👤'],
    ],
    'doctor' => [
        ['href'=>BASE_URL.'/public/doctor/dashboard.php',      'label'=>'Dashboard',      'icon'=>'⊞'],
        ['href'=>BASE_URL.'/public/doctor/appointments.php',   'label'=>'Appointments',   'icon'=>'📅'],
        ['href'=>BASE_URL.'/public/doctor/schedule.php',       'label'=>'My Schedule',    'icon'=>'🗓'],
        ['href'=>BASE_URL.'/public/doctor/lab-tests.php',      'label'=>'Lab Tests',      'icon'=>'🔬'],
        ['href'=>BASE_URL.'/public/doctor/profile.php',        'label'=>'Profile',        'icon'=>'👤'],
    ],
    'nurse' => [
        ['href'=>BASE_URL.'/public/nurse/dashboard.php',       'label'=>'Dashboard',      'icon'=>'⊞'],
        ['href'=>BASE_URL.'/public/nurse/appointments.php',    'label'=>'Appointments',   'icon'=>'📅'],
        ['href'=>BASE_URL.'/public/nurse/lab-tests.php',       'label'=>'Lab Tests',      'icon'=>'🔬'],
        ['href'=>BASE_URL.'/public/nurse/medicines.php',       'label'=>'Medicines',      'icon'=>'💊'],
        ['href'=>BASE_URL.'/public/nurse/profile.php',         'label'=>'Profile',        'icon'=>'👤'],
    ],
    'admin' => [
        ['href'=>BASE_URL.'/public/admin/dashboard.php',       'label'=>'Dashboard',      'icon'=>'⊞'],
        ['href'=>BASE_URL.'/public/admin/create-staff.php',    'label'=>'Create Staff',   'icon'=>'➕'],
        ['href'=>BASE_URL.'/public/admin/add-student.php',     'label'=>'Add Student',    'icon'=>'🎓'],
        ['href'=>BASE_URL.'/public/admin/doctors.php',         'label'=>'Doctors',        'icon'=>'👨‍⚕️'],
        ['href'=>BASE_URL.'/public/admin/nurses.php',          'label'=>'Nurses',         'icon'=>'👩‍⚕️'],
        ['href'=>BASE_URL.'/public/admin/students.php',        'label'=>'Students',       'icon'=>'🎓'],
        ['href'=>BASE_URL.'/public/admin/appointments.php',    'label'=>'Appointments',   'icon'=>'📋'],
        ['href'=>BASE_URL.'/public/admin/medicines.php',       'label'=>'Inventory',      'icon'=>'💊'],
        ['href'=>BASE_URL.'/public/admin/lab-tests.php',       'label'=>'Lab Tests',      'icon'=>'🔬'],
        ['href'=>BASE_URL.'/public/admin/feedback.php',        'label'=>'Complaints',     'icon'=>'📨'],
        ['href'=>BASE_URL.'/public/admin/audit-log.php',       'label'=>'Audit Log',      'icon'=>'🛡️'],
    ],
];
$navItems = $allNav[$role] ?? [];
$roleLabels = ['admin'=>'Administrator','doctor'=>'Physician','nurse'=>'Nursing Staff','student'=>'Student'];
$roleBadgeColors = ['admin'=>'#e74c3c','doctor'=>'#2980b9','nurse'=>'#27ae60','student'=>'#8e44ad'];
$roleLabel      = $roleLabels[$role]      ?? ucfirst($role);
$roleBadgeColor = $roleBadgeColors[$role] ?? '#555';
$currentScript  = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'MediBase') ?> — MediBase University Health</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/style.css">
</head>
<body>
<div class="app-wrap">

<!-- Sidebar -->
<aside class="sidebar">
  <div class="sb-logo">
    <div style="font-size:22px">🏥</div>
    <div>
      <div style="font-weight:700;color:#fff;font-size:14px">MediBase</div>
      <div style="font-size:10px;color:#64748b">University Health v2</div>
    </div>
  </div>

  <nav class="sb-nav">
    <?php foreach ($navItems as $item):
      $isCurrent = str_ends_with($item['href'], $currentScript);
    ?>
      <a href="<?= htmlspecialchars($item['href']) ?>"
         class="sb-link<?= $isCurrent ? ' active' : '' ?>">
        <span style="font-size:14px;flex-shrink:0"><?= $item['icon'] ?></span>
        <?= htmlspecialchars($item['label']) ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="sb-foot">
    <?php if (!empty($notifUrl[$role])): ?>
    <a href="<?= $notifUrl[$role] ?>" class="sb-link" style="border-radius:6px;padding:7px 10px;font-size:12px;position:relative">
      🔔 Notifications
      <?php if ($unreadCount > 0): ?>
        <span class="badge-red"><?= $unreadCount ?></span>
      <?php endif; ?>
    </a>
    <?php endif; ?>
    <div class="sb-user">
      <strong><?= htmlspecialchars(mb_substr($name, 0, 24)) ?></strong>
      <span style="display:inline-block;background:<?= $roleBadgeColor ?>;color:#fff;
            border-radius:20px;padding:1px 8px;font-size:10px;margin-top:3px">
        <?= $roleLabel ?>
      </span>
    </div>
    <a href="<?= BASE_URL ?>/public/change-password.php"
       class="sb-link" style="border-radius:6px;padding:7px 10px;font-size:12px;margin-bottom:4px">
      🔒 Change Password
    </a>
    <a href="<?= BASE_URL ?>/public/logout.php"
       class="btn btn-secondary btn-sm btn-block" style="margin-top:4px">
      Sign out
    </a>
  </div>
</aside>

<!-- Main area -->
<div class="main-area">
  <div class="topbar">
    <h1><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
    <span style="font-size:13px;color:#6b7280"><?= date('l, d M Y') ?></span>
  </div>
  <div class="content">
