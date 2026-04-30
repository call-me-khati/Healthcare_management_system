<?php // app/views/student/followups.php ?>
<div class="page-header"><h2>🔄 My Follow-Ups</h2></div>

<?php if (empty($followUps)): ?>
  <div class="card text-center" style="padding:40px">
    <p style="font-size:32px;margin-bottom:12px">🔄</p>
    <p class="text-muted">No follow-ups scheduled. Your doctor will add these after a consultation.</p>
  </div>
<?php else: ?>
  <?php foreach ($followUps as $fu):
    $stMap = ['Scheduled'=>'pill-orange','Booked'=>'pill-blue','Completed'=>'pill-green','Missed'=>'pill-red'];
    $stCls = $stMap[$fu['status']] ?? 'pill-gray';
    $isPast = strtotime($fu['followup_date']) < strtotime('today');
  ?>
  <div class="card mb-16">
    <div style="display:flex;justify-content:space-between;align-items:flex-start">
      <div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
          <strong><?= $fu['followup_date'] ?></strong>
          <span class="pill <?= $stCls ?>"><?= $fu['status'] ?></span>
          <?php if ($isPast && $fu['status'] === 'Scheduled'): ?>
            <span class="pill pill-red">Overdue</span>
          <?php endif; ?>
        </div>
        <div class="text-muted" style="font-size:13px">
          Doctor: <strong><?= htmlspecialchars($fu['doctor_name']) ?></strong>
        </div>
        <?php if ($fu['notes']): ?>
          <div style="margin-top:8px;font-size:13px;background:#f8fafc;border-radius:6px;padding:8px">
            <?= nl2br(htmlspecialchars($fu['notes'])) ?>
          </div>
        <?php endif; ?>
      </div>

      <?php if ($fu['status'] === 'Scheduled'): ?>
        <a href="<?= BASE_URL ?>/public/student/book.php?doctor_id=<?= $fu['doctor_id'] ?>&followup_id=<?= $fu['followup_id'] ?>"
           class="btn btn-primary btn-sm">
          📅 Book Appointment
        </a>
      <?php elseif ($fu['status'] === 'Booked'): ?>
        <span class="pill pill-blue">Appointment Booked ✓</span>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
<?php endif; ?>
