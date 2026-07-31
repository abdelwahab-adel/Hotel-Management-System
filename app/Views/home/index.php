<?php $pageTitle = 'Welcome'; ?>

<section class="hero">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-white">
    <p class="hero-eyebrow mb-4 fade-in">A modern luxury escape</p>
    <h1 class="font-display text-4xl sm:text-6xl font-bold max-w-2xl leading-tight fade-in" style="animation-delay:.05s">
      Where the coastline meets refined comfort
    </h1>
    <p class="mt-6 max-w-xl text-slate-300 text-lg fade-in" style="animation-delay:.1s">
      Discover <?= e((string) setting('hotel_name', 'The Pacific Hotel')) ?> — handcrafted rooms, oceanfront dining,
      and service that remembers your name.
    </p>
    <div class="mt-9 flex flex-wrap gap-4 fade-in" style="animation-delay:.15s">
      <a href="<?= url('/rooms') ?>" class="btn btn-gold">Explore Rooms <i class="fa-solid fa-arrow-right"></i></a>
      <a href="<?= url('/events') ?>" class="btn btn-outline text-white border-white/30 hover:border-gold">Plan an Event</a>
    </div>

    <div class="mt-16 grid grid-cols-2 sm:grid-cols-4 gap-6 max-w-3xl fade-in" style="animation-delay:.2s">
      <div><p class="text-3xl font-display font-bold text-gold">9</p><p class="text-xs text-slate-400 uppercase tracking-wide mt-1">Room Categories</p></div>
      <div><p class="text-3xl font-display font-bold text-gold">24/7</p><p class="text-xs text-slate-400 uppercase tracking-wide mt-1">Concierge</p></div>
      <div><p class="text-3xl font-display font-bold text-gold">3</p><p class="text-xs text-slate-400 uppercase tracking-wide mt-1">Event Venues</p></div>
      <div><p class="text-3xl font-display font-bold text-gold">4.8<span class="text-lg">/5</span></p><p class="text-xs text-slate-400 uppercase tracking-wide mt-1">Guest Rating</p></div>
    </div>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
  <div class="flex items-end justify-between mb-10">
    <div>
      <p class="hero-eyebrow text-gold mb-2" style="letter-spacing:.2em">Featured Stays</p>
      <h2 class="font-display text-3xl font-bold">Rooms &amp; Suites guests love</h2>
    </div>
    <a href="<?= url('/rooms') ?>" class="hidden sm:inline-flex btn btn-outline">View all rooms</a>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-7">
    <?php foreach ($featuredRooms as $room): ?>
      <div class="room-card">
        <div class="room-card__image"><i class="fa-solid fa-bed text-5xl"></i></div>
        <div class="p-5">
          <div class="flex items-center justify-between mb-2">
            <h3 class="font-display font-semibold text-lg"><?= e($room['name']) ?></h3>
            <span class="text-gold font-bold"><?= money($room['base_price']) ?><span class="text-xs text-muted font-normal">/night</span></span>
          </div>
          <p class="text-sm text-muted line-clamp-2 mb-4"><?= e(mb_strimwidth((string) $room['description'], 0, 90, '…')) ?></p>
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

<section class="bg-secondary text-white py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-10">
    <div>
      <i class="fa-solid fa-concierge-bell text-gold text-2xl mb-4"></i>
      <h3 class="font-display font-semibold text-lg mb-2">Attentive Concierge</h3>
      <p class="text-sm text-slate-400">From airport transfers to dinner reservations, our team handles the details.</p>
    </div>
    <div>
      <i class="fa-solid fa-shield-halved text-gold text-2xl mb-4"></i>
      <h3 class="font-display font-semibold text-lg mb-2">Secure Booking</h3>
      <p class="text-sm text-slate-400">Encrypted, verified reservations with instant confirmation and clear pricing.</p>
    </div>
    <div>
      <i class="fa-solid fa-champagne-glasses text-gold text-2xl mb-4"></i>
      <h3 class="font-display font-semibold text-lg mb-2">Signature Events</h3>
      <p class="text-sm text-slate-400">Ballrooms, terraces, and conference spaces ready for your next occasion.</p>
    </div>
  </div>
</section>
