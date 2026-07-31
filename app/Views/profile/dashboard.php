<?php $pageTitle = 'My Dashboard'; ?>
<section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
  <h1 class="font-display text-2xl font-bold mb-1">Welcome back, <?= e(explode(' ', $user['full_name'])[0]) ?></h1>
  <p class="text-muted mb-8">Here's an overview of your account.</p>

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-10">
    <div class="card p-5"><p class="text-xs text-muted uppercase tracking-wide mb-1">Total Reservations</p><p class="text-2xl font-display font-bold"><?= (int) $totalBookings ?></p></div>
    <div class="card p-5"><p class="text-xs text-muted uppercase tracking-wide mb-1">Account Status</p><p class="text-2xl font-display font-bold capitalize"><?= e($user['status']) ?></p></div>
    <div class="card p-5"><p class="text-xs text-muted uppercase tracking-wide mb-1">Member Since</p><p class="text-2xl font-display font-bold"><?= e(date('M Y', strtotime($user['created_at']))) ?></p></div>
  </div>

  <div class="flex items-center justify-between mb-4">
    <h2 class="font-display font-semibold text-lg">Recent Room Bookings</h2>
    <a href="<?= url('/dashboard/bookings') ?>" class="text-sm text-gold hover:underline">View all</a>
  </div>
  <div class="card divide-app overflow-hidden mb-10">
    <?php if (empty($recentBookings)): ?>
      <p class="p-6 text-sm text-muted">No bookings yet. <a href="<?= url('/rooms') ?>" class="text-gold hover:underline">Browse rooms</a>.</p>
    <?php endif; ?>
    <?php foreach ($recentBookings as $b): ?>
      <div class="p-4 flex items-center justify-between text-sm">
        <div>
          <p class="font-semibold"><?= e($b['room_type_name']) ?> <span class="text-muted font-normal">#<?= e($b['room_number']) ?></span></p>
          <p class="text-muted"><?= e($b['check_in']) ?> → <?= e($b['check_out']) ?> · <?= e($b['booking_ref']) ?></p>
        </div>
        <span class="badge badge-<?= e($b['status']) ?>"><?= e(ucfirst(str_replace('_',' ',$b['status']))) ?></span>
      </div>
    <?php endforeach; ?>
  </div>
</section>
