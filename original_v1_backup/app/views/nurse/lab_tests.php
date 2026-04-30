<?php // app/views/nurse/lab_tests.php
$csrf = csrfToken();
?>
<?php if ($success = getFlash('success')): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

<div class="page-header">
  <h2>🔬 Lab Tests</h2>
  <div style="display:flex;gap:8px">
    <?php foreach (['','Requested','In Progress','Done','Completed'] as $s): ?>
      <a href="?status=<?= $s ?>" class="btn btn-sm <?= ($_GET['status']??'')===$s ? 'btn-primary' : 'btn-secondary' ?>"><?= $s ?: 'All' ?></a>
    <?php endforeach; ?>
  </div>
</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr><th>#</th><th>Patient</th><th>Doctor</th><th>Test Name</th><th>Requested</th><th>Status</th><th>Patient Done</th><th>Update</th></tr>
    </thead>
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
        <td class="text-muted"><?= htmlspecialchars($t['doctor_name']) ?></td>
        <td><?= htmlspecialchars($t['test_name']) ?></td>
        <td class="text-muted"><?= $t['request_date'] ?></td>
        <td><span class="pill <?= $stCls ?>"><?= $t['status'] ?></span></td>
        <td>
          <?php if ($t['patient_done_at']): ?>
            <span class="pill pill-green">✓ <?= date('d M', strtotime($t['patient_done_at'])) ?></span>
          <?php else: ?>
            <span class="pill pill-gray">Pending</span>
          <?php endif; ?>
        </td>
        <td>
          <?php if (!in_array($t['status'], ['Completed','Cancelled'])): ?>
          <form method="POST" action="<?= BASE_URL ?>/public/nurse/lab-tests.php" style="display:flex;gap:6px">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="lab_test_id" value="<?= $t['lab_test_id'] ?>">
            <select name="status" class="form-control" style="width:130px;padding:4px 8px;font-size:12px">
              <option value="Requested"   <?= $t['status']==='Requested'   ? 'selected':'' ?>>Requested</option>
              <option value="In Progress" <?= $t['status']==='In Progress' ? 'selected':'' ?>>In Progress</option>
              <option value="Done"        <?= $t['status']==='Done'        ? 'selected':'' ?>>Done</option>
              <option value="Completed"   <?= $t['status']==='Completed'   ? 'selected':'' ?>>Completed</option>
            </select>
            <button type="submit" class="btn btn-sm btn-primary">Save</button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($tests)): ?>
        <tr><td colspan="8" class="text-center text-muted" style="padding:30px">No lab tests found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
