<?php // app/views/nurse/medicines.php ?>
<?php if ($success = getFlash('success')): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if ($error = getFlash('error')): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<div class="page-header"><h2>💊 Medicine Inventory</h2></div>

<?php if (!empty(array_filter($meds, fn($m) => $m['is_low_stock']))): ?>
  <div class="alert alert-warning">⚠️ Some medicines are low on stock. Please notify admin for restocking.</div>
<?php endif; ?>

<div class="table-wrap">
  <table>
    <thead>
      <tr><th>#</th><th>Medicine</th><th>Generic Name</th><th>Qty</th><th>Unit</th><th>Expiry</th><th>Status</th></tr>
    </thead>
    <tbody>
      <?php foreach ($meds as $i => $m):
        $lowClass = $m['is_low_stock'] ? 'low-stock' : '';
        $expClass = $m['expiring_soon'] ? 'expiring' : '';
        $rowClass = $expClass ?: $lowClass;
      ?>
      <tr class="<?= $rowClass ?>">
        <td><?= $i+1 ?></td>
        <td><strong><?= htmlspecialchars($m['medicine_name']) ?></strong></td>
        <td class="text-muted"><?= htmlspecialchars($m['generic_name'] ?? '—') ?></td>
        <td>
          <strong><?= $m['quantity'] ?></strong>
          <?php if ($m['is_low_stock']): ?><span class="pill pill-red" style="margin-left:4px">Low</span><?php endif; ?>
        </td>
        <td><?= htmlspecialchars($m['unit']) ?></td>
        <td>
          <?= $m['expiry_date'] ?? '—' ?>
          <?php if ($m['expiring_soon']): ?><span class="pill pill-red">Soon</span><?php endif; ?>
        </td>
        <td>
          <?php if ($m['quantity'] === 0): ?>
            <span class="pill pill-red">Out of Stock</span>
          <?php elseif ($m['is_low_stock']): ?>
            <span class="pill pill-orange">Low Stock</span>
          <?php else: ?>
            <span class="pill pill-green">Available</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
