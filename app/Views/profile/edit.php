<?php $pageTitle = 'My Profile'; ?>
<section class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
  <h1 class="font-display text-2xl font-bold mb-8">My Profile</h1>
  <form method="POST" action="<?= url('/dashboard/profile') ?>" class="card p-7 space-y-4">
    <?= $csrf_field ?>
    <div>
      <label class="form-label">Full Name</label>
      <input type="text" name="full_name" value="<?= old('full_name', $user['full_name']) ?>" class="form-input <?= field_error_class('full_name') ?>" required>
      <?php if (field_error('full_name')): ?><p class="form-error"><?= e(field_error('full_name')) ?></p><?php endif; ?>
    </div>
    <div>
      <label class="form-label">Phone</label>
      <input type="text" name="phone" value="<?= old('phone', (string) $user['phone']) ?>" class="form-input">
    </div>
    <div>
      <label class="form-label">Email (read-only)</label>
      <input type="email" value="<?= e($user['email']) ?>" class="form-input" disabled>
    </div>
    <div class="pt-4 border-t border-app">
      <label class="form-label">New Password (optional)</label>
      <input type="password" name="new_password" class="form-input" placeholder="Leave blank to keep current password">
    </div>
    <button type="submit" class="btn btn-gold w-full">Save Changes</button>
  </form>
</section>
