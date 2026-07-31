<?php $pageTitle = $customer['full_name']; ?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
  <div class="card p-6 md:col-span-1">
    <h3 class="font-display font-semibold mb-3"><?= e($customer['full_name']) ?></h3>
    <p class="text-sm text-muted mb-1"><i class="fa-solid fa-envelope mr-2 text-gold"></i><?= e($customer['email']) ?></p>
    <p class="text-sm text-muted mb-1"><i class="fa-solid fa-phone mr-2 text-gold"></i><?= e((string) $customer['phone']) ?></p>
    <p class="text-sm text-muted mb-4"><i class="fa-solid fa-calendar mr-2 text-gold"></i>Joined <?= e(date('M j, Y', strtotime($customer['created_at']))) ?></p>
    <span class="badge <?= $customer['status'] === 'active' ? 'badge-paid' : 'badge-cancelled' ?>"><?= e(ucfirst($customer['status'])) ?></span>
  </div>
  <div class="md:col-span-2 card p-6">
    <h3 class="font-display font-semibold mb-4">Booking History</h3>
    <div class="divide-app">
      <?php foreach ($bookings as $b): ?>
        <div class="py-3 flex justify-between text-sm">
          <span><?= e($b['room_type_name']) ?> · <?= e($b['check_in']) ?> → <?= e($b['check_out']) ?></span>
          <span class="badge badge-<?= e($b['status']) ?>"><?= e(ucfirst(str_replace('_',' ',$b['status']))) ?></span>
        </div>
      <?php endforeach; ?>
      <?php if (empty($bookings)): ?><p class="text-sm text-muted py-3">No room bookings yet.</p><?php endif; ?>
    </div>
  </div>
</div>
