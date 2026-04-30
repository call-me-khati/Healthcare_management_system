<?php // app/views/admin/add_medicine.php
$csrf = csrfToken(); ?>
<?php if ($error = getFlash('error')): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<div class="card" style="max-width:560px">
  <div class="card-title">➕ Add Medicine to Inventory</div>
  <form method="POST" action="<?= BASE_URL ?>/public/admin/add-medicine.php">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <div class="form-row">
      <div class="form-group"><label>Medicine Name *</label><input type="text" name="medicine_name" class="form-control" required placeholder="e.g. Paracetamol 500mg"></div>
      <div class="form-group"><label>Generic Name</label><input type="text" name="generic_name" class="form-control" placeholder="e.g. Acetaminophen"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Category</label><input type="text" name="category" class="form-control" placeholder="e.g. Analgesic, Antibiotic"></div>
      <div class="form-group"><label>Unit</label>
        <select name="unit" class="form-control">
          <option>tablet</option><option>capsule</option><option>syrup</option>
          <option>inhaler</option><option>injection</option><option>cream</option><option>drops</option>
        </select>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group"><label>Quantity *</label><input type="number" name="quantity" class="form-control" min="0" required placeholder="0"></div>
      <div class="form-group"><label>Low Stock Alert At</label><input type="number" name="low_stock_threshold" class="form-control" min="1" value="10"></div>
    </div>
    <div class="form-group"><label>Expiry Date</label><input type="date" name="expiry_date" class="form-control"></div>
    <button type="submit" class="btn btn-primary btn-block">Add to Inventory</button>
    <a href="<?= BASE_URL ?>/public/admin/medicines.php" class="btn btn-secondary btn-block" style="margin-top:8px">← Back</a>
  </form>
</div>
