<?php // app/views/doctor/profile.php
$csrf = csrfToken(); ?>
<?php if ($success = getFlash('success')): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if ($error   = getFlash('error')):   ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<div class="card" style="max-width:700px">
  <div class="card-title">👨‍⚕️ My Profile</div>
  <form method="POST" action="<?= BASE_URL ?>/public/doctor/profile.php">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <div class="form-row">
      <div class="form-group"><label>Full Name *</label><input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($doctor['full_name'] ?? '') ?>"></div>
      <div class="form-group"><label>Specialization</label><input type="text" name="specialization" class="form-control" value="<?= htmlspecialchars($doctor['specialization'] ?? '') ?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Department</label><input type="text" name="department" class="form-control" value="<?= htmlspecialchars($doctor['department'] ?? '') ?>"></div>
      <div class="form-group"><label>Consultation Fee (৳)</label><input type="number" name="consultation_fee" class="form-control" min="0" step="50" value="<?= $doctor['consultation_fee'] ?? 0 ?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Gender</label>
        <select name="gender" class="form-control">
          <option value="">— Select —</option>
          <?php foreach (['Male','Female','Other'] as $g): ?><option value="<?= $g ?>" <?= ($doctor['gender']??'')===$g?'selected':'' ?>><?= $g ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label>Contact Number</label><input type="text" name="contact_number" class="form-control" value="<?= htmlspecialchars($doctor['contact_number'] ?? '') ?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Date of Birth</label><input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($doctor['date_of_birth'] ?? '') ?>"></div>
      <div class="form-group"><label>Employee ID</label><input type="text" class="form-control" value="<?= htmlspecialchars($doctor['employee_id'] ?? '') ?>" disabled><p class="form-hint">Cannot be changed</p></div>
    </div>
    <div class="form-group"><label>Address</label><textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($doctor['address'] ?? '') ?></textarea></div>
    <div class="form-group"><label>Bio</label><textarea name="bio" class="form-control" rows="3"><?= htmlspecialchars($doctor['bio'] ?? '') ?></textarea></div>
    <div style="background:#f8fafc;border-radius:6px;padding:10px;margin-bottom:16px;font-size:12px"><strong>Email:</strong> <?= htmlspecialchars($doctor['email'] ?? '') ?></div>
    <button type="submit" class="btn btn-primary">💾 Update Profile</button>
  </form>
</div>
