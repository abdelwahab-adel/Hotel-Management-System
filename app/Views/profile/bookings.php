<?php $pageTitle = 'My Bookings'; ?>
<section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
  <h1 class="font-display text-2xl font-bold mb-8">My Bookings</h1>

  <h2 class="font-display font-semibold text-lg mb-3">Room Bookings</h2>
  <div class="card overflow-x-auto mb-10">
    <table class="w-full text-sm">
      <thead><tr class="text-left text-xs text-muted uppercase border-b border-app">
        <th class="p-4">Reference</th><th class="p-4">Room</th><th class="p-4">Dates</th><th class="p-4">Total</th><th class="p-4">Status</th><th class="p-4"></th>
      </tr></thead>
      <tbody class="divide-app">
        <?php foreach ($bookings as $b): ?>
        <tr>
          <td class="p-4 font-mono text-xs"><?= e($b['booking_ref']) ?></td>
          <td class="p-4"><?= e($b['room_type_name']) ?> #<?= e($b['room_number']) ?></td>
          <td class="p-4"><?= e($b['check_in']) ?> → <?= e($b['check_out']) ?></td>
          <td class="p-4"><?= money($b['total_amount']) ?></td>
          <td class="p-4"><span class="badge badge-<?= e($b['status']) ?>"><?= e(ucfirst(str_replace('_',' ',$b['status']))) ?></span></td>
          <td class="p-4 text-right whitespace-nowrap">
            <a href="<?= url('/dashboard/bookings/' . $b['booking_ref'] . '/invoice') ?>" class="btn btn-outline btn-sm" target="_blank">Invoice</a>
            <?php if (in_array($b['status'], ['pending','confirmed'], true)): ?>
              <form method="POST" action="<?= url('/dashboard/bookings/' . $b['booking_ref'] . '/cancel') ?>" class="inline" onsubmit="return confirm('Cancel this booking?');">
                <?= $csrf_field ?>
                <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($bookings)): ?><tr><td colspan="6" class="p-6 text-center text-muted">No room bookings yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <h2 class="font-display font-semibold text-lg mb-3">Event Bookings</h2>
  <div class="card overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="text-left text-xs text-muted uppercase border-b border-app">
        <th class="p-4">Reference</th><th class="p-4">Venue</th><th class="p-4">Date</th><th class="p-4">Total</th><th class="p-4">Status</th>
      </tr></thead>
      <tbody class="divide-app">
        <?php foreach ($events as $ev): ?>
        <tr>
          <td class="p-4 font-mono text-xs"><?= e($ev['booking_ref']) ?></td>
          <td class="p-4"><?= e($ev['event_type_name']) ?></td>
          <td class="p-4"><?= e($ev['event_date']) ?>, <?= e($ev['start_time']) ?>–<?= e($ev['end_time']) ?></td>
          <td class="p-4"><?= money($ev['total_amount']) ?></td>
          <td class="p-4"><span class="badge badge-<?= e($ev['status']) ?>"><?= e(ucfirst($ev['status'])) ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($events)): ?><tr><td colspan="5" class="p-6 text-center text-muted">No event bookings yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
