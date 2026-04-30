<?php // app/views/nurse/patient_record.php ?>
<div class="card mb-24">
  <div class="card-title">👤 Patient Record <span class="pill pill-blue" style="margin-left:8px">Read Only</span></div>
  <div class="form-row-3">
    <div><span class="section-title">Name</span><strong><?= htmlspecialchars($student['full_name']) ?></strong></div>
    <div><span class="section-title">UID</span><span class="pill pill-blue"><?= htmlspecialchars($student['student_uid'] ?? '—') ?></span></div>
    <div><span class="section-title">Blood Group</span><span class="pill pill-red"><?= htmlspecialchars($student['blood_group'] ?? 'Unknown') ?></span></div>
    <div><span class="section-title">Course</span><?= htmlspecialchars($student['course'] ?? '—') ?></div>
    <div><span class="section-title">Contact</span><?= htmlspecialchars($student['contact_number'] ?? '—') ?></div>
    <div><span class="section-title">Emergency</span><?= htmlspecialchars($student['emergency_contact_name'] ?? '—') ?> <?= htmlspecialchars($student['emergency_contact_phone'] ?? '') ?></div>
  </div>
  <?php if (!empty($allergies)): ?>
  <div style="margin-top:14px">
    <span class="section-title">⚠️ Allergies</span>
    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:6px">
      <?php foreach ($allergies as $al):
        $cls = $al['level']==='Life-threatening' ? 'pill-red' : ($al['level']==='Severe' ? 'pill-orange' : 'pill-yellow');
      ?><span class="pill <?= $cls ?>"><?= htmlspecialchars($al['name']) ?> (<?= $al['level'] ?>)</span><?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<div class="card mb-24">
  <div class="card-title">📋 Consultation History</div>
  <?php if (empty($records)): ?>
    <p class="text-muted">No records yet.</p>
  <?php else: ?>
  <div class="timeline">
    <?php foreach ($records as $r): ?>
    <div class="tl-item">
      <div class="tl-date"><?= $r['consultation_date'] ?> — <?= htmlspecialchars($r['doctor_name']) ?></div>
      <div class="tl-card">
        <?php if ($r['diagnosis']): ?><p><strong>Diagnosis:</strong> <?= htmlspecialchars($r['diagnosis']) ?></p><?php endif; ?>
        <?php if ($r['consultation_notes']): ?><p style="margin-top:6px;font-size:12px;color:#64748b"><?= nl2br(htmlspecialchars($r['consultation_notes'])) ?></p><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<div class="card">
  <div class="card-title">🔬 Lab Tests</div>
  <?php if (empty($labTests)): ?>
    <p class="text-muted">No lab tests.</p>
  <?php else: ?>
    <?php foreach ($labTests as $lt):
      $stMap = ['Requested'=>'pill-orange','In Progress'=>'pill-blue','Done'=>'pill-yellow','Completed'=>'pill-green','Cancelled'=>'pill-gray'];
      $stCls = $stMap[$lt['status']] ?? 'pill-gray';
    ?>
    <div class="lab-card">
      <div style="flex:1">
        <strong><?= htmlspecialchars($lt['test_name']) ?></strong>
        <span class="text-muted"> — <?= $lt['request_date'] ?></span>
        <?php if ($lt['result']): ?><p style="margin-top:4px;font-size:12px"><?= htmlspecialchars($lt['result']) ?></p><?php endif; ?>
      </div>
      <span class="pill <?= $stCls ?>"><?= $lt['status'] ?></span>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
