<?php $pageTitle = $roomType['name']; $amenities = json_decode((string) $roomType['amenities_json'], true) ?: []; ?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid grid-cols-1 lg:grid-cols-3 gap-10">
  <div class="lg:col-span-2">
    <div class="room-card__image rounded-2xl mb-6" style="aspect-ratio: 16/9;"><i class="fa-solid fa-bed text-7xl"></i></div>

    <?php if (!empty($images)): ?>
      <div class="grid grid-cols-4 gap-3 mb-8">
        <?php foreach ($images as $img): ?>
          <div class="room-card__image rounded-lg" style="aspect-ratio:1/1"><i class="fa-solid fa-image text-2xl"></i></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <p class="hero-eyebrow text-gold mb-2">Room Category</p>
    <h1 class="font-display text-3xl font-bold mb-4"><?= e($roomType['name']) ?></h1>
    <p class="text-muted leading-relaxed mb-6"><?= e($roomType['description']) ?></p>

    <div class="grid grid-cols-3 gap-4 mb-8">
      <div class="card p-4 text-center"><i class="fa-solid fa-user-group text-gold mb-2"></i><p class="text-sm font-semibold"><?= (int) $roomType['max_guests'] ?> Guests</p></div>
      <div class="card p-4 text-center"><i class="fa-solid fa-bed text-gold mb-2"></i><p class="text-sm font-semibold"><?= (int) $roomType['bed_count'] ?> Bed(s)</p></div>
      <div class="card p-4 text-center"><i class="fa-solid fa-expand text-gold mb-2"></i><p class="text-sm font-semibold"><?= (int) ($roomType['size_sqm'] ?? 0) ?> m²</p></div>
    </div>

    <?php if (!empty($amenities)): ?>
      <h3 class="font-display font-semibold mb-3">Amenities</h3>
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-8">
        <?php foreach ($amenities as $a): ?>
          <div class="flex items-center gap-2 text-sm text-muted"><i class="fa-solid fa-check text-gold"></i><?= e($a) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div>
    <div class="card p-6 sticky top-24">
      <p class="text-3xl font-display font-bold text-gold"><?= money($roomType['base_price']) ?> <span class="text-sm text-muted font-normal">/ night</span></p>
      <p class="text-xs text-muted mt-1 mb-6">Taxes calculated at checkout</p>
      <a href="<?= url('/rooms/' . $roomType['slug'] . '/book') ?>" class="btn btn-gold w-full">Book This Room</a>
    </div>
  </div>
</section>
