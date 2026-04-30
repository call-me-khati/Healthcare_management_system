<?php // app/views/admin/add_student.php ?>
<?php if ($success = getFlash('success')): ?>
  <div class="alert alert-success">✅ <?= $success ?></div>
  <?php if ($tempPwd = getFlash('temp_pwd')): ?>
    <div class="alert alert-warning">
      🔑 Temporary Password: <strong style="font-size:15px;font-family:monospace"><?= htmlspecialchars($tempPwd) ?></strong><br>
      <small>Share this with the student. They will be prompted to update their profile on first login.</small>
    </div>
  <?php endif; ?>
<?php endif; ?>
<?php if ($error = getFlash('error')): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<div class="card" style="max-width:500px">
  <div class="card-title">🎓 Add Student Account</div>
  <p class="text-muted mb-16">Admin creates the account with basic info only. The student must complete their full profile (medical history, allergies, etc.) themselves.</p>

  <form method="POST" action="<?= BASE_URL ?>/public/admin/add-student.php">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <div class="form-group">
      <label>Full Name</label>
      <input type="text" name="full_name" class="form-control" placeholder="Student's full name">
    </div>
    <div class="form-group">
      <label>Email *</label>
      <input type="email" name="email" class="form-control" required placeholder="student@uni.edu">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Course</label>
        <input type="text" name="course" class="form-control" placeholder="e.g. BSc Computer Science">
      </div>
      <div class="form-group">
        <label>Year Level</label>
        <select name="year_level" class="form-control">
          <option value="">— Select —</option>
          <option>Year 1</option><option>Year 2</option><option>Year 3</option>
          <option>Year 4</option><option>Year 5</option><option>Graduate</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label>Contact Number</label>
      <input type="text" name="contact_number" class="form-control" placeholder="+880...">
    </div>
    <div class="alert alert-info" style="font-size:12px">
      ℹ️ A temporary password will be generated. The student must update their full profile including medical history and allergies themselves.
    </div>
    <button type="submit" class="btn btn-primary btn-block">Create Student Account</button>
  </form>
</div>
