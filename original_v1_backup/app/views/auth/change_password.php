<?php // app/views/auth/change_password.php
// This view is included INSIDE the layout (header/footer wrap it)
?>
<div class="card" style="max-width:480px">
  <div class="card-title">🔒 Change Password</div>

  <?php if ($success = getFlash('success')): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <?php if ($error = getFlash('error')): ?>
    <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="<?= BASE_URL ?>/public/change-password.php">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <div class="form-group">
      <label>Current Password</label>
      <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
    </div>
    <div class="form-group">
      <label>New Password</label>
      <input type="password" name="new_password" class="form-control" required autocomplete="new-password" placeholder="Min 8 chars, A-Z, a-z, 0-9">
    </div>
    <div class="form-group">
      <label>Confirm New Password</label>
      <input type="password" name="confirm_password" class="form-control" required autocomplete="new-password">
    </div>
    <div class="alert alert-info" style="font-size:12px">
      Password must be at least 8 characters and include uppercase, lowercase, and a number.
    </div>
    <button type="submit" class="btn btn-primary">Update Password</button>
  </form>
</div>
