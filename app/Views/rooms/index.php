<?php $pageTitle = 'Rooms & Suites'; ?>
<section class="bg-secondary text-white py-16">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <p class="hero-eyebrow text-gold mb-3">Accommodations</p>
    <h1 class="font-display text-4xl font-bold">Rooms &amp; Suites</h1>
    <p class="text-slate-400 mt-3 max-w-xl">From cozy studios to the Presidential Suite — a category for every kind of stay.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-7">
    <?php foreach ($roomTypes as $room): ?>
      <div class="room-card">
        <div class="room-card__image relative">
          <i class="fa-solid fa-bed text-5xl"></i>
          <span class="absolute top-3 left-3 badge <?= $room['available_now'] > 0 ? 'badge-paid' : 'badge-cancelled' ?>">
            <?= $room['available_now'] > 0 ? $room['available_now'] . ' available today' : 'Fully booked today' ?>
          </span>
        </div>
        <div class="p-5">
          <div class="flex items-center justify-between mb-2">
            <h3 class="font-display font-semibold text-lg"><?= e($room['name']) ?></h3>
            <span class="text-gold font-bold"><?= money($room['base_price']) ?><span class="text-xs text-muted font-normal">/night</span></span>
          </div>
          <p class="text-sm text-muted line-clamp-2 mb-4"><?= e(mb_strimwidth((string) $room['description'], 0, 100, '…')) ?></p>
          <div class="flex items-center gap-4 text-xs text-muted mb-4">
            <span><i class="fa-solid fa-user-group mr-1"></i><?= (int) $room['max_guests'] ?> guests</span>
            <span><i class="fa-solid fa-bed mr-1"></i><?= (int) $room['bed_count'] ?> bed</span>
            <?php if ($room['size_sqm']): ?><span><i class="fa-solid fa-expand mr-1"></i><?= (int) $room['size_sqm'] ?> m²</span><?php endif; ?>
          </div>
          <a href="<?= url('/rooms/' . $room['slug']) ?>" class="btn btn-dark w-full btn-sm">View Details</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
