<?php // app/views/doctor/consult.php ?>

<?php if ($success = getFlash('success')): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if ($error = getFlash('error')): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<!-- Patient summary -->
<div class="card mb-24">
  <div class="card-title">👤 Patient Information</div>
  <div class="form-row-3">
    <div><span class="section-title">Name</span><strong><?= htmlspecialchars($student['full_name']) ?></strong></div>
    <div><span class="section-title">UID</span><?= htmlspecialchars($student['student_uid'] ?? '—') ?></div>
    <div><span class="section-title">Blood Group</span><span class="pill pill-red"><?= htmlspecialchars($student['blood_group'] ?? 'Unknown') ?></span></div>
    <div><span class="section-title">Priority</span>
      <?php $prCls = $appt['priority'] === 'Emergency' ? 'pill-red' : ($appt['priority'] === 'Urgent' ? 'pill-orange' : 'pill-blue'); ?>
      <span class="pill <?= $prCls ?>"><?= $appt['priority'] ?></span>
    </div>
    <div><span class="section-title">Phone</span><?= htmlspecialchars($student['contact_number'] ?? '—') ?></div>
    <div><span class="section-title">Appointment</span><?= $appt['appointment_date'] ?> @ <?= substr($appt['appointment_time'], 0, 5) ?></div>
  </div>

  <?php if (!empty($allergies)): ?>
  <div style="margin-top:14px">
    <span class="section-title">⚠️ Known Allergies</span>
    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:6px">
      <?php foreach ($allergies as $al):
        $alCls = $al['level'] === 'Life-threatening' ? 'pill-red' : ($al['level'] === 'Severe' ? 'pill-orange' : 'pill-yellow');
      ?>
        <span class="pill <?= $alCls ?>"><?= htmlspecialchars($al['name']) ?> (<?= $al['level'] ?>)</span>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if (!empty($student['medical_history'])): ?>
  <div style="margin-top:14px">
    <span class="section-title">Medical History</span>
    <p style="font-size:13px;margin-top:4px"><?= nl2br(htmlspecialchars($student['medical_history'])) ?></p>
  </div>
  <?php endif; ?>

  <div style="margin-top:12px">
    <a href="<?= BASE_URL ?>/public/doctor/patient-record.php?student_id=<?= $student['student_id'] ?>" class="btn btn-sm btn-outline">📋 Full Medical History</a>
  </div>
</div>

