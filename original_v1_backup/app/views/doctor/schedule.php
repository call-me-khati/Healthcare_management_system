<?php // app/views/doctor/schedule.php
$csrf = csrfToken();
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
$schedMap = [];
foreach ($schedule as $s) { $schedMap[$s['day_of_week']] = $s; }
?>
<?php if ($success = getFlash('success')): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if ($error   = getFlash('error')):   ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<div class="card">
  <div class="card-title">🗓️ Weekly Availability Schedule</div>
  <div class="alert alert-info" style="font-size:12px">
    Appointments are automatically split into <strong>10-minute slots</strong>. Enable a day and set working hours.
  </div>

  <form method="POST" action="<?= BASE_URL ?>/public/doctor/schedule.php">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <div class="table-wrap mb-24">
      <table>
        <thead>
          <tr><th>Day</th><th>Active?</th><th>Start Time</th><th>End Time</th><th>Slots</th></tr>
        </thead>
        <tbody>
          <?php foreach ($days as $day):
            $key   = strtolower($day);
            $set   = $schedMap[$day] ?? null;
            $start = substr($set['start_time'] ?? '09:00:00', 0, 5);
            $end   = substr($set['end_time']   ?? '13:00:00', 0, 5);
            $count = 0;
            if ($set) {
                $diff = (strtotime($set['end_time']) - strtotime($set['start_time'])) / 60;
                if ($diff > 0) $count = (int)($diff / 10);
            }
          ?>
          <tr>
            <td><strong><?= $day ?></strong></td>
            <td>
              <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:400;text-transform:none">
                <input type="checkbox" name="day_<?= $key ?>" value="1"
                       <?= $set ? 'checked' : '' ?>
                       onchange="toggleDay('<?= $key ?>',this.checked)">
                <?= $set ? '✅' : '—' ?>
              </label>
            </td>
            <td>
              <input type="time" name="start_<?= $key ?>" id="start_<?= $key ?>"
                     class="form-control" value="<?= $start ?>"
                     <?= !$set ? 'disabled' : '' ?>
                     onchange="calcSlots('<?= $key ?>')" style="width:130px">
            </td>
            <td>
              <input type="time" name="end_<?= $key ?>" id="end_<?= $key ?>"
                     class="form-control" value="<?= $end ?>"
                     <?= !$set ? 'disabled' : '' ?>
                     onchange="calcSlots('<?= $key ?>')" style="width:130px">
            </td>
            <td><span id="slots_<?= $key ?>" class="pill pill-blue"><?= $count > 0 ? $count.' slots' : '—' ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <button type="submit" class="btn btn-primary">💾 Save Schedule</button>
  </form>
</div>

<?php if (!empty($schedule)): ?>
<div class="card mt-24">
  <div class="card-title">📅 Active Schedule</div>
  <div style="display:flex;flex-wrap:wrap;gap:10px">
    <?php foreach ($schedule as $s):
      $diff = (strtotime($s['end_time']) - strtotime($s['start_time'])) / 60;
      $cnt  = $diff > 0 ? (int)($diff / 10) : 0;
    ?>
    <div style="background:#eff6ff;border:1px solid #93c5fd;border-radius:8px;padding:12px 16px;min-width:155px">
      <div style="font-weight:600;color:#1d4ed8"><?= $s['day_of_week'] ?></div>
      <div style="font-size:13px;margin-top:4px"><?= substr($s['start_time'],0,5) ?> – <?= substr($s['end_time'],0,5) ?></div>
      <div class="text-muted" style="font-size:12px"><?= $cnt ?> × 10-min slots</div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<script>
function toggleDay(k,on){
  document.getElementById('start_'+k).disabled=!on;
  document.getElementById('end_'+k).disabled=!on;
  document.getElementById('slots_'+k).textContent=on?calcSlots(k):'—';
}
function calcSlots(k){
  const s=document.getElementById('start_'+k).value;
  const e=document.getElementById('end_'+k).value;
  const el=document.getElementById('slots_'+k);
  if(!s||!e){el.textContent='—';return;}
  const d=(new Date('1970-01-01T'+e)-new Date('1970-01-01T'+s))/60000;
  el.textContent=d>0?Math.floor(d/10)+' slots':'Invalid range';
}
</script>
