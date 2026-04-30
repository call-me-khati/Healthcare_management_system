<?php // app/views/doctor/patient_record.php ?>

<div class="card mb-24">
  <div class="card-title">👤 Patient Profile</div>
  <div class="form-row-3">
    <div><span class="section-title">Full Name</span><strong><?= htmlspecialchars($student['full_name']) ?></strong></div>
    <div><span class="section-title">Student UID</span><span class="pill pill-blue"><?= htmlspecialchars($student['student_uid'] ?? '—') ?></span></div>
    <div><span class="section-title">Blood Group</span><span class="pill pill-red"><?= htmlspecialchars($student['blood_group'] ?? 'Unknown') ?></span></div>
    <div><span class="section-title">Course</span><?= htmlspecialchars($student['course'] ?? '—') ?></div>
    <div><span class="section-title">Year</span><?= htmlspecialchars($student['year_level'] ?? '—') ?></div>
    <div><span class="section-title">Contact</span><?= htmlspecialchars($student['contact_number'] ?? '—') ?></div>
  </div>

  <?php if (!empty($allergies)): ?>
  <div style="margin-top:16px">
    <span class="section-title">⚠️ Allergies</span>
    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:6px">
      <?php foreach ($allergies as $al):
        $cls = $al['level'] === 'Life-threatening' ? 'pill-red' : ($al['level'] === 'Severe' ? 'pill-orange' : 'pill-yellow');
      ?>
        <span class="pill <?= $cls ?>"><?= htmlspecialchars($al['name']) ?> — <?= $al['level'] ?></span>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if (!empty($student['medical_history'])): ?>
  <div style="margin-top:14px">
    <span class="section-title">Medical History</span>
    <p style="font-size:13px;margin-top:4px;background:#f8fafc;border-radius:6px;padding:10px"><?= nl2br(htmlspecialchars($student['medical_history'])) ?></p>
  </div>
  <?php endif; ?>
</div>

<!-- Consultation timeline -->
<div class="card mb-24">
  <div class="card-title">📋 Consultation History</div>
  <?php if (empty($records)): ?>
    <p class="text-muted">No consultation records yet.</p>
  <?php else: ?>
  <div class="timeline">
    <?php foreach ($records as $r): ?>
    <div class="tl-item">
      <div class="tl-date"><?= $r['consultation_date'] ?> — <?= htmlspecialchars($r['specialization'] ?? '') ?> (<?= htmlspecialchars($r['doctor_name']) ?>)</div>
      <div class="tl-card">
        <?php if ($r['diagnosis']): ?><p><strong>Diagnosis:</strong> <?= htmlspecialchars($r['diagnosis']) ?></p><?php endif; ?>
        <?php if ($r['consultation_notes']): ?><p style="margin-top:6px;font-size:12px;color:#64748b"><?= nl2br(htmlspecialchars($r['consultation_notes'])) ?></p><?php endif; ?>
        <?php if ($r['follow_up_date']): ?><p style="margin-top:6px;font-size:12px"><strong>Follow-up:</strong> <?= $r['follow_up_date'] ?></p><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<!-- Lab Tests -->
<div class="card mb-24">
  <div class="card-title">🔬 Lab Tests</div>
  <?php if (empty($labTests)): ?>
    <p class="text-muted">No lab tests requested.</p>
  <?php else: ?>
    <?php foreach ($labTests as $lt):
      $lsCls = $lt['status'] === 'Completed' ? 'done' : ($lt['status'] === 'Done' ? 'done' : 'pending');
    ?>
    <div class="lab-card <?= $lsCls ?>">
      <div style="flex:1">
        <strong><?= htmlspecialchars($lt['test_name']) ?></strong>
        <span class="text-muted"> — Requested: <?= $lt['request_date'] ?></span>
        <?php if ($lt['result']): ?>
          <p style="margin-top:6px;font-size:12px;background:#f0fdf4;padding:6px;border-radius:4px"><?= nl2br(htmlspecialchars($lt['result'])) ?></p>
        <?php endif; ?>
      </div>
      <?php $stMap = ['Requested'=>'pill-orange','In Progress'=>'pill-blue','Done'=>'pill-yellow','Completed'=>'pill-green','Cancelled'=>'pill-gray'];
            $stCls = $stMap[$lt['status']] ?? 'pill-gray'; ?>
      <span class="pill <?= $stCls ?>"><?= $lt['status'] ?></span>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Follow-ups -->
<div class="card">
  <div class="card-title">🔄 Follow-Ups</div>
  <?php if (empty($followUps)): ?>
    <p class="text-muted">No follow-ups scheduled.</p>
  <?php else: ?>
    <?php foreach ($followUps as $fu):
      $fuCls = $fu['status'] === 'Completed' ? 'pill-green' : ($fu['status'] === 'Booked' ? 'pill-blue' : ($fu['status'] === 'Missed' ? 'pill-red' : 'pill-orange'));
    ?>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px;border-bottom:1px solid var(--border)">
      <div>
        <strong><?= $fu['followup_date'] ?></strong>
        <span class="text-muted"> — <?= htmlspecialchars($fu['notes'] ?? 'General follow-up') ?></span>
      </div>
      <span class="pill <?= $fuCls ?>"><?= $fu['status'] ?></span>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