<!-- Consultation form -->
<form method="POST" action="<?= BASE_URL ?>/public/doctor/consult.php" id="consultForm">
  <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
  <input type="hidden" name="appointment_id" value="<?= $appt['appointment_id'] ?>">

  <div class="card mb-24">
    <div class="card-title">🩺 Consultation Notes</div>
    <div class="form-group">
      <label>Diagnosis</label>
      <input type="text" name="diagnosis" class="form-control"
             value="<?= htmlspecialchars($existing['diagnosis'] ?? '') ?>"
             placeholder="Primary diagnosis...">
    </div>
    <div class="form-group">
      <label>Consultation Notes</label>
      <textarea name="consultation_notes" class="form-control" rows="4"
                placeholder="Observations, examination findings..."><?= htmlspecialchars($existing['consultation_notes'] ?? '') ?></textarea>
    </div>
  </div>

  <!-- Prescriptions -->
  <div class="card mb-24">
    <div class="card-title">💊 Prescriptions</div>
    <div id="rx-rows">
      <div class="rx-row" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr auto;gap:8px;margin-bottom:10px;align-items:end">
        <div class="form-group mb-0"><label>Medicine</label>
          <select name="medicine_id[]" class="form-control">
            <option value="">— Select —</option>
            <?php foreach ($medicines as $med): ?>
              <option value="<?= $med['medicine_id'] ?>"><?= htmlspecialchars($med['medicine_name']) ?> (<?= $med['quantity'] ?> left)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group mb-0"><label>Amount</label><input type="number" name="dosage_amount[]" class="form-control" step="0.5" placeholder="1"></div>
        <div class="form-group mb-0"><label>Unit</label>
          <select name="dosage_unit[]" class="form-control">
            <option>tablet</option><option>capsule</option><option>ml</option><option>mg</option><option>puff</option>
          </select>
        </div>
        <div class="form-group mb-0"><label>Frequency</label>
          <select name="frequency[]" class="form-control">
            <option>Once daily</option><option>Twice daily</option><option>Three times daily</option>
            <option>Every 8 hours</option><option>Every 12 hours</option><option>As needed</option>
          </select>
        </div>
        <div class="form-group mb-0"><label>Duration</label>
          <select name="duration[]" class="form-control">
            <option>3 days</option><option>5 days</option><option>7 days</option>
            <option>10 days</option><option>14 days</option><option>1 month</option><option>Ongoing</option>
          </select>
        </div>
        <div class="form-group mb-0"><label>Instruction</label><input type="text" name="instruction[]" class="form-control" placeholder="After meals..."></div>
        <div><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.rx-row').remove()">✕</button></div>
      </div>
    </div>
    <button type="button" class="btn btn-sm btn-outline" onclick="addRxRow()">+ Add Medicine</button>
  </div>

  <!-- Lab Tests -->
  <div class="card mb-24">
    <div class="card-title">🔬 Request Lab Tests</div>
    <div id="lab-rows">
      <div class="lab-row" style="display:flex;gap:8px;margin-bottom:8px;align-items:center">
        <input type="text" name="test_name[]" class="form-control" placeholder="e.g. CBC, Blood Sugar, Urine Routine...">
        <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.lab-row').remove()">✕</button>
      </div>
    </div>
    <button type="button" class="btn btn-sm btn-outline" onclick="addLabRow()">+ Add Test</button>
    <p class="form-hint">Patient will be notified and must click "Test Done" after completing the test.</p>
  </div>

  <!-- Follow-up -->
  <div class="card mb-24">
    <div class="card-title">🔄 Follow-Up</div>
    <div class="form-row">
      <div class="form-group">
        <label>Follow-up Date</label>
        <input type="date" name="follow_up_date" class="form-control"
               value="<?= htmlspecialchars($existing['follow_up_date'] ?? '') ?>"
               min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
        <p class="form-hint">Patient will be notified and reminded to book an appointment.</p>
      </div>
      <div class="form-group">
        <label>Follow-up Notes</label>
        <textarea name="follow_up_notes" class="form-control" rows="3" placeholder="What to check at follow-up..."><?= htmlspecialchars($existing['follow_up_notes'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <div style="display:flex;gap:10px;justify-content:flex-end">
    <a href="<?= BASE_URL ?>/public/doctor/appointments.php" class="btn btn-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">💾 Save Consultation & Complete</button>
  </div>
</form>

<?php if (!empty($history)): ?>
<div class="card mt-24">
  <div class="card-title">📋 Previous Consultations</div>
  <div class="timeline">
    <?php foreach ($history as $h): ?>
    <div class="tl-item">
      <div class="tl-date"><?= $h['consultation_date'] ?></div>
      <div class="tl-card">
        <?php if ($h['diagnosis']): ?><strong><?= htmlspecialchars($h['diagnosis']) ?></strong><br><?php endif; ?>
        <p style="font-size:12px;color:#64748b;margin-top:4px"><?= htmlspecialchars(mb_substr($h['consultation_notes'] ?? '', 0, 150)) ?></p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<script>
function addRxRow() {
  const row = document.querySelector('.rx-row').cloneNode(true);
  row.querySelectorAll('input,select,textarea').forEach(el => { if(el.type!=='button') el.value=''; });
  document.getElementById('rx-rows').appendChild(row);
}
function addLabRow() {
  const row = document.querySelector('.lab-row').cloneNode(true);
  row.querySelector('input').value = '';
  document.getElementById('lab-rows').appendChild(row);
}
</script>
