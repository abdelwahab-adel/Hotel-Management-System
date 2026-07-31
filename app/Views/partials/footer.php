<footer class="bg-primary text-slate-300 mt-24">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid grid-cols-1 md:grid-cols-4 gap-10">
    <div>
      <div class="flex items-center gap-2 font-display font-bold text-white text-lg mb-3">
        <i class="fa-solid fa-hotel text-gold"></i> <?= e((string) setting('hotel_name', 'The Pacific Hotel')) ?>
      </div>
      <p class="text-sm text-slate-400 leading-relaxed"><?= e((string) setting('contact_address', '')) ?></p>
    </div>
    <div>
      <h4 class="font-display font-semibold text-white mb-3 text-sm tracking-wide uppercase">Explore</h4>
      <ul class="space-y-2 text-sm text-slate-400">
        <li><a href="<?= url('/rooms') ?>" class="hover:text-gold">Rooms &amp; Suites</a></li>
        <li><a href="<?= url('/events') ?>" class="hover:text-gold">Events &amp; Venues</a></li>
        <li><a href="<?= url('/gallery') ?>" class="hover:text-gold">Gallery</a></li>
        <li><a href="<?= url('/about') ?>" class="hover:text-gold">About Us</a></li>
      </ul>
    </div>
    <div>
      <h4 class="font-display font-semibold text-white mb-3 text-sm tracking-wide uppercase">Guest</h4>
      <ul class="space-y-2 text-sm text-slate-400">
        <li><a href="<?= url('/login') ?>" class="hover:text-gold">Sign In</a></li>
        <li><a href="<?= url('/register') ?>" class="hover:text-gold">Create Account</a></li>
        <li><a href="<?= url('/contact') ?>" class="hover:text-gold">Contact Support</a></li>
      </ul>
    </div>
    <div>
      <h4 class="font-display font-semibold text-white mb-3 text-sm tracking-wide uppercase">Contact</h4>
      <ul class="space-y-2 text-sm text-slate-400">
        <li><i class="fa-solid fa-phone text-gold mr-2"></i><?= e((string) setting('contact_phone', '')) ?></li>
        <li><i class="fa-solid fa-envelope text-gold mr-2"></i><?= e((string) setting('contact_notify_email', '')) ?></li>
      </ul>
      <div class="flex gap-3 mt-4 text-slate-400">
        <a href="#" class="hover:text-gold"><i class="fa-brands fa-instagram"></i></a>
        <a href="#" class="hover:text-gold"><i class="fa-brands fa-facebook"></i></a>
        <a href="#" class="hover:text-gold"><i class="fa-brands fa-x-twitter"></i></a>
      </div>
    </div>
  </div>
  <div class="border-t border-white/10 py-5 text-center text-xs text-slate-500">
    &copy; <?= date('Y') ?> <?= e((string) setting('hotel_name', 'The Pacific Hotel')) ?>. All rights reserved.
  </div>
</footer>
