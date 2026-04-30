<?php // app/views/student/profile.php
$csrf = csrfToken();
?>
<?php if ($success = getFlash('success')): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if ($error   = getFlash('error')):   ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/public/student/profile.php">
  <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

  <div class="grid-2">
    <!-- Personal info -->
    <div class="card">
      <div class="card-title">👤 Personal Information</div>
      <div class="form-group">
        <label>Full Name *</label>
        <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($student['full_name']) ?>">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Gender</label>
          <select name="gender" class="form-control">
            <option value="">— Select —</option>
            <?php foreach (['Male','Female','Other'] as $g): ?>
              <option value="<?= $g ?>" <?= ($student['gender'] ?? '')===$g ? 'selected':'' ?>><?= $g ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Date of Birth</label>
          <input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($student['date_of_birth'] ?? '') ?>">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Course</label>
          <input type="text" name="course" class="form-control" value="<?= htmlspecialchars($student['course'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Year Level</label>
          <select name="year_level" class="form-control">
            <option value="">— Select —</option>
            <?php foreach (['Year 1','Year 2','Year 3','Year 4','Year 5','Graduate'] as $y): ?>
              <option value="<?= $y ?>" <?= ($student['year_level']??'')===$y ? 'selected':'' ?>><?= $y ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Contact Number</label>
          <input type="text" name="contact_number" class="form-control" value="<?= htmlspecialchars($student['contact_number'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Blood Group</label>
          <select name="blood_group" class="form-control">
            <option value="">— Select —</option>
            <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
              <option value="<?= $bg ?>" <?= ($student['blood_group']??'')===$bg ? 'selected':'' ?>><?= $bg ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label>Address</label>
        <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($student['user_address'] ?? '') ?></textarea>
      </div>
    </div>

    <!-- Medical info -->
    <div>
      <div class="card mb-16">
        <div class="card-title">🏥 Emergency Contact</div>
        <div class="form-row">
          <div class="form-group">
            <label>Name</label>
            <input type="text" name="emergency_contact_name" class="form-control" value="<?= htmlspecialchars($student['emergency_contact_name'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Phone</label>
            <input type="text" name="emergency_contact_phone" class="form-control" value="<?= htmlspecialchars($student['emergency_contact_phone'] ?? '') ?>">
          </div>
        </div>
      </div>

      <div class="card mb-16">
        <div class="card-title">📋 Medical History</div>
        <div class="form-group mb-0">
          <textarea name="medical_history" class="form-control" rows="4"
                    placeholder="List any past conditions, surgeries, chronic diseases..."><?= htmlspecialchars($student['medical_history'] ?? '') ?></textarea>
          <p class="form-hint">This helps doctors make better decisions. Only doctor and nurse can view this.</p>
        </div>
      </div>

      <!-- Student ID display (read only) -->
      <div class="card">
        <div class="card-title">🎓 Student ID</div>
        <div style="background:#eff6ff;border:1px solid #93c5fd;border-radius:7px;padding:12px;text-align:center">
          <div style="font-size:22px;font-weight:700;color:#1d4ed8;letter-spacing:2px"><?= htmlspecialchars($student['student_uid'] ?? 'Not yet assigned') ?></div>
          <p class="text-muted" style="font-size:11px;margin-top:4px">Use this ID for all health center visits</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Allergies -->
  <div class="card mt-24">
    <div class="card-title">⚠️ Allergies</div>
    <p class="text-muted mb-16" style="font-size:13px">Select all that apply. This information is critical for safe prescriptions.</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px">
      <?php
        $myAllergyIds = array_column($allergies, 'allergy_id');
        $myAllergyLevels = array_column($allergies, 'level', 'allergy_id');
      ?>
      <?php foreach ($allergyOptions as $a): ?>
      <div style="border:1px solid var(--border);border-radius:7px;padding:10px">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;text-transform:none;font-weight:400;font-size:13px">
          <input type="checkbox" name="allergy_ids[]" value="<?= $a['allergy_id'] ?>"
                 <?= in_array($a['allergy_id'], $myAllergyIds) ? 'checked' : '' ?>
                 onchange="toggleLevel(this, <?= $a['allergy_id'] ?>)">
          <?= htmlspecialchars($a['name']) ?>
        </label>
        <select name="allergy_levels[]" id="level_<?= $a['allergy_id'] ?>" class="form-control"
                style="margin-top:6px;font-size:11px;padding:4px 8px;<?= !in_array($a['allergy_id'],$myAllergyIds) ? 'display:none' : '' ?>">
          <?php foreach (['Mild','Moderate','Severe','Life-threatening'] as $lv): ?>
            <option value="<?= $lv ?>" <?= ($myAllergyLevels[$a['allergy_id']] ?? '')===$lv ? 'selected':'' ?>><?= $lv ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div style="margin-top:24px;display:flex;gap:10px">
    <button type="submit" class="btn btn-primary">💾 Save Profile</button>
  </div>
</form>

<script>
function toggleLevel(cb, id) {
  const sel = document.getElementById('level_' + id);
  sel.style.display = cb.checked ? 'block' : 'none';
}
</script>
