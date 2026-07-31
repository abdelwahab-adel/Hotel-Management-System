<?php $pageTitle = 'Booking Confirmed'; ?>
<section class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
  <div class="w-16 h-16 rounded-full bg-success/10 text-success flex items-center justify-center mx-auto mb-6 text-2xl">
    <i class="fa-solid fa-check"></i>
  </div>
  <h1 class="font-display text-3xl font-bold mb-3">Booking Received</h1>
  <p class="text-muted mb-8">Reference <span class="font-mono font-semibold text-gold"><?= e($booking['booking_ref']) ?></span> — we've sent a confirmation and will follow up shortly.</p>

  <div class="card p-6 text-left space-y-2 text-sm mb-8">
    <div class="flex justify-between"><span class="text-muted">Guest</span><span><?= e($booking['guest_name']) ?></span></div>
    <div class="flex justify-between"><span class="text-muted">Check-in</span><span><?= e($booking['check_in']) ?></span></div>
    <div class="flex justify-between"><span class="text-muted">Check-out</span><span><?= e($booking['check_out']) ?></span></div>
    <div class="flex justify-between"><span class="text-muted">Status</span><span class="badge badge-<?= e($booking['status']) ?>"><?= e(ucfirst($booking['status'])) ?></span></div>
    <div class="flex justify-between font-semibold pt-2 border-t border-app"><span>Total</span><span class="text-gold"><?= money($booking['total_amount']) ?></span></div>
  </div>

  <a href="<?= url('/') ?>" class="btn btn-dark">Back to Home</a>
</section>
