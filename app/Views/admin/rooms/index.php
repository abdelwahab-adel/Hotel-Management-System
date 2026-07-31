<?php $pageTitle = 'Manage Rooms'; ?>
<div class="flex items-center justify-between mb-6">
  <p class="text-sm text-muted">Manage room categories and individual physical rooms.</p>
  <a href="<?= url('/admin/room-types/create') ?>" class="btn btn-gold btn-sm"><i class="fa-solid fa-plus"></i> Add Room Type</a>
</div>

<div class="space-y-6">
  <?php foreach ($roomTypes as $type): ?>
    <div class="card overflow-hidden">
      <div class="p-5 flex items-center justify-between border-b border-app">
        <div>
          <h3 class="font-display font-semibold"><?= e($type['name']) ?> <span class="text-muted text-sm font-normal">(<?= e($type['slug']) ?>)</span></h3>
          <p class="text-xs text-muted mt-1"><?= money($type['base_price']) ?>/night · <?= (int) $type['max_guests'] ?> guests · <?= count($type['rooms']) ?> rooms</p>
        </div>
        <div class="flex items-center gap-2">
          <span class="badge <?= $type['is_active'] ? 'badge-paid' : 'badge-cancelled' ?>"><?= $type['is_active'] ? 'Active' : 'Inactive' ?></span>
          <form method="POST" action="<?= url('/admin/room-types/' . $type['id'] . '/toggle') ?>">
            <?= $csrf_field ?>
            <button class="btn btn-outline btn-sm"><?= $type['is_active'] ? 'Deactivate' : 'Activate' ?></button>
          </form>
        </div>
      </div>
      <div class="p-5">
        <div class="flex flex-wrap gap-2 mb-4">
          <?php foreach ($type['rooms'] as $room): ?>
            <span class="badge badge-confirmed flex items-center gap-2">
              #<?= e($room['room_number']) ?>
              <form method="POST" action="<?= url('/admin/rooms/' . $room['id'] . '/delete') ?>" onsubmit="return confirm('Remove this room?');">
                <?= $csrf_field ?>
                <button class="text-xs opacity-70 hover:opacity-100"><i class="fa-solid fa-xmark"></i></button>
              </form>
            </span>
          <?php endforeach; ?>
        </div>
        <form method="POST" action="<?= url('/admin/room-types/' . $type['id'] . '/rooms') ?>" class="flex gap-2">
          <?= $csrf_field ?>
          <input type="text" name="room_number" placeholder="Room number e.g. <?= strtoupper($type['slug']) ?>-007" class="form-input max-w-xs" required>
          <button class="btn btn-dark btn-sm">Add Room</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
</div>
