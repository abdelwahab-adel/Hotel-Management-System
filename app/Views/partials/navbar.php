<header class="sticky top-0 z-40 bg-primary/95 backdrop-blur border-b border-white/10">
  <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">
      <a href="<?= url('/') ?>" class="flex items-center gap-2 font-display font-bold text-white text-lg">
        <i class="fa-solid fa-hotel text-gold"></i>
        <?= e((string) setting('hotel_name', 'The Pacific Hotel')) ?>
      </a>

      <div class="hidden md:flex items-center gap-7 text-sm font-medium text-slate-200">
        <a href="<?= url('/') ?>" class="hover:text-gold transition">Home</a>
        <a href="<?= url('/rooms') ?>" class="hover:text-gold transition">Rooms</a>
        <a href="<?= url('/gallery') ?>" class="hover:text-gold transition">Gallery</a>
        <a href="<?= url('/events') ?>" class="hover:text-gold transition">Events</a>
        <a href="<?= url('/about') ?>" class="hover:text-gold transition">About</a>
        <a href="<?= url('/contact') ?>" class="hover:text-gold transition">Contact</a>
      </div>

      <div class="flex items-center gap-3">
        <button onclick="toggleTheme()" class="text-slate-300 hover:text-gold transition w-9 h-9 flex items-center justify-center rounded-full hover:bg-white/5" title="Toggle dark mode">
          <i class="fa-solid fa-circle-half-stroke"></i>
        </button>

        <?php if (\App\Core\Auth::check()): ?>
          <?php $unread = \App\Models\Notification::unreadCount(\App\Core\Auth::id()); ?>
          <div class="relative">
            <button id="notif-bell" class="relative text-slate-300 hover:text-gold transition w-9 h-9 flex items-center justify-center rounded-full hover:bg-white/5">
              <i class="fa-solid fa-bell"></i>
              <?php if ($unread > 0): ?><span class="notif-dot absolute top-1 right-1 w-2 h-2 bg-gold rounded-full"></span><?php endif; ?>
            </button>
            <div id="notif-panel" class="hidden absolute right-0 mt-2 w-72 bg-surface text-app border border-app rounded-xl shadow-luxury overflow-hidden">
              <div class="p-3 border-b border-app font-semibold text-sm" style="color: var(--color-text)">Notifications</div>
              <div class="max-h-72 overflow-y-auto divide-app">
                <?php $notifs = \App\Models\Notification::forUser(\App\Core\Auth::id(), 6); ?>
                <?php if (empty($notifs)): ?>
                  <p class="p-4 text-xs text-muted">No notifications yet.</p>
                <?php endif; ?>
                <?php foreach ($notifs as $n): ?>
                  <div class="p-3 text-xs" style="color: var(--color-text)">
                    <p class="font-semibold"><?= e($n['title']) ?></p>
                    <p class="text-muted mt-0.5"><?= e($n['body']) ?></p>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          <a href="<?= url(\App\Core\Auth::hasRole(['super_admin','admin','receptionist']) ? '/admin' : '/dashboard') ?>" class="btn btn-gold btn-sm hidden sm:inline-flex">
            <i class="fa-solid fa-gauge"></i> <?= \App\Core\Auth::hasRole(['super_admin','admin','receptionist']) ? 'Back Office' : 'Dashboard' ?>
          </a>
          <a href="<?= url('/logout') ?>" class="text-slate-300 hover:text-gold transition text-sm hidden sm:inline">Logout</a>
        <?php else: ?>
          <a href="<?= url('/login') ?>" class="text-slate-200 hover:text-gold transition text-sm font-medium hidden sm:inline">Login</a>
          <a href="<?= url('/register') ?>" class="btn btn-gold btn-sm">Book Now</a>
        <?php endif; ?>

        <button id="nav-toggle" class="md:hidden text-white w-9 h-9 flex items-center justify-center">
          <i class="fa-solid fa-bars"></i>
        </button>
      </div>
    </div>

    <div id="mobile-menu" class="hidden md:hidden pb-4 flex flex-col gap-3 text-slate-200 text-sm font-medium">
      <a href="<?= url('/') ?>" class="hover:text-gold">Home</a>
      <a href="<?= url('/rooms') ?>" class="hover:text-gold">Rooms</a>
      <a href="<?= url('/gallery') ?>" class="hover:text-gold">Gallery</a>
      <a href="<?= url('/events') ?>" class="hover:text-gold">Events</a>
      <a href="<?= url('/about') ?>" class="hover:text-gold">About</a>
      <a href="<?= url('/contact') ?>" class="hover:text-gold">Contact</a>
      <?php if (!\App\Core\Auth::check()): ?><a href="<?= url('/login') ?>" class="hover:text-gold">Login</a><?php endif; ?>
    </div>
  </nav>
</header>
