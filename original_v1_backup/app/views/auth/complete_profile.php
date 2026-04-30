<?php
// app/views/auth/complete_profile.php
$csrf = csrfToken();
?>
<div class="card" style="max-width:560px;margin:40px auto">
  <div style="text-align:center;margin-bottom:24px">
    <div style="font-size:40px">🏥</div>
    <h2 style="margin:8px 0">Complete Your Profile</h2>
    <p style="color:#6b7280;font-size:14px">Please fill in your details and set a new password before continuing.</p>
  </div>

  <?php if ($error = getFlash('error')): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST" action="<?= BASE_URL ?>/public/complete-profile.php">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

    <div class="form-group">
      <label>Full Name *</label>
      <input type="text" name="full_name" class="form-control" required
             value="<?= htmlspecialchars($_SESSION['full_name'] ?? '') ?>">
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <div class="form-group">
        <label>Gender</label>
        <select name="gender" class="form-control">
          <option value="">— Select —</option>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
          <option value="Other">Other</option>
        </select>
      </div>
      <div class="form-group">
        <label>Date of Birth</label>
        <input type="date" name="date_of_birth" class="form-control">
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <div class="form-group">
        <label>Phone</label>
        <input type="text" name="contact_number" class="form-control" placeholder="+880...">
      </div>
      <div class="form-group">
        <label>Department</label>
        <input type="text" name="department" class="form-control">
      </div>
    </div>

    <div class="form-group">
      <label>Address</label>
      <textarea name="address" class="form-control" rows="2" placeholder="Your address..."></textarea>
    </div>

    <?php if ($role === 'doctor'): ?>
    <hr style="margin:16px 0">
    <h4 style="color:#2980b9;margin-bottom:12px">Doctor Details</h4>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <div class="form-group">
        <label>Specialization *</label>
        <input type="text" name="specialization" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Consultation Fee (৳)</label>
        <input type="number" name="consultation_fee" class="form-control" min="0" step="50">
      </div>
    </div>
    <div class="form-group">
      <label>Bio / About</label>
      <textarea name="bio" class="form-control" rows="3" placeholder="Brief professional bio..."></textarea>
    </div>
    <?php endif; ?>

    <hr style="margin:16px 0">
    <h4 style="margin-bottom:12px">Set New Password</h4>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <div class="form-group">
        <label>New Password *</label>
        <input type="password" name="new_password" class="form-control" required
               placeholder="Min 8 chars, A-Z, a-z, 0-9">
      </div>
      <div class="form-group">
        <label>Confirm Password *</label>
        <input type="password" name="confirm_password" class="form-control" required>
      </div>
    </div>
    <div class="form-group" style="background:#f0f9ff;border-radius:6px;padding:10px;font-size:12px;color:#0369a1">
      Password requirements: at least 8 characters, 1 uppercase, 1 lowercase, 1 digit
    </div>

    <button type="submit" class="btn btn-primary btn-block" style="margin-top:8px">
      Complete Profile & Continue →
    </button>
  </form>
</div>
