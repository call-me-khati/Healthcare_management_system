<?php // app/views/student/feedback.php
$csrf = csrfToken();
?>
<?php if ($success = getFlash('success')): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if ($error   = getFlash('error')):   ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<div class="grid-2">
  <!-- Submit form -->
  <div class="card">
    <div class="card-title">✍️ Submit Feedback or Complaint</div>
    <div class="alert alert-info" style="font-size:12px">
      🔒 Your submission is <strong>completely confidential</strong>. Only the Admin can view complaints and feedback — Doctors and Nurses cannot see them.
    </div>
    <form method="POST" action="<?= BASE_URL ?>/public/student/feedback.php">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <div class="form-group">
        <label>Type</label>
        <select name="type" class="form-control">
          <option value="Feedback">General Feedback</option>
          <option value="Complaint">Complaint</option>
          <option value="Suggestion">Suggestion</option>
        </select>
      </div>
      <div class="form-group">
        <label>Subject *</label>
        <input type="text" name="subject" class="form-control" required placeholder="Brief subject...">
      </div>
      <div class="form-group">
        <label>Message *</label>
        <textarea name="message" class="form-control" rows="5" required placeholder="Describe your feedback or complaint in detail..."></textarea>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Submit</button>
    </form>
  </div>

  <!-- My submissions -->
  <div class="card">
    <div class="card-title">📨 My Submissions</div>
    <?php if (empty($items)): ?>
      <p class="text-muted text-center" style="padding:30px">No submissions yet.</p>
    <?php else: ?>
      <?php foreach ($items as $fb):
        $typeClass = $fb['type']==='Complaint' ? 'pill-red' : ($fb['type']==='Suggestion' ? 'pill-blue' : 'pill-gray');
        $stClass = $fb['status']==='Open' ? 'pill-orange' : ($fb['status']==='Resolved' ? 'pill-green' : 'pill-gray');
      ?>
      <div style="padding:12px;border-bottom:1px solid var(--border)">
        <div style="display:flex;justify-content:space-between;margin-bottom:6px">
          <span class="pill <?= $typeClass ?>"><?= $fb['type'] ?></span>
          <span class="pill <?= $stClass ?>"><?= $fb['status'] ?></span>
        </div>
        <strong style="font-size:13px"><?= htmlspecialchars($fb['subject']) ?></strong>
        <p class="text-muted" style="font-size:12px;margin-top:3px"><?= date('d M Y', strtotime($fb['created_at'])) ?></p>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
