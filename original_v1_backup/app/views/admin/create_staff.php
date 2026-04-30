<?php /* app/views/admin/create_staff.php — vars: $error, $success, $tempPwd */ ?>

<?php if ($error):   ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

<?php if ($tempPwd): ?>
  <div class="temp-pwd-box" style="margin-bottom:20px">
    <h4>⚠️ Temporary Password — Share with the new staff member</h4>
    <p style="font-size:12px;color:#92400e;margin:4px 0 10px">
      Write this down now. It will <strong>not</strong> be shown again.
      The staff member must change it on first login.
    </p>
    <code><?= htmlspecialchars($tempPwd) ?></code>
  </div>
<?php endif; ?>

<div style="max-width:480px">
  <div class="card">
    <div class="card-hd"><h3>➕ Create Doctor or Nurse Account</h3></div>
    <div class="card-bd">

      <div class="alert alert-info" style="margin-bottom:18px">
        Only the <strong>Employee ID</strong>, <strong>email</strong>, and
        <strong>role</strong> are required here. The staff member will complete
        their profile (name, department, etc.) when they first log in.
      </div>

      <form method="POST" action="<?= BASE_URL ?>/public/admin/create-staff.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <div class="form-group">
          <label>Role *</label>
          <select name="role" required>
            <option value="">— select role —</option>
            <option value="doctor" <?= ($_POST['role']??'')==='doctor'?'selected':'' ?>>Doctor</option>
            <option value="nurse"  <?= ($_POST['role']??'')==='nurse' ?'selected':'' ?>>Nurse</option>
          </select>
        </div>

        <div class="form-group">
          <label>Employee ID *</label>
          <input type="text" name="employee_id" required
                 placeholder="e.g. DOC-2024-001"
                 value="<?= htmlspecialchars($_POST['employee_id'] ?? '') ?>">
          <p style="font-size:11px;color:#6b7280;margin-top:3px">
            Must be unique across all staff.
          </p>
        </div>

        <div class="form-group">
          <label>Email Address *</label>
          <input type="email" name="email" required
                 placeholder="staff@example.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <p style="font-size:12px;color:#6b7280;margin-bottom:16px">
          A random temporary password will be generated. Share it with the staff member
          so they can log in and complete their profile.
        </p>

        <button type="submit" class="btn btn-primary">Create Account</button>
        <a href="<?= BASE_URL ?>/public/admin/dashboard.php"
           class="btn btn-secondary" style="margin-left:8px">Cancel</a>
      </form>
    </div>
  </div>
</div>
