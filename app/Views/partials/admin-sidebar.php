<?php
$role = \App\Core\Auth::role();
$links = [
    ['url' => '/admin', 'icon' => 'fa-gauge', 'label' => 'Dashboard', 'roles' => ['super_admin','admin','receptionist']],
    ['url' => '/admin/bookings', 'icon' => 'fa-calendar-check', 'label' => 'Bookings', 'roles' => ['super_admin','admin','receptionist']],
    ['url' => '/admin/events', 'icon' => 'fa-champagne-glasses', 'label' => 'Events', 'roles' => ['super_admin','admin','receptionist']],
    ['url' => '/admin/room-types', 'icon' => 'fa-bed', 'label' => 'Rooms', 'roles' => ['super_admin','admin']],
    ['url' => '/admin/customers', 'icon' => 'fa-users', 'label' => 'Customers', 'roles' => ['super_admin','admin','receptionist']],
    ['url' => '/admin/reports', 'icon' => 'fa-chart-line', 'label' => 'Reports', 'roles' => ['super_admin','admin']],
    ['url' => '/admin/staff', 'icon' => 'fa-user-shield', 'label' => 'Staff', 'roles' => ['super_admin']],
    ['url' => '/admin/settings', 'icon' => 'fa-gear', 'label' => 'Settings', 'roles' => ['super_admin']],
    ['url' => '/admin/activity-logs', 'icon' => 'fa-clock-rotate-left', 'label' => 'Activity Log', 'roles' => ['super_admin']],
];
$currentPath = rtrim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
?>
<aside class="w-64 bg-primary text-white min-h-screen shrink-0 hidden lg:flex flex-col">
  <a href="<?= url('/') ?>" class="flex items-center gap-2 font-display font-bold text-lg px-6 py-5 border-b border-white/10">
    <i class="fa-solid fa-hotel text-gold"></i> <?= e((string) setting('hotel_name', 'The Pacific Hotel')) ?>
  </a>
  <nav class="flex-1 px-3 py-5 space-y-1">
    <?php foreach ($links as $link): ?>
      <?php if (!in_array($role, $link['roles'], true)) continue; ?>
      <a href="<?= url($link['url']) ?>" class="admin-sidebar-link <?= $currentPath === rtrim(url($link['url']), '/') || str_ends_with($currentPath, $link['url']) ? 'active' : '' ?>">
        <i class="fa-solid <?= $link['icon'] ?> w-4"></i> <?= $link['label'] ?>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="px-6 py-4 border-t border-white/10 text-xs text-slate-400">
    Signed in as<br><span class="text-white font-medium"><?= e(current_user_name()) ?></span> · <span class="capitalize"><?= e((string) $role) ?></span>
  </div>
</aside>
