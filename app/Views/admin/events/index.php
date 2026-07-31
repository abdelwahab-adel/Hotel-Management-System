<?php $pageTitle = 'Manage Events'; ?>
<div class="flex items-center justify-between mb-6">
  <form method="GET" action="<?= url('/admin/events') ?>" class="flex gap-3">
    <select name="status" class="form-select" onchange="this.form.submit()">
      <option value="">All statuses</option>
      <?php foreach (['pending','confirmed','paid','cancelled','rejected'] as $s): ?>
        <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
  </form>
  <button onclick="document.getElementById('new-venue-modal').classList.remove('hidden')" class="btn btn-gold btn-sm"><i class="fa-solid fa-plus"></i> Add Venue Type</button>
</div>

<div class="card overflow-x-auto">
  <table class="w-full text-sm">
    <thead><tr class="text-left text-xs text-muted uppercase border-b border-app">
      <th class="p-4">Reference</th><th class="p-4">Guest</th><th class="p-4">Venue</th><th class="p-4">When</th><th class="p-4">Total</th><th class="p-4">Status</th><th class="p-4">Actions</th>
    </tr></thead>
    <tbody class="divide-app">
      <?php foreach ($bookings as $b): ?>
      <tr>
        <td class="p-4 font-mono text-xs"><?= e($b['booking_ref']) ?></td>
        <td class="p-4"><?= e($b['guest_name']) ?><p class="text-xs text-muted"><?= e($b['guest_phone']) ?></p></td>
        <td class="p-4"><?= e($b['event_type_name']) ?></td>
        <td class="p-4 text-xs"><?= e($b['event_date']) ?>, <?= e($b['start_time']) ?>–<?= e($b['end_time']) ?></td>
        <td class="p-4"><?= money($b['total_amount']) ?></td>
        <td class="p-4"><span class="badge badge-<?= e($b['status']) ?>"><?= e(ucfirst($b['status'])) ?></span></td>
        <td class="p-4">
          <div class="flex gap-1.5">
            <?php
              $actions = [];
              if ($b['status'] === 'pending') { $actions = ['confirm' => 'Confirm', 'reject' => 'Reject']; }
              elseif ($b['status'] === 'confirmed') { $actions = ['pay' => 'Mark Paid', 'cancel' => 'Cancel']; }
            ?>
            <?php foreach ($actions as $action => $label): ?>
              <form method="POST" action="<?= url('/admin/events/' . $b['booking_ref'] . '/status') ?>">
                <?= $csrf_field ?><input type="hidden" name="action" value="<?= $action ?>">
                <button class="btn btn-outline btn-sm"><?= $label ?></button>
              </form>
            <?php endforeach; ?>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($bookings)): ?><tr><td colspan="7" class="p-8 text-center text-muted">No event bookings found.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<div id="new-venue-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
  <div class="bg-surface rounded-2xl p-7 max-w-md w-full">
    <h3 class="font-display font-semibold text-lg mb-4">Add Venue / Event Type</h3>
    <form method="POST" action="<?= url('/admin/event-types') ?>" class="space-y-4">
      <?= $csrf_field ?>
      <div><label class="form-label">Name</label><input type="text" name="name" class="form-input" required></div>
      <div><label class="form-label">Description</label><input type="text" name="description" class="form-input"></div>
      <div><label class="form-label">Base Price</label><input type="number" step="0.01" name="base_price" class="form-input" required></div>
      <div class="flex gap-2">
        <button type="button" onclick="document.getElementById('new-venue-modal').classList.add('hidden')" class="btn btn-outline flex-1">Cancel</button>
        <button type="submit" class="btn btn-gold flex-1">Add</button>
      </div>
    </form>
  </div>
</div>
