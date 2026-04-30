<?php // app/views/admin/dashboard.php ?>

<!-- Alerts for low stock / expiring -->
<?php foreach ($lowStock as $m): ?>
  <div class="alert alert-warning">⚠️ Low Stock: <strong><?= htmlspecialchars($m['medicine_name']) ?></strong> — only <?= $m['quantity'] ?> <?= htmlspecialchars($m['unit']) ?> remaining.</div>
<?php endforeach; ?>
<?php foreach ($expiring as $m): ?>
  <div class="alert alert-danger">🚨 Expiring Soon: <strong><?= htmlspecialchars($m['medicine_name']) ?></strong> expires on <?= $m['expiry_date'] ?>.</div>
<?php endforeach; ?>
<?php if ($openComplaints > 0): ?>
  <div class="alert alert-info">📨 You have <strong><?= $openComplaints ?></strong> open complaint(s). <a href="<?= BASE_URL ?>/public/admin/feedback.php">View →</a></div>
<?php endif; ?>

<!-- User counts -->
<div class="stat-grid">
  <div class="stat-card blue">
    <div class="num"><?= $counts['doctor'] ?? 0 ?></div>
    <div class="lbl">👨‍⚕️ Doctors</div>
  </div>
  <div class="stat-card green">
    <div class="num"><?= $counts['nurse'] ?? 0 ?></div>
    <div class="lbl">👩‍⚕️ Nurses</div>
  </div>
  <div class="stat-card purple">
    <div class="num"><?= $counts['student'] ?? 0 ?></div>
    <div class="lbl">🎓 Patients</div>
  </div>
  <div class="stat-card orange">
    <div class="num"><?= $apptStats['Pending'] ?></div>
    <div class="lbl">📅 Pending Appts</div>
  </div>
  <div class="stat-card teal">
    <div class="num"><?= $apptStats['Confirmed'] ?></div>
    <div class="lbl">✅ Confirmed</div>
  </div>
  <div class="stat-card green">
    <div class="num"><?= $apptStats['Completed'] ?></div>
    <div class="lbl">🏁 Completed</div>
  </div>
  <div class="stat-card red">
    <div class="num"><?= count($pendingLabs) ?></div>
    <div class="lbl">🔬 Pending Labs</div>
  </div>
  <div class="stat-card orange">
    <div class="num"><?= count($lowStock) ?></div>
    <div class="lbl">💊 Low Stock Meds</div>
  </div>
</div>

<div class="grid-2">
  <!-- Monthly appointments chart -->
  <div class="card">
    <div class="card-title">📊 Appointment Trends (6 months)</div>
    <?php if ($monthlyAppts): ?>
      <div style="display:flex;align-items:flex-end;gap:8px;height:120px">
        <?php
          $maxVal = max(array_column($monthlyAppts, 'total')) ?: 1;
          foreach ($monthlyAppts as $m):
            $h = round(($m['total']/$maxVal)*100);
        ?>
        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px">
          <span style="font-size:11px;font-weight:600;color:#2563eb"><?= $m['total'] ?></span>
          <div style="width:100%;background:#2563eb;border-radius:4px 4px 0 0;height:<?= $h ?>px"></div>
          <span style="font-size:10px;color:#64748b;white-space:nowrap"><?= $m['month'] ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-muted text-center" style="padding:30px 0">No appointment data yet</p>
    <?php endif; ?>
  </div>

  <!-- Common diagnoses -->
  <div class="card">
    <div class="card-title">🩺 Most Common Diagnoses</div>
    <?php if ($commonDx): ?>
      <?php foreach ($commonDx as $dx): ?>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;font-size:13px">
          <span><?= htmlspecialchars($dx['diagnosis']) ?></span>
          <span class="pill pill-blue"><?= $dx['cnt'] ?> cases</span>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-muted">No consultation records yet.</p>
    <?php endif; ?>
  </div>
</div>

<div class="grid-2 mt-24">
  <!-- Top prescribed medicines -->
  <div class="card">
    <div class="card-title">💊 Top Prescribed Medicines</div>
    <?php if ($topMeds): ?>
      <?php foreach ($topMeds as $med): ?>
        <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:13px">
          <span><?= htmlspecialchars($med['medicine_name']) ?></span>
          <span class="pill pill-green"><?= $med['times_prescribed'] ?>×</span>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-muted">No prescription records yet.</p>
    <?php endif; ?>
  </div>

  <!-- Doctor workload -->
  <div class="card">
    <div class="card-title">👨‍⚕️ Doctor Workload</div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Doctor</th><th>Specialization</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($doctors as $d): ?>
          <tr>
            <td><?= htmlspecialchars($d['full_name'] ?? $d['employee_id']) ?></td>
            <td class="text-muted"><?= htmlspecialchars($d['specialization'] ?? '—') ?></td>
            <td>
              <?php $s = $d['availability_status'];
                $cls = $s === 'Available' ? 'pill-green' : ($s === 'On Leave' ? 'pill-red' : 'pill-yellow');
              ?>
              <span class="pill <?= $cls ?>"><?= $s ?></span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Quick actions -->
<div class="card mt-24">
  <div class="card-title">⚡ Quick Actions</div>
  <div style="display:flex;flex-wrap:wrap;gap:10px">
    <a href="<?= BASE_URL ?>/public/admin/create-staff.php" class="btn btn-primary">➕ Add Doctor/Nurse</a>
    <a href="<?= BASE_URL ?>/public/admin/add-student.php" class="btn btn-outline">🎓 Add Student</a>
    <a href="<?= BASE_URL ?>/public/admin/medicines.php" class="btn btn-warning">💊 Manage Inventory</a>
    <a href="<?= BASE_URL ?>/public/admin/feedback.php" class="btn btn-secondary">📨 View Complaints</a>
    <a href="<?= BASE_URL ?>/public/admin/audit-log.php" class="btn btn-secondary">🛡️ Audit Log</a>
  </div>
</div>
