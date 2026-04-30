<?php // app/views/admin/feedback.php ?>
<?php if ($success = getFlash('success')): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

<div class="page-header">
  <h2>📨 Feedback & Complaints <span class="text-muted">(Admin Only)</span></h2>
  <div style="display:flex;gap:8px">
    <a href="?type=Complaint" class="btn btn-sm btn-danger">Complaints</a>
    <a href="?type=Feedback" class="btn btn-sm btn-secondary">Feedback</a>
    <a href="?type=Suggestion" class="btn btn-sm btn-secondary">Suggestions</a>
    <a href="?" class="btn btn-sm btn-outline">All</a>
  </div>
</div>

<div class="alert alert-info" style="font-size:12px">
  🔒 This section is <strong>only visible to Admin</strong>. Doctors and Nurses cannot see feedback or complaints.
</div>

<?php if (empty($items)): ?>
  <div class="card text-center" style="padding:40px">
    <p style="font-size:32px;margin-bottom:12px">📭</p>
    <p class="text-muted">No submissions found.</p>
  </div>
<?php else: ?>
<div class="table-wrap">
  <table>
    <thead>
      <tr><th>#</th><th>Student</th><th>Type</th><th>Subject</th><th>Status</th><th>Date</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($items as $i => $f):
        $typeClass = $f['type'] === 'Complaint' ? 'pill-red' : ($f['type'] === 'Suggestion' ? 'pill-blue' : 'pill-gray');
        $stClass = $f['status'] === 'Open' ? 'pill-orange' : ($f['status'] === 'Resolved' ? 'pill-green' : 'pill-gray');
      ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td>
          <strong><?= htmlspecialchars($f['student_name']) ?></strong><br>
          <span class="text-muted"><?= htmlspecialchars($f['student_uid'] ?? '') ?></span>
        </td>
        <td><span class="pill <?= $typeClass ?>"><?= $f['type'] ?></span></td>
        <td>
          <?= htmlspecialchars($f['subject']) ?><br>
          <span class="text-muted" style="font-size:11px"><?= htmlspecialchars(mb_substr($f['message'], 0, 80)) ?>...</span>
        </td>
        <td><span class="pill <?= $stClass ?>"><?= $f['status'] ?></span></td>
        <td class="text-muted"><?= date('d M Y', strtotime($f['created_at'])) ?></td>
        <td>
          <button class="btn btn-sm btn-secondary"
            onclick="openResolveModal(<?= $f['feedback_id'] ?>, <?= htmlspecialchars(json_encode($f['admin_notes'] ?? '')) ?>, <?= htmlspecialchars(json_encode($f['message'])) ?>)">
            Review
          </button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<!-- Resolve Modal -->
<div id="resolveModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center">
  <div class="card" style="width:500px">
    <div class="card-title">Review Feedback</div>
    <div class="form-group">
      <label>Original Message</label>
      <div id="fb_message" style="background:#f8fafc;border:1px solid var(--border);border-radius:7px;padding:10px;font-size:13px;max-height:120px;overflow-y:auto"></div>
    </div>
    <form method="POST" action="<?= BASE_URL ?>/public/admin/handle-feedback.php">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <input type="hidden" name="feedback_id" id="resolve_id">
      <div class="form-group">
        <label>Update Status</label>
        <select name="status" class="form-control">
          <option value="Open">Open</option>
          <option value="Under Review">Under Review</option>
          <option value="Resolved">Resolved</option>
          <option value="Closed">Closed</option>
        </select>
      </div>
      <div class="form-group">
        <label>Admin Notes</label>
        <textarea name="admin_notes" id="resolve_notes" class="form-control" rows="3" placeholder="Internal notes..."></textarea>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('resolveModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>
<script>
function openResolveModal(id, notes, message) {
  document.getElementById('resolve_id').value    = id;
  document.getElementById('resolve_notes').value = notes || '';
  document.getElementById('fb_message').textContent = message;
  document.getElementById('resolveModal').style.display = 'flex';
}
</script>
