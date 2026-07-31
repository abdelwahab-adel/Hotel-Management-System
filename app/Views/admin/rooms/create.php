<?php $pageTitle = 'Add Room Type'; ?>
<form method="POST" action="<?= url('/admin/room-types') ?>" class="card p-7 space-y-4 max-w-2xl">
  <?= $csrf_field ?>
  <div class="grid grid-cols-2 gap-4">
    <div>
      <label class="form-label">Name</label>
      <input type="text" name="name" class="form-input" required>
    </div>
    <div>
      <label class="form-label">Slug (URL, letters/numbers/dashes)</label>
      <input type="text" name="slug" class="form-input" placeholder="e.g. ocean-suite" required>
    </div>
  </div>
  <div>
    <label class="form-label">Description</label>
    <textarea name="description" rows="3" class="form-textarea"></textarea>
  </div>
  <div class="grid grid-cols-3 gap-4">
    <div><label class="form-label">Base Price / Night</label><input type="number" step="0.01" name="base_price" class="form-input" required></div>
    <div><label class="form-label">Max Guests</label><input type="number" name="max_guests" value="2" class="form-input"></div>
    <div><label class="form-label">Bed Count</label><input type="number" name="bed_count" value="1" class="form-input"></div>
  </div>
  <div class="grid grid-cols-2 gap-4">
    <div><label class="form-label">Size (m²)</label><input type="number" name="size_sqm" class="form-input"></div>
    <div><label class="form-label">Initial Room Count</label><input type="number" name="initial_room_count" value="6" class="form-input"></div>
  </div>
  <div>
    <label class="form-label">Amenities (comma-separated)</label>
    <input type="text" name="amenities" class="form-input" placeholder="Free Wi-Fi, Air Conditioning, City View">
  </div>
  <button type="submit" class="btn btn-gold">Create Room Type</button>
</form>
