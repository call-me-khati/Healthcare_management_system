<?php // app/views/student/dashboard.php ?>

<!-- Notifications/reminders -->
<?php foreach ($followUps as $fu): ?>
  <div class="alert alert-warning">
    🔄 You have a follow-up with <strong><?= htmlspecialchars($fu['doctor_name']) ?></strong> on <strong><?= $fu['followup_date'] ?></strong>.
    <?php if ($fu['status'] === 'Scheduled'): ?>
      <a href="<?= BASE_URL ?>/public/student/book.php?doctor_id=<?= $fu['doctor_id'] ?>&followup_id=<?= $fu['followup_id'] ?>">Book Appointment →</a>
    <?php endif; ?>
  </div>
<?php endforeach; ?>

<?php foreach ($labTests as $lt): if ($lt['status'] === 'Requested'): ?>
  <div class="alert alert-info">
    🔬 Lab test pending: <strong><?= htmlspecialchars($lt['test_name']) ?></strong>.
    Please complete this test and mark it done.
    <a href="<?= BASE_URL ?>/public/student/lab-tests.php">Go to Lab Tests →</a>
  </div>
<?php endif; endforeach; ?>

<!-- Stats -->
<div class="stat-grid">
  <div class="stat-card blue"><div class="num"><?= $stats['total'] ?></div><div class="lbl">📅 Total Appointments</div></div>
  <div class="stat-card yellow"><div class="num"><?= $stats['Pending'] ?></div><div class="lbl">⏳ Pending</div></div>
  <div class="stat-card green"><div class="num"><?= $stats['Completed'] ?></div><div class="lbl">✅ Completed</div></div>
  <div class="stat-card orange">
    <div class="num"><?= count(array_filter($labTests, fn($l) => in_array($l['status'],['Requested','Done']))) ?></div>
    <div class="lbl">🔬 Active Lab Tests</div>
  </div>
</div>

<!-- Quick actions -->
<div class="card mb-24">
  <div class="card-title">⚡ Quick Actions</div>
  <div style="display:flex;flex-wrap:wrap;gap:10px">
    <a href="<?= BASE_URL ?>/public/student/doctors.php" class="btn btn-primary">🔍 Find Doctor & Book</a>
    <a href="<?= BASE_URL ?>/public/student/records.php" class="btn btn-outline">📋 My Medical Records</a>
    <a href="<?= BASE_URL ?>/public/student/lab-tests.php" class="btn btn-outline">🔬 Lab Tests</a>
    <a href="<?= BASE_URL ?>/public/student/followups.php" class="btn btn-outline">🔄 Follow-Ups</a>
    <a href="<?= BASE_URL ?>/public/student/feedback.php" class="btn btn-secondary">💬 Submit Feedback</a>
  </div>
</div>

<!-- Student info card -->
<div class="grid-2">
  <div class="card">
    <div class="card-title">👤 My Info</div>
    <table style="width:100%;font-size:13px">
      <tr><td class="text-muted" style="padding:5px 0;width:40%">UID</td><td><strong><?= htmlspecialchars($student['student_uid'] ?? 'Not assigned') ?></strong></td></tr>
      <tr><td class="text-muted" style="padding:5px 0">Course</td><td><?= htmlspecialchars($student['course'] ?? '—') ?></td></tr>
      <tr><td class="text-muted" style="padding:5px 0">Year</td><td><?= htmlspecialchars($student['year_level'] ?? '—') ?></td></tr>
      <tr><td class="text-muted" style="padding:5px 0">Blood Group</td><td>
        <?php if ($student['blood_group']): ?>
          <span class="pill pill-red"><?= htmlspecialchars($student['blood_group']) ?></span>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/public/student/profile.php" style="font-size:12px">Add blood group →</a>
        <?php endif; ?>
      </td></tr>
      <tr><td class="text-muted" style="padding:5px 0">Contact</td><td><?= htmlspecialchars($student['contact_number'] ?? '—') ?></td></tr>
    </table>
  </div>

  <!-- Recent appointments -->
  <div class="card">
    <div class="card-title">📅 Recent Appointments</div>
    <?php if (empty($recent)): ?>
      <p class="text-muted">No appointments yet. <a href="<?= BASE_URL ?>/public/student/doctors.php">Book one →</a></p>
    <?php else: ?>
      <?php foreach (array_slice($recent, 0, 4) as $a):
        $stCls = match($a['status']) {'Confirmed'=>'pill-green','Completed'=>'pill-gray','Cancelled'=>'pill-red',default=>'pill-yellow'};
      ?>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border)">
        <div>
          <strong style="font-size:13px"><?= htmlspecialchars($a['doctor_name']) ?></strong><br>
          <span class="text-muted"><?= $a['appointment_date'] ?> @ <?= substr($a['appointment_time'],0,5) ?></span>
        </div>
        <span class="pill <?= $stCls ?>"><?= $a['status'] ?></span>
      </div>
      <?php endforeach; ?>
      <a href="<?= BASE_URL ?>/public/student/appointments.php" class="btn btn-sm btn-outline mt-16">View All</a>
    <?php endif; ?>
  </div>
</div>
