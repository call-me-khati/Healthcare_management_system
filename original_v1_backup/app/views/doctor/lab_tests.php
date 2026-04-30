<?php // app/views/doctor/lab_tests.php ?>
<?php if ($success = getFlash('success')): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

<div class="page-header"><h2>🔬 Lab Tests I Requested</h2></div>

<div class="table-wrap">
  <table>
    <thead><tr><th>#</th><th>Patient</th><th>Test</th><th>Requested</th><th>Status</th><th>Patient Done</th><th>Result</th><th>Action</th></tr></thead>
    <tbody>
      <?php foreach ($tests as $i => $t):
        $stMap = ['Requested'=>'pill-orange','In Progress'=>'pill-blue','Done'=>'pill-yellow','Completed'=>'pill-green','Cancelled'=>'pill-gray'];
        $stCls = $stMap[$t['status']] ?? 'pill-gray';
      ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td>
          <strong><?= htmlspecialchars($t['student_name']) ?></strong><br>
          <span class="text-muted"><?= htmlspecialchars($t['student_uid'] ?? '') ?></span>
        </td>
        <td><?= htmlspecialchars($t['test_name']) ?></td>
        <td class="text-muted"><?= $t['request_date'] ?></td>
        <td><span class="pill <?= $stCls ?>"><?= $t['status'] ?></span></td>
        <td><?= $t['patient_done_at'] ? '<span class="pill pill-green">✓ Done</span>' : '<span class="pill pill-gray">Pending</span>' ?></td>
        <td style="max-width:180px;font-size:12px"><?= htmlspecialchars(mb_substr($t['result'] ?? '—', 0, 60)) ?></td>
        <td>
          <?php if ($t['status'] === 'Done' || $t['status'] === 'In Progress'): ?>
          <button class="btn btn-sm btn-primary"
            onclick="openResultModal(<?= $t['lab_test_id'] ?>, <?= htmlspecialchars(json_encode($t['test_name'])) ?>, <?= htmlspecialchars(json_encode($t['result'] ?? '')) ?>)">
            Enter Result
          </button>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($tests)): ?><tr><td colspan="8" class="text-center text-muted" style="padding:30px">No lab tests found.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Result Modal -->
<div id="resultModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center">
  <div class="card" style="width:460px">
    <div class="card-title">Enter Lab Result</div>
    <div id="result_test_name" style="margin-bottom:12px;font-weight:600;color:#2563eb"></div>
    <form method="POST" action="<?= BASE_URL ?>/public/doctor/lab-tests.php">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <input type="hidden" name="lab_test_id" id="result_id">
      <div class="form-group">
        <label>Result</label>
        <textarea name="result" id="result_text" class="form-control" rows="5" required placeholder="Enter lab test result..."></textarea>
      </div>
      <p class="form-hint">Patient will be notified automatically when you save the result.</p>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:12px">
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('resultModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Result & Notify Patient</button>
      </div>
    </form>
  </div>
</div>
<script>
function openResultModal(id, name, existing) {
  document.getElementById('result_id').value    = id;
  document.getElementById('result_test_name').textContent = name;
  document.getElementById('result_text').value  = existing || '';
  document.getElementById('resultModal').style.display = 'flex';
}
</script>
