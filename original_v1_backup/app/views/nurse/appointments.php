<?php // app/views/nurse/appointments.php
$csrf = csrfToken();
?>
<?php if ($success = getFlash('success')): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

<div class="page-header">
  <h2>📅 All Appointments</h2>
  <form method="GET" style="display:flex;gap:8px;align-items:center">
    <select name="status" class="form-control" style="width:140px">
      <option value="">All Statuses</option>
      <option value="Pending"   <?= ($_GET['status']??'')==='Pending'   ?'selected':'' ?>>Pending</option>
      <option value="Confirmed" <?= ($_GET['status']??'')==='Confirmed' ?'selected':'' ?>>Confirmed</option>
      <option value="Completed" <?= ($_GET['status']??'')==='Completed' ?'selected':'' ?>>Completed</option>
      <option value="Cancelled" <?= ($_GET['status']??'')==='Cancelled' ?'selected':'' ?>>Cancelled</option>
    </select>
    <input type="date" name="date" class="form-control" style="width:160px" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <a href="?" class="btn btn-secondary btn-sm">Clear</a>
  </form>
</div>

<div class="stat-grid">
  <div class="stat-card blue"><div class="num"><?= $stats['Pending'] ?></div><div class="lbl">Pending</div></div>
  <div class="stat-card teal"><div class="num"><?= $stats['Confirmed'] ?></div><div class="lbl">Confirmed</div></div>
  <div class="stat-card green"><div class="num"><?= $stats['Completed'] ?></div><div class="lbl">Completed</div></div>
  <div class="stat-card red"><div class="num"><?= $stats['Cancelled'] ?></div><div class="lbl">Cancelled</div></div>
</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr><th>#</th><th>Queue</th><th>Patient</th><th>Doctor</th><th>Date & Time</th><th>Priority</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($appointments as $i => $a):
        $prCls = $a['priority']==='Emergency' ? 'pill-red' : ($a['priority']==='Urgent' ? 'pill-orange' : 'pill-blue');
        $stCls = match($a['status']) {
          'Confirmed'  => 'pill-green',
          'Completed'  => 'pill-gray',
          'Cancelled'  => 'pill-red',
          default      => 'pill-yellow'
        };
        $rowCls = $a['priority']==='Emergency' ? 'priority-emergency' : ($a['priority']==='Urgent' ? 'priority-urgent' : '');
      ?>
      <tr class="<?= $rowCls ?>">
        <td><?= $i+1 ?></td>
        <td><span class="queue-number"><?= $a['queue_number'] ?? '—' ?></span></td>
        <td>
          <strong><?= htmlspecialchars($a['student_name']) ?></strong><br>
          <span class="text-muted"><?= htmlspecialchars($a['student_uid'] ?? '') ?></span>
        </td>
        <td class="text-muted"><?= htmlspecialchars($a['doctor_name']) ?><br><span style="font-size:11px"><?= htmlspecialchars($a['specialization'] ?? '') ?></span></td>
        <td><?= $a['appointment_date'] ?> <span class="text-muted">@ <?= substr($a['appointment_time'],0,5) ?></span></td>
        <td><span class="pill <?= $prCls ?>"><?= $a['priority'] ?></span></td>
        <td><span class="pill <?= $stCls ?>"><?= $a['status'] ?></span></td>
        <td>
          <a href="<?= BASE_URL ?>/public/nurse/patient-record.php?student_id=<?= $a['student_id'] ?>" class="btn btn-sm btn-secondary">📋 Record</a>
          <?php if ($a['status'] === 'Pending'): ?>
          <form method="POST" action="<?= BASE_URL ?>/public/nurse/appointments.php" style="display:inline">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="appointment_id" value="<?= $a['appointment_id'] ?>">
            <button name="status" value="Confirmed" class="btn btn-sm btn-success">Confirm</button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($appointments)): ?>
        <tr><td colspan="8" class="text-center text-muted" style="padding:30px">No appointments found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
