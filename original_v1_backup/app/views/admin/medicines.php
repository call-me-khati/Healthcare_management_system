<?php // app/views/admin/medicines.php ?>

<?php if ($success = getFlash('success')): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if ($error = getFlash('error')): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<?php if (count($lowStock)): ?>
  <div class="alert alert-warning">⚠️ <?= count($lowStock) ?> medicine(s) are low on stock and need restocking.</div>
<?php endif; ?>
<?php if (count($expiring)): ?>
  <div class="alert alert-danger">🚨 <?= count($expiring) ?> medicine(s) expiring within 30 days.</div>
<?php endif; ?>

<div class="page-header">
  <h2>💊 Medicine Inventory</h2>
  <a href="<?= BASE_URL ?>/public/admin/add-medicine.php" class="btn btn-primary">+ Add Medicine</a>
</div>

<!-- Legend -->
<div style="display:flex;gap:16px;margin-bottom:16px;font-size:12px">
  <span style="display:flex;align-items:center;gap:6px"><span style="width:12px;height:12px;background:#fff7ed;border-radius:2px;border:1px solid #fde68a;display:inline-block"></span> Low Stock</span>
  <span style="display:flex;align-items:center;gap:6px"><span style="width:12px;height:12px;background:#fef2f2;border-radius:2px;border:1px solid #fecaca;display:inline-block"></span> Expiring Soon</span>
</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>#</th><th>Medicine</th><th>Generic Name</th><th>Category</th>
        <th>Qty</th><th>Unit</th><th>Expiry</th><th>Status</th><th>Actions</th>
      </tr>
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
        <td><?= htmlspecialchars($m['category'] ?? '—') ?></td>
        <td>
          <strong><?= $m['quantity'] ?></strong>
          <?php if ($m['is_low_stock']): ?>
            <span class="pill pill-red" style="margin-left:4px">Low</span>
          <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($m['unit']) ?></td>
        <td>
          <?php if ($m['expiry_date']): ?>
            <?= $m['expiry_date'] ?>
            <?php if ($m['expiring_soon']): ?>
              <span class="pill pill-red">Soon</span>
            <?php endif; ?>
          <?php else: ?>—<?php endif; ?>
        </td>
        <td>
          <?php if ($m['quantity'] === 0): ?>
            <span class="pill pill-red">Out of Stock</span>
          <?php elseif ($m['is_low_stock']): ?>
            <span class="pill pill-orange">Low Stock</span>
          <?php else: ?>
            <span class="pill pill-green">In Stock</span>
          <?php endif; ?>
        </td>
        <td>
          <button class="btn btn-sm btn-secondary" onclick="openEditModal(<?= htmlspecialchars(json_encode($m)) ?>)">Edit</button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center">
  <div class="card" style="width:480px;max-height:90vh;overflow-y:auto">
    <div class="card-title">Edit Medicine</div>
    <form method="POST" action="<?= BASE_URL ?>/public/admin/edit-medicine.php">
      <?php $csrf = csrfToken(); ?>
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <input type="hidden" name="medicine_id" id="edit_id">
      <div class="form-row">
        <div class="form-group"><label>Medicine Name</label><input type="text" name="medicine_name" id="edit_name" class="form-control" required></div>
        <div class="form-group"><label>Generic Name</label><input type="text" name="generic_name" id="edit_generic" class="form-control"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Category</label><input type="text" name="category" id="edit_cat" class="form-control"></div>
        <div class="form-group"><label>Unit</label>
          <select name="unit" id="edit_unit" class="form-control">
            <option>tablet</option><option>capsule</option><option>inhaler</option>
            <option>syrup</option><option>injection</option><option>cream</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Quantity</label><input type="number" name="quantity" id="edit_qty" class="form-control" min="0"></div>
        <div class="form-group"><label>Low Stock Threshold</label><input type="number" name="low_stock_threshold" id="edit_thresh" class="form-control" min="1"></div>
      </div>
      <div class="form-group"><label>Expiry Date</label><input type="date" name="expiry_date" id="edit_expiry" class="form-control"></div>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditModal(med) {
  document.getElementById('edit_id').value      = med.medicine_id;
  document.getElementById('edit_name').value    = med.medicine_name;
  document.getElementById('edit_generic').value = med.generic_name || '';
  document.getElementById('edit_cat').value     = med.category || '';
  document.getElementById('edit_unit').value    = med.unit;
  document.getElementById('edit_qty').value     = med.quantity;
  document.getElementById('edit_thresh').value  = med.low_stock_threshold;
  document.getElementById('edit_expiry').value  = med.expiry_date || '';
  document.getElementById('editModal').style.display = 'flex';
}
function closeEditModal() {
  document.getElementById('editModal').style.display = 'none';
}
</script>
