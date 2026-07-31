<?php $pageTitle = 'Gallery'; ?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
  <p class="hero-eyebrow text-gold mb-3">A Glimpse Inside</p>
  <h1 class="font-display text-4xl font-bold mb-10">Gallery</h1>

  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <?php
    $icons = ['fa-bed', 'fa-utensils', 'fa-water-ladder', 'fa-champagne-glasses', 'fa-spa', 'fa-tree-city', 'fa-martini-glass-citrus', 'fa-hot-tub-person'];
    foreach ($icons as $i => $icon):
    ?>
      <div class="room-card__image rounded-xl <?= $i % 3 === 0 ? 'sm:col-span-2 sm:row-span-2' : '' ?>" style="aspect-ratio: <?= $i % 3 === 0 ? '1/1' : '4/3' ?>;">
        <i class="fa-solid <?= $icon ?> text-4xl"></i>
      </div>
    <?php endforeach; ?>
  </div>
  <p class="text-xs text-muted mt-6">Photography placeholders shown — replace with your property's real photos in <code>public/uploads</code>.</p>
</section>
