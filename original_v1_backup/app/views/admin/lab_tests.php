<?php // app/views/admin/lab_tests.php ?>
<div class="page-header">
  <h2>🔬 Lab Tests</h2>
  <div style="display:flex;gap:8px">
    <?php foreach (['','Requested','In Progress','Done','Completed','Cancelled'] as $s): ?>
      <a href="?status=<?= $s ?>" class="btn btn-sm <?= ($_GET['status'] ?? '') === $s ? 'btn-primary' : 'btn-secondary' ?>"><?= $s ?: 'All' ?></a>
    <?php endforeach; ?>
  </div>
</div>
<div class="table-wrap">
  <table>
    <thead><tr><th>#</th><th>Student</th><th>Doctor</th><th>Test</th><th>Requested</th><th>Status</th><th>Patient Done</th></tr></thead>
    <tbody>
      <?php foreach ($tests as $i => $t):
        $stMap = ['Requested'=>'pill-orange','In Progress'=>'pill-blue','Done'=>'pill-yellow','Completed'=>'pill-green','Cancelled'=>'pill-gray'];
        $stCls = $stMap[$t['status']] ?? 'pill-gray';
      ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td><strong><?= htmlspecialchars($t['student_name']) ?></strong><br><span class="text-muted"><?= htmlspecialchars($t['student_uid'] ?? '') ?></span></td>
        <td class="text-muted"><?= htmlspecialchars($t['doctor_name']) ?></td>
        <td><?= htmlspecialchars($t['test_name']) ?></td>
        <td class="text-muted"><?= $t['request_date'] ?></td>
        <td><span class="pill <?= $stCls ?>"><?= $t['status'] ?></span></td>
        <td><?= $t['patient_done_at'] ? '<span class="pill pill-green">✓ '.date('d M',strtotime($t['patient_done_at'])).'</span>' : '<span class="pill pill-gray">Pending</span>' ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($tests)): ?><tr><td colspan="7" class="text-center text-muted" style="padding:30px">No lab tests found.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
