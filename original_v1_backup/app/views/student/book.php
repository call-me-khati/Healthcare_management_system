<?php // app/views/student/book.php
$csrf = csrfToken();
?>
<?php if ($error = getFlash('error')): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<?php if ($followupId): ?>
  <div class="alert alert-info">🔄 You are booking a <strong>follow-up appointment</strong>. Please select a convenient slot.</div>
<?php endif; ?>

<div class="grid-2">
  <!-- Doctor card -->
  <div class="card">
    <div class="card-title">👨‍⚕️ <?= htmlspecialchars($doctor['full_name']) ?></div>
    <table style="width:100%;font-size:13px">
      <tr><td class="text-muted" style="padding:5px 0;width:40%">Specialization</td><td><?= htmlspecialchars($doctor['specialization'] ?? '—') ?></td></tr>
      <tr><td class="text-muted" style="padding:5px 0">Department</td><td><?= htmlspecialchars($doctor['department'] ?? '—') ?></td></tr>
      <tr><td class="text-muted" style="padding:5px 0">Status</td><td>
        <span class="pill <?= $doctor['availability_status']==='Available' ? 'pill-green' : 'pill-red' ?>"><?= $doctor['availability_status'] ?></span>
      </td></tr>
    </table>

    <!-- Weekly schedule -->
    <?php if (!empty($schedule)): ?>
    <div style="margin-top:14px">
      <span class="section-title">Available Days</span>
      <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px">
        <?php foreach ($schedule as $s): ?>
          <span class="pill pill-blue"><?= $s['day_of_week'] ?> <?= substr($s['start_time'],0,5) ?>–<?= substr($s['end_time'],0,5) ?></span>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Date + slot selection -->
  <div class="card">
    <div class="card-title">📅 Select Date & Time</div>

    <form method="GET" style="display:flex;gap:10px;align-items:center;margin-bottom:20px">
      <input type="hidden" name="doctor_id" value="<?= $doctor['doctor_id'] ?>">
      <?php if ($followupId): ?><input type="hidden" name="followup_id" value="<?= $followupId ?>"><?php endif; ?>
      <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($date) ?>"
             min="<?= date('Y-m-d') ?>">
      <button type="submit" class="btn btn-primary btn-sm">Check Slots</button>
    </form>

    <!-- Slot legend -->
    <div style="display:flex;gap:16px;margin-bottom:12px;font-size:12px">
      <span><span class="slot-btn" style="cursor:default;padding:4px 8px">09:00</span> Available</span>
      <span><span class="slot-btn booked" style="padding:4px 8px">10:00</span> Booked</span>
    </div>

    <!-- Slots -->
    <?php if (empty($slots)): ?>
      <div class="alert alert-warning">No availability for this date. Please select another date or day.</div>
    <?php else: ?>
      <div class="slot-grid" id="slotGrid">
        <?php foreach ($slots as $s): ?>
          <button type="button"
                  class="slot-btn <?= !$s['available'] ? 'booked' : '' ?>"
                  data-time="<?= $s['time'] ?>"
                  <?= !$s['available'] ? 'disabled' : '' ?>
                  onclick="selectSlot(this)">
            <?= $s['available'] ? '✅' : '❌' ?> <?= $s['time'] ?>
          </button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Booking form (shown after slot selection) -->
<div class="card mt-24" id="bookingForm" style="display:<?= empty($slots) ? 'none' : 'block' ?>">
  <div class="card-title">✍️ Confirm Appointment</div>
  <form method="POST" action="<?= BASE_URL ?>/public/student/book.php">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <input type="hidden" name="doctor_id" value="<?= $doctor['doctor_id'] ?>">
    <input type="hidden" name="appointment_date" value="<?= htmlspecialchars($date) ?>">
    <input type="hidden" name="appointment_time" id="selectedTime" value="">
    <?php if ($followupId): ?><input type="hidden" name="followup_id" value="<?= $followupId ?>"><?php endif; ?>

    <div class="form-row">
      <div class="form-group">
        <label>Selected Time</label>
        <div id="displayTime" style="padding:9px 12px;background:#eff6ff;border:1.5px solid #93c5fd;border-radius:7px;font-weight:600;color:#1d4ed8;font-size:14px">
          — Select a slot above —
        </div>
      </div>
      <div class="form-group">
        <label>Priority</label>
        <select name="priority" class="form-control">
          <option value="Normal">Normal</option>
          <option value="Urgent">Urgent</option>
          <option value="Emergency">Emergency 🚨</option>
        </select>
        <p class="form-hint">Select Urgent/Emergency only if medically necessary.</p>
      </div>
    </div>

    <div class="form-group">
      <label>Reason for Visit</label>
      <textarea name="reason" class="form-control" rows="3" placeholder="Briefly describe your symptoms or reason..."></textarea>
    </div>

    <div style="display:flex;gap:10px;align-items:center">
      <a href="<?= BASE_URL ?>/public/student/doctors.php" class="btn btn-secondary">← Back</a>
      <button type="submit" id="submitBtn" class="btn btn-primary" disabled>Confirm Booking</button>
    </div>
  </form>
</div>

<script>
function selectSlot(btn) {
  document.querySelectorAll('.slot-btn.selected').forEach(b => b.classList.remove('selected'));
  btn.classList.add('selected');
  const time = btn.dataset.time;
  document.getElementById('selectedTime').value = time;
  document.getElementById('displayTime').textContent  = '📅 ' + time + ' on <?= $date ?>';
  document.getElementById('submitBtn').disabled = false;
  document.getElementById('bookingForm').style.display = 'block';
  document.getElementById('bookingForm').scrollIntoView({behavior:'smooth'});
}
</script>
