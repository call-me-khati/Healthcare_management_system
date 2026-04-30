<?php // app/views/student/lab_tests.php
$csrf = csrfToken();
?>
<?php if ($success = getFlash('success')): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if ($error   = getFlash('error')):   ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<div class="page-header"><h2>🔬 My Lab Tests</h2></div>

<?php if (empty($tests)): ?>
  <div class="card text-center" style="padding:40px">
    <p style="font-size:32px;margin-bottom:12px">🔬</p>
    <p class="text-muted">No lab tests requested yet.</p>
  </div>
<?php else: ?>
  <?php foreach ($tests as $lt):
    $stMap = ['Requested'=>'pill-orange','In Progress'=>'pill-blue','Done'=>'pill-yellow','Completed'=>'pill-green','Cancelled'=>'pill-gray'];
    $stCls = $stMap[$lt['status']] ?? 'pill-gray';
    $lsCls = $lt['status']==='Completed' ? 'done' : ($lt['status']==='Done' ? 'done' : 'pending');
  ?>
  <div class="lab-card <?= $lsCls ?>">
    <div style="flex:1">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
        <strong><?= htmlspecialchars($lt['test_name']) ?></strong>
        <span class="pill <?= $stCls ?>"><?= $lt['status'] ?></span>
        <?php if ($lt['is_followup'] ?? false): ?><span class="pill pill-purple">Follow-up</span><?php endif; ?>
      </div>
      <div class="text-muted" style="font-size:12px">
        Requested by <strong><?= htmlspecialchars($lt['doctor_name']) ?></strong> on <?= $lt['request_date'] ?>
      </div>

      <?php if ($lt['status'] === 'Requested' && !$lt['patient_done_at']): ?>
        <div class="alert alert-info" style="margin-top:10px;font-size:13px">
          ⏰ Please complete this test at the lab and click <strong>"Mark as Done"</strong> below.
        </div>
      <?php endif; ?>

      <?php if ($lt['result']): ?>
        <div style="margin-top:10px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:10px">
          <strong style="font-size:12px;color:#166534">✅ Lab Result:</strong>
          <p style="font-size:13px;margin-top:4px"><?= nl2br(htmlspecialchars($lt['result'])) ?></p>
          <?php if ($lt['result_date']): ?><p class="text-muted" style="font-size:11px;margin-top:4px">Result date: <?= $lt['result_date'] ?></p><?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <div style="display:flex;flex-direction:column;gap:8px;align-items:flex-end">
      <?php if ($lt['status'] === 'Requested' && !$lt['patient_done_at']): ?>
        <form method="POST" action="<?= BASE_URL ?>/public/student/lab-tests.php">
          <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
          <input type="hidden" name="lab_test_id" value="<?= $lt['lab_test_id'] ?>">
          <button type="submit" class="btn btn-success"
                  onclick="return confirm('Confirm that you have completed this test?')">
            ✅ Mark as Done
          </button>
        </form>
      <?php elseif ($lt['patient_done_at']): ?>
        <span class="pill pill-green">✓ Completed <?= date('d M', strtotime($lt['patient_done_at'])) ?></span>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
<?php endif; ?>
