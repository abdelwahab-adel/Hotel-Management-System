<?php $pageTitle = 'Staff Accounts'; ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 card overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="text-left text-xs text-muted uppercase border-b border-app">
        <th class="p-4">Name</th><th class="p-4">Username</th><th class="p-4">Role</th><th class="p-4">Status</th>
      </tr></thead>
      <tbody class="divide-app">
        <?php foreach ($staff as $s): ?>
        <tr>
          <td class="p-4"><?= e($s['full_name']) ?></td>
          <td class="p-4"><?= e($s['username']) ?></td>
          <td class="p-4 capitalize"><?= e(str_replace('_',' ',$s['role'])) ?></td>
          <td class="p-4"><span class="badge <?= $s['status'] === 'active' ? 'badge-paid' : 'badge-cancelled' ?>"><?= e(ucfirst($s['status'])) ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="card p-6">
    <h3 class="font-display font-semibold mb-4">Add Staff Account</h3>
    <form method="POST" action="<?= url('/admin/staff') ?>" class="space-y-3">
      <?= $csrf_field ?>
      <input type="text" name="full_name" placeholder="Full name" class="form-input" required>
      <input type="text" name="username" placeholder="Username" class="form-input" required>
      <input type="email" name="email" placeholder="Email" class="form-input" required>
      <select name="role" class="form-select">
        <option value="receptionist">Receptionist</option>
        <option value="admin">Admin</option>
      </select>
      <input type="password" name="password" placeholder="Temporary password" class="form-input" required>
      <button type="submit" class="btn btn-gold w-full">Create Account</button>
    </form>
  </div>
</div>
