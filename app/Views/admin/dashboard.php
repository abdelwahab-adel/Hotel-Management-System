<?php $pageTitle = 'Dashboard'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 mb-8">
  <div class="card p-5">
    <p class="text-xs text-muted uppercase tracking-wide mb-1">Total Bookings</p>
    <p class="text-2xl font-display font-bold"><?= (int) $summary['total_bookings'] ?></p>
  </div>
  <div class="card p-5">
    <p class="text-xs text-muted uppercase tracking-wide mb-1">Revenue</p>
    <p class="text-2xl font-display font-bold text-gold"><?= money($summary['total_revenue']) ?></p>
  </div>
  <div class="card p-5">
    <p class="text-xs text-muted uppercase tracking-wide mb-1">Customers</p>
    <p class="text-2xl font-display font-bold"><?= (int) $summary['total_customers'] ?></p>
  </div>
  <div class="card p-5">
    <p class="text-xs text-muted uppercase tracking-wide mb-1">Pending Approval</p>
    <p class="text-2xl font-display font-bold text-danger"><?= (int) $summary['pending_bookings'] ?></p>
  </div>
  <div class="card p-5">
    <p class="text-xs text-muted uppercase tracking-wide mb-1">Occupancy Today</p>
    <p class="text-2xl font-display font-bold"><?= e((string) $summary['occupancy_rate']) ?>%</p>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
  <div class="card p-6 lg:col-span-2">
    <h3 class="font-display font-semibold mb-4">Revenue (last 6 months)</h3>
    <canvas id="revenueChart" height="110"></canvas>
  </div>
  <div class="card p-6">
    <h3 class="font-display font-semibold mb-4">Top Room Types</h3>
    <div class="space-y-3">
      <?php foreach ($topRoomTypes as $rt): ?>
        <div class="flex justify-between text-sm">
          <span><?= e($rt['name']) ?></span>
          <span class="text-muted"><?= (int) $rt['bookings_count'] ?> bookings</span>
        </div>
      <?php endforeach; ?>
      <?php if (empty($topRoomTypes)): ?><p class="text-sm text-muted">No paid bookings yet.</p><?php endif; ?>
    </div>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="card overflow-hidden lg:col-span-2">
    <div class="p-5 border-b border-app flex items-center justify-between">
      <h3 class="font-display font-semibold">Recent Bookings</h3>
      <a href="<?= url('/admin/bookings') ?>" class="text-sm text-gold hover:underline">View all</a>
    </div>
    <table class="w-full text-sm">
      <tbody class="divide-app">
        <?php foreach ($recentBookings as $b): ?>
        <tr>
          <td class="p-4"><?= e($b['guest_name']) ?><p class="text-xs text-muted"><?= e($b['room_type_name']) ?> · <?= e($b['booking_ref']) ?></p></td>
          <td class="p-4 text-right"><?= money($b['total_amount']) ?></td>
          <td class="p-4 text-right"><span class="badge badge-<?= e($b['status']) ?>"><?= e(ucfirst(str_replace('_',' ',$b['status']))) ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($recentBookings)): ?><tr><td class="p-6 text-center text-muted">No bookings yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card overflow-hidden">
    <div class="p-5 border-b border-app"><h3 class="font-display font-semibold">Activity Log</h3></div>
    <div class="divide-app max-h-96 overflow-y-auto">
      <?php foreach ($recentActivity as $log): ?>
        <div class="p-4 text-xs">
          <p class="font-medium"><?= e($log['full_name'] ?? 'System') ?> — <?= e(str_replace('_', ' ', $log['action'])) ?></p>
          <p class="text-muted mt-0.5"><?= e($log['description']) ?> · <?= e(date('M j, g:ia', strtotime($log['created_at']))) ?></p>
        </div>
      <?php endforeach; ?>
      <?php if (empty($recentActivity)): ?><p class="p-6 text-center text-muted text-sm">No activity yet.</p><?php endif; ?>
    </div>
  </div>
</div>

<script>
new Chart(document.getElementById('revenueChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_column($revenueByMonth, 'month')) ?>,
    datasets: [{
      label: 'Revenue',
      data: <?= json_encode(array_map('floatval', array_column($revenueByMonth, 'revenue'))) ?>,
      backgroundColor: '#C9A227',
      borderRadius: 6,
    }]
  },
  options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
