<?php // app/views/student/doctors.php ?>
<div class="page-header">
  <h2>🔍 Find a Doctor</h2>
  <span class="text-muted">Select a doctor to view their schedule and book an appointment</span>
</div>

<?php if (empty($doctors)): ?>
  <div class="card text-center" style="padding:40px">
    <p style="font-size:32px;margin-bottom:12px">👨‍⚕️</p>
    <p class="text-muted">No doctors are currently available.</p>
  </div>
<?php else: ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px">
    <?php foreach ($doctors as $d): ?>
    <div class="card" style="position:relative">
      <div style="font-size:36px;text-align:center;margin-bottom:12px">👨‍⚕️</div>
      <h3 style="text-align:center;font-size:15px;margin-bottom:4px"><?= htmlspecialchars($d['full_name']) ?></h3>
      <p style="text-align:center;color:var(--muted);font-size:12px;margin-bottom:12px"><?= htmlspecialchars($d['specialization'] ?? '—') ?></p>

      <div style="border-top:1px solid var(--border);padding-top:12px">
        <?php if ($d['department']): ?>
          <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:6px">
            <span class="text-muted">Department</span>
            <span><?= htmlspecialchars($d['department']) ?></span>
          </div>
        <?php endif; ?>
        <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:6px">
          <span class="text-muted">Status</span>
          <span class="pill <?= $d['availability_status']==='Available' ? 'pill-green' : 'pill-red' ?>"><?= $d['availability_status'] ?></span>
        </div>
      </div>

      <?php if ($d['bio']): ?>
        <p style="font-size:12px;color:var(--muted);margin-top:8px;line-height:1.5"><?= htmlspecialchars(mb_substr($d['bio'],0,100)) ?>...</p>
      <?php endif; ?>

      <a href="<?= BASE_URL ?>/public/student/book.php?doctor_id=<?= $d['doctor_id'] ?>"
         class="btn btn-primary btn-block mt-16">
        📅 Book Appointment
      </a>
    </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
