<?php $pageTitle = 'Reports'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<div class="flex flex-wrap gap-3 mb-8">
  <a href="<?= url('/admin/reports/export/bookings') ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-file-csv"></i> Export Bookings (CSV)</a>
  <a href="<?= url('/admin/reports/export/customers') ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-file-csv"></i> Export Customers (CSV)</a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 mb-8">
  <div class="card p-5"><p class="text-xs text-muted uppercase mb-1">Total Bookings</p><p class="text-2xl font-display font-bold"><?= (int) $summary['total_bookings'] ?></p></div>
  <div class="card p-5"><p class="text-xs text-muted uppercase mb-1">Revenue</p><p class="text-2xl font-display font-bold text-gold"><?= money($summary['total_revenue']) ?></p></div>
  <div class="card p-5"><p class="text-xs text-muted uppercase mb-1">Customers</p><p class="text-2xl font-display font-bold"><?= (int) $summary['total_customers'] ?></p></div>
  <div class="card p-5"><p class="text-xs text-muted uppercase mb-1">Pending</p><p class="text-2xl font-display font-bold"><?= (int) $summary['pending_bookings'] ?></p></div>
  <div class="card p-5"><p class="text-xs text-muted uppercase mb-1">Occupancy</p><p class="text-2xl font-display font-bold"><?= e((string) $summary['occupancy_rate']) ?>%</p></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="card p-6 lg:col-span-2">
    <h3 class="font-display font-semibold mb-4">Revenue (last 12 months)</h3>
    <canvas id="revenueChart" height="100"></canvas>
  </div>
  <div class="card p-6">
    <h3 class="font-display font-semibold mb-4">Top Room Types</h3>
    <table class="w-full text-sm">
      <?php foreach ($topRoomTypes as $rt): ?>
        <tr class="border-b border-app last:border-0">
          <td class="py-2"><?= e($rt['name']) ?></td>
          <td class="py-2 text-right text-muted"><?= (int) $rt['bookings_count'] ?></td>
          <td class="py-2 text-right font-semibold"><?= money($rt['revenue'] ?? 0) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>

<script>
new Chart(document.getElementById('revenueChart'), {
  type: 'line',
  data: {
    labels: <?= json_encode(array_column($revenueByMonth, 'month')) ?>,
    datasets: [{ label: 'Revenue', data: <?= json_encode(array_map('floatval', array_column($revenueByMonth, 'revenue'))) ?>, borderColor: '#C9A227', backgroundColor: 'rgba(201,162,39,.15)', fill: true, tension: .35 }]
  },
  options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
