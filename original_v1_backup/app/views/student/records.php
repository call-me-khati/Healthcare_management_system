<?php // app/views/student/records.php ?>
<div class="page-header">
  <h2>📋 My Medical Records</h2>
  <span class="text-muted">Patient ID: <strong><?= htmlspecialchars($student['student_uid'] ?? '—') ?></strong></span>
</div>

<?php if (!empty($allergies)): ?>
<div class="alert alert-warning" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center">
  <strong>⚠️ Your Allergies:</strong>
  <?php foreach ($allergies as $al):
    $cls = $al['level']==='Life-threatening' ? 'pill-red' : ($al['level']==='Severe' ? 'pill-orange' : 'pill-yellow');
  ?><span class="pill <?= $cls ?>"><?= htmlspecialchars($al['name']) ?> (<?= $al['level'] ?>)</span><?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (empty($records)): ?>
  <div class="card text-center" style="padding:40px">
    <p style="font-size:32px;margin-bottom:12px">📂</p>
    <p class="text-muted">No consultation records yet. Book an appointment to get started.</p>
    <a href="<?= BASE_URL ?>/public/student/doctors.php" class="btn btn-primary mt-16">Find a Doctor</a>
  </div>
<?php else: ?>
  <div class="timeline">
    <?php foreach ($records as $r): ?>
    <div class="tl-item">
      <div class="tl-date">
        <?= $r['consultation_date'] ?> —
        <strong><?= htmlspecialchars($r['doctor_name']) ?></strong>
        <span class="text-muted">(<?= htmlspecialchars($r['specialization'] ?? '') ?>)</span>
      </div>
      <div class="tl-card">
        <?php if ($r['diagnosis']): ?>
          <p><strong>🩺 Diagnosis:</strong> <?= htmlspecialchars($r['diagnosis']) ?></p>
        <?php endif; ?>
        <?php if ($r['consultation_notes']): ?>
          <p style="margin-top:6px;font-size:13px;color:#475569"><?= nl2br(htmlspecialchars($r['consultation_notes'])) ?></p>
        <?php endif; ?>
        <?php if ($r['follow_up_date']): ?>
          <p style="margin-top:6px;font-size:12px"><strong>🔄 Follow-up:</strong> <?= $r['follow_up_date'] ?>
            <?php if ($r['follow_up_notes']): ?> — <?= htmlspecialchars($r['follow_up_notes']) ?><?php endif; ?>
          </p>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
