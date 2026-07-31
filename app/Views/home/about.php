<?php $pageTitle = 'About Us'; ?>
<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
  <p class="hero-eyebrow text-gold mb-3">Our Story</p>
  <h1 class="font-display text-4xl font-bold mb-6">About <?= e((string) setting('hotel_name', 'The Pacific Hotel')) ?></h1>
  <p class="text-muted leading-relaxed mb-4">
    For over two decades, <?= e((string) setting('hotel_name', 'The Pacific Hotel')) ?> has welcomed travelers seeking
    a refined, unhurried stay along the coast. Every room is designed around comfort and quiet luxury, and every
    member of our team is trained to anticipate what you need before you ask.
  </p>
  <p class="text-muted leading-relaxed mb-10">
    Whether you're here for a weekend escape, a business trip, or to host the event of a lifetime in our Grand
    Ballroom, we treat every stay as a chance to exceed expectations.
  </p>

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
    <div class="card p-6">
      <i class="fa-solid fa-award text-gold text-2xl mb-3"></i>
      <h3 class="font-display font-semibold mb-1">Award-winning Service</h3>
      <p class="text-sm text-muted">Recognized for hospitality excellence year after year.</p>
    </div>
    <div class="card p-6">
      <i class="fa-solid fa-leaf text-gold text-2xl mb-3"></i>
      <h3 class="font-display font-semibold mb-1">Sustainable Practices</h3>
      <p class="text-sm text-muted">Energy-conscious operations across every property.</p>
    </div>
    <div class="card p-6">
      <i class="fa-solid fa-heart text-gold text-2xl mb-3"></i>
      <h3 class="font-display font-semibold mb-1">Guest-first Culture</h3>
      <p class="text-sm text-muted">A team that remembers your preferences, every visit.</p>
    </div>
  </div>
</section>
