<?php // app/views/student/appointments.php
$csrf = csrfToken();
?>
<?php if ($success = getFlash('success')): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if ($error   = getFlash('error')):   ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<div class="page-header">
  <h2>📅 My Appointments</h2>
  <div style="display:flex;gap:8px">
    <?php foreach ([''=>'All','Pending'=>'Pending','Confirmed'=>'Confirmed','Completed'=>'Completed','Cancelled'=>'Cancelled'] as $v=>$l): ?>
      <a href="?status=<?= $v ?>" class="btn btn-sm <?= ($_GET['status']??'')===$v ? 'btn-primary' : 'btn-secondary' ?>"><?= $l ?></a>
    <?php endforeach; ?>
    <a href="<?= BASE_URL ?>/public/student/doctors.php" class="btn btn-sm btn-outline">+ New</a>
  </div>
</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr><th>#</th><th>Doctor</th><th>Date & Time</th><th>Queue #</th><th>Priority</th><th>Status</th><th>Reason</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($appointments as $i => $a):
        $prCls = $a['priority']==='Emergency' ? 'pill-red' : ($a['priority']==='Urgent' ? 'pill-orange' : 'pill-blue');
        $stCls = match($a['status']) {'Confirmed'=>'pill-green','Completed'=>'pill-gray','Cancelled'=>'pill-red',default=>'pill-yellow'};
      ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td>
          <strong><?= htmlspecialchars($a['doctor_name']) ?></strong><br>
          <span class="text-muted"><?= htmlspecialchars($a['specialization'] ?? '') ?></span>
        </td>
        <td><?= $a['appointment_date'] ?> <span class="text-muted">@ <?= substr($a['appointment_time'],0,5) ?></span></td>
        <td><span class="queue-number"><?= $a['queue_number'] ?? '—' ?></span></td>
        <td><span class="pill <?= $prCls ?>"><?= $a['priority'] ?></span></td>
        <td><span class="pill <?= $stCls ?>"><?= $a['status'] ?></span></td>
        <td class="text-muted"><?= htmlspecialchars(mb_substr($a['reason'] ?? '—', 0, 50)) ?></td>
        <td>
          <?php if ($a['status'] === 'Pending'): ?>
          <form method="POST" action="<?= BASE_URL ?>/public/student/appointments.php" style="display:inline">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="appointment_id" value="<?= $a['appointment_id'] ?>">
            <button type="submit" class="btn btn-sm btn-danger"
                    onclick="return confirm('Cancel this appointment?')">Cancel</button>
          </form>
          <?php endif; ?>
          <?php if ($a['notes']): ?>
            <button class="btn btn-sm btn-secondary" onclick="alert(<?= htmlspecialchars(json_encode($a['notes'])) ?>)">📝 Notes</button>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($appointments)): ?>
        <tr><td colspan="8" class="text-center text-muted" style="padding:30px">
          No appointments. <a href="<?= BASE_URL ?>/public/student/doctors.php">Book one →</a>
        </td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
