<?php $pageTitle = 'Customers'; $totalPages = max(1, (int) ceil($total / $perPage)); ?>
<form method="GET" action="<?= url('/admin/customers') ?>" class="mb-6">
  <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search customers by name, email, username" class="form-input max-w-sm">
</form>

<div class="card overflow-x-auto">
  <table class="w-full text-sm">
    <thead><tr class="text-left text-xs text-muted uppercase border-b border-app">
      <th class="p-4">Name</th><th class="p-4">Email</th><th class="p-4">Phone</th><th class="p-4">Status</th><th class="p-4">Joined</th><th class="p-4"></th>
    </tr></thead>
    <tbody class="divide-app">
      <?php foreach ($customers as $c): ?>
      <tr>
        <td class="p-4 font-medium"><?= e($c['full_name']) ?></td>
        <td class="p-4"><?= e($c['email']) ?></td>
        <td class="p-4"><?= e((string) $c['phone']) ?></td>
        <td class="p-4"><span class="badge <?= $c['status'] === 'active' ? 'badge-paid' : 'badge-cancelled' ?>"><?= e(ucfirst($c['status'])) ?></span></td>
        <td class="p-4 text-xs text-muted"><?= e(date('M j, Y', strtotime($c['created_at']))) ?></td>
        <td class="p-4 text-right whitespace-nowrap">
          <a href="<?= url('/admin/customers/' . $c['id']) ?>" class="btn btn-outline btn-sm">View</a>
          <form method="POST" action="<?= url('/admin/customers/' . $c['id'] . '/toggle') ?>" class="inline">
            <?= $csrf_field ?>
            <button class="btn btn-sm <?= $c['status'] === 'active' ? 'btn-danger' : 'btn-dark' ?>"><?= $c['status'] === 'active' ? 'Suspend' : 'Activate' ?></button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($customers)): ?><tr><td colspan="6" class="p-8 text-center text-muted">No customers found.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php if ($totalPages > 1): ?>
<div class="flex gap-2 mt-4">
  <?php for ($p = 1; $p <= $totalPages; $p++): ?>
    <a href="<?= url('/admin/customers?page=' . $p . '&q=' . urlencode($search)) ?>" class="btn btn-sm <?= $p === $page ? 'btn-dark' : 'btn-outline' ?>"><?= $p ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>
