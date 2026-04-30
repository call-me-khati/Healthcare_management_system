<?php /* app/views/admin/list_students.php — var: $students */ ?>

<div class="card">
  <div class="card-hd">
    <h3>🎓 Registered Students (<?= count($students) ?>)</h3>
    <input type="text" id="search" placeholder="Search by name…"
           style="max-width:220px" oninput="filterRows(this.value)">
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Name</th><th>Email</th><th>Course</th><th>Year</th><th>Contact</th><th>Registered</th></tr>
      </thead>
      <tbody id="tbody">
        <?php if (empty($students)): ?>
          <tr><td colspan="6" style="text-align:center;padding:28px;color:#9ca3af">
            No students registered yet.
          </td></tr>
        <?php else: foreach ($students as $s): ?>
          <tr data-name="<?= strtolower(htmlspecialchars($s['full_name'])) ?>">
            <td><strong><?= htmlspecialchars($s['full_name']) ?></strong></td>
            <td style="font-size:12px;color:#6b7280"><?= htmlspecialchars($s['email']) ?></td>
            <td><?= htmlspecialchars($s['course'] ?? '—') ?></td>
            <td><?= htmlspecialchars($s['year_level'] ?? '—') ?></td>
            <td><?= htmlspecialchars($s['contact_number'] ?? '—') ?></td>
            <td style="font-size:12px;color:#6b7280">
              <?= date('d M Y', strtotime($s['created_at'])) ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function filterRows(q) {
  q = q.toLowerCase();
  document.querySelectorAll('#tbody tr[data-name]').forEach(row => {
    row.style.display = row.dataset.name.includes(q) ? '' : 'none';
  });
}
</script>
