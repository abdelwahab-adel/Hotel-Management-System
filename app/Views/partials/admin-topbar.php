<header class="bg-surface border-b border-app px-4 sm:px-8 py-4 flex items-center justify-between">
  <div class="flex items-center gap-3">
    <button id="nav-toggle" class="lg:hidden w-9 h-9 flex items-center justify-center"><i class="fa-solid fa-bars"></i></button>
    <h1 class="font-display font-semibold text-lg"><?= e($pageTitle ?? 'Dashboard') ?></h1>
  </div>
  <div class="flex items-center gap-4">
    <button onclick="toggleTheme()" class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-black/5" title="Toggle dark mode"><i class="fa-solid fa-circle-half-stroke"></i></button>
    <a href="<?= url('/') ?>" class="text-sm text-muted hover:text-gold hidden sm:inline"><i class="fa-solid fa-arrow-up-right-from-square mr-1"></i>View Site</a>
    <a href="<?= url('/logout') ?>" class="btn btn-outline btn-sm">Logout</a>
  </div>
</header>
<div id="mobile-menu" class="hidden lg:hidden bg-primary text-white px-4 py-3 space-y-1">
  <a href="<?= url('/admin') ?>" class="admin-sidebar-link"><i class="fa-solid fa-gauge w-4"></i> Dashboard</a>
  <a href="<?= url('/admin/bookings') ?>" class="admin-sidebar-link"><i class="fa-solid fa-calendar-check w-4"></i> Bookings</a>
  <a href="<?= url('/admin/events') ?>" class="admin-sidebar-link"><i class="fa-solid fa-champagne-glasses w-4"></i> Events</a>
  <a href="<?= url('/admin/room-types') ?>" class="admin-sidebar-link"><i class="fa-solid fa-bed w-4"></i> Rooms</a>
  <a href="<?= url('/admin/customers') ?>" class="admin-sidebar-link"><i class="fa-solid fa-users w-4"></i> Customers</a>
</div>
