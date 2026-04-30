<?php // app/views/nurse/profile.php
$csrf = csrfToken(); ?>
<?php if ($success = getFlash('success')): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<div class="card" style="max-width:600px">
  <div class="card-title">👩‍⚕️ My Profile</div>
  <form method="POST" action="<?= BASE_URL ?>/public/nurse/profile.php">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <div class="form-row">
      <div class="form-group"><label>Full Name *</label><input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($nurse['full_name'] ?? '') ?>"></div>
      <div class="form-group"><label>Gender</label>
        <select name="gender" class="form-control">
          <option value="">— Select —</option>
          <?php foreach (['Male','Female','Other'] as $g): ?><option value="<?= $g ?>" <?= ($nurse['gender']??'')===$g?'selected':'' ?>><?= $g ?></option><?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Department</label><input type="text" name="department" class="form-control" value="<?= htmlspecialchars($nurse['department'] ?? '') ?>"></div>
      <div class="form-group"><label>Contact Number</label><input type="text" name="contact_number" class="form-control" value="<?= htmlspecialchars($nurse['contact_number'] ?? '') ?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Date of Birth</label><input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($nurse['date_of_birth'] ?? '') ?>"></div>
      <div class="form-group"><label>Employee ID</label><input type="text" class="form-control" value="<?= htmlspecialchars($nurse['employee_id'] ?? '') ?>" disabled></div>
    </div>
    <div class="form-group"><label>Address</label><textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($nurse['address'] ?? '') ?></textarea></div>
    <div style="background:#f8fafc;border-radius:6px;padding:10px;margin-bottom:16px;font-size:12px"><strong>Email:</strong> <?= htmlspecialchars($nurse['email'] ?? '') ?></div>
    <button type="submit" class="btn btn-primary">💾 Update Profile</button>
  </form>
</div>
