<?php /* app/views/admin/list_appointments.php — vars: $appointments, $stats */ ?>

<!-- Stats bar -->
<div class="stats-grid" style="margin-bottom:18px">
  <?php foreach (['Pending'=>'#92400e','Confirmed'=>'#1e40af','Completed'=>'#065f46','Cancelled'=>'#6b7280'] as $s=>$c): ?>
    <div class="stat-card" style="padding:12px 16px">
      <div class="label" style="font-size:10px"><?= $s ?></div>
      <div class="value" style="font-size:22px;color:<?= $c ?>"><?= $stats[$s] ?></div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Filters -->
<form method="GET" style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
  <select name="status" onchange="this.form.submit()" style="max-width:160px">
    <option value="">All Statuses</option>
    <?php foreach(['Pending','Confirmed','Completed','Cancelled'] as $s): ?>
      <option value="<?= $s ?>" <?= ($_GET['status']??'')===$s?'selected':'' ?>><?= $s ?></option>
    <?php endforeach; ?>
  </select>
  <input type="date" name="date" value="<?= htmlspecialchars($_GET['date']??'') ?>"
         onchange="this.form.submit()">
  <a href="?" class="btn btn-secondary btn-sm">Clear Filters</a>
</form>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Student</th><th>Doctor</th><th>Specialization</th><th>Date</th><th>Time</th><th>Status</th><th>Reason</th></tr>
      </thead>
      <tbody>
        <?php if (empty($appointments)): ?>
          <tr><td colspan="7" style="text-align:center;padding:28px;color:#9ca3af">No appointments found.</td></tr>
        <?php else: foreach ($appointments as $a): ?>
          <tr>
            <td><?= htmlspecialchars($a['student_name']) ?></td>
            <td><?= htmlspecialchars($a['doctor_name']) ?></td>
            <td style="color:#6b7280"><?= htmlspecialchars($a['specialization']??'—') ?></td>
            <td><?= date('d M Y', strtotime($a['appointment_date'])) ?></td>
            <td><?= substr($a['appointment_time'],0,5) ?></td>
            <td><span class="badge badge-<?= strtolower($a['status']) ?>"><?= $a['status'] ?></span></td>
            <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;
                       white-space:nowrap;color:#6b7280;font-size:13px"
                title="<?= htmlspecialchars($a['reason']??'') ?>">
              <?= htmlspecialchars(mb_substr($a['reason']??'—',0,60)) ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
