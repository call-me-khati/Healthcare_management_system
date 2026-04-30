<?php // app/views/student/notifications.php ?>
<div class="page-header"><h2>🔔 Notifications</h2></div>
<?php if (empty($notifs)): ?>
  <div class="card text-center" style="padding:40px"><p style="font-size:32px">🔕</p><p class="text-muted mt-16">No notifications yet.</p></div>
<?php else: ?>
  <div class="card" style="padding:0">
    <?php foreach ($notifs as $n):
      $icons=['appointment'=>'📅','lab_test'=>'🔬','medicine'=>'💊','followup'=>'🔄','reminder'=>'⏰','system'=>'🔔'];
      $icon=$icons[$n['type']] ?? '🔔';
    ?>
    <div class="notif-item <?= !$n['is_read'] ? 'unread' : '' ?>">
      <div class="notif-icon"><?= $icon ?></div>
      <div class="notif-body" style="flex:1">
        <strong><?= htmlspecialchars($n['title']) ?></strong>
        <p><?= htmlspecialchars($n['message']) ?></p>
        <div class="notif-time"><?= date('d M Y, H:i', strtotime($n['created_at'])) ?></div>
      </div>
      <?php if (!$n['is_read']): ?><span class="pill pill-blue">New</span><?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
