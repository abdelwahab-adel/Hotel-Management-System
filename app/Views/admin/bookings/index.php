<?php $pageTitle = 'Manage Bookings'; ?>
<form method="GET" action="<?= url('/admin/bookings') ?>" class="flex flex-wrap gap-3 mb-6">
  <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search by name, phone, or reference" class="form-input max-w-xs">
  <select name="status" class="form-select max-w-xs">
    <option value="">All statuses</option>
    <?php foreach (['pending','confirmed','paid','checked_in','checked_out','cancelled','rejected'] as $s): ?>
      <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-dark btn-sm">Filter</button>
</form>

<div class="card overflow-x-auto">
  <table class="w-full text-sm">
    <thead><tr class="text-left text-xs text-muted uppercase border-b border-app">
      <th class="p-4">Reference</th><th class="p-4">Guest</th><th class="p-4">Room</th><th class="p-4">Dates</th><th class="p-4">Total</th><th class="p-4">Status</th><th class="p-4">Actions</th>
    </tr></thead>
    <tbody class="divide-app">
      <?php foreach ($bookings as $b): ?>
      <tr>
        <td class="p-4 font-mono text-xs"><?= e($b['booking_ref']) ?></td>
        <td class="p-4"><?= e($b['guest_name']) ?><p class="text-xs text-muted"><?= e($b['guest_phone']) ?></p></td>
        <td class="p-4"><?= e($b['room_type_name']) ?> #<?= e($b['room_number']) ?></td>
        <td class="p-4 text-xs"><?= e($b['check_in']) ?> → <?= e($b['check_out']) ?></td>
        <td class="p-4"><?= money($b['total_amount']) ?></td>
        <td class="p-4"><span class="badge badge-<?= e($b['status']) ?>"><?= e(ucfirst(str_replace('_',' ',$b['status']))) ?></span></td>
        <td class="p-4">
          <div class="flex flex-wrap gap-1.5">
            <?php
              $actions = [];
              if ($b['status'] === 'pending') { $actions = ['confirm' => 'Confirm', 'reject' => 'Reject']; }
              elseif ($b['status'] === 'confirmed') { $actions = ['pay' => 'Mark Paid', 'check_in' => 'Check-in', 'cancel' => 'Cancel']; }
              elseif ($b['status'] === 'paid') { $actions = ['check_in' => 'Check-in', 'cancel' => 'Cancel']; }
              elseif ($b['status'] === 'checked_in') { $actions = ['check_out' => 'Check-out']; }
            ?>
            <?php foreach ($actions as $action => $label): ?>
              <form method="POST" action="<?= url('/admin/bookings/' . $b['booking_ref'] . '/status') ?>">
                <?= $csrf_field ?>
                <input type="hidden" name="action" value="<?= $action ?>">
                <button class="btn btn-outline btn-sm"><?= $label ?></button>
              </form>
            <?php endforeach; ?>
            <a href="<?= url('/admin/bookings/' . $b['booking_ref'] . '/invoice') ?>" target="_blank" class="btn btn-outline btn-sm"><i class="fa-solid fa-file-pdf"></i></a>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($bookings)): ?><tr><td colspan="7" class="p-8 text-center text-muted">No bookings match your filters.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
